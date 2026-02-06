<?php

namespace Tests\Feature;

use Tests\TestCase;
use Livewire\Livewire;
use App\Models\EventLogistic;
use App\Livewire\Logistics\Survey;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LogisticsSurveyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_render_survey_with_flexible_days()
    {
        $logistic = EventLogistic::factory()->create([
            'settings' => [
                'start_date' => '2026-02-05',
                'days_count' => 2,
            ],
        ]);

        Livewire::test(Survey::class, ['event_logistic' => $logistic])
            ->set('participantId', 'new')
            ->set('isCoach', true)
            ->assertSee('2026-02-05')
            ->assertSee('2026-02-06');
    }

    /** @test */
    public function it_can_add_new_person_and_coach()
    {
        $logistic = EventLogistic::factory()->create();

        Livewire::test(Survey::class, ['event_logistic' => $logistic])
            ->set('participantId', 'new')
            ->set('newName', 'Jean Dupont')
            ->set('isCoach', true)
            ->set('responses.2026-02-05.aller.mode', 'bus')
            ->call('submit')
            ->assertHasNoErrors();

        $logistic->refresh();
        $this->assertCount(1, $logistic->participants_data);
        $this->assertEquals('[E] Jean Dupont', $logistic->participants_data[0]['name']);
        $this->assertEquals('coach', $logistic->participants_data[0]['role']);
        $this->assertEquals('bus', $logistic->participants_data[0]['survey_response']['responses']['2026-02-05']['aller']['mode']);
    }

    /** @test */
    public function it_restricts_hotel_checkbox_to_coaches()
    {
        $logistic = EventLogistic::factory()->create([
            'participants_data' => [
                ['id' => 'p1', 'name' => 'Athlete', 'role' => 'athlete'],
                ['id' => 'p2', 'name' => '[E] Coach', 'role' => 'coach'],
            ],
        ]);

        // Athlete should not be able to request hotel
        Livewire::test(Survey::class, ['event_logistic' => $logistic])
            ->set('participantId', 'p1')
            ->assertSet('can_request_hotel', false)
            ->set('hotel_needed', true)
            ->call('submit');

        $logistic->refresh();
        $p1 = collect($logistic->participants_data)->firstWhere('id', 'p1');
        $this->assertFalse($p1['survey_response']['hotel_needed']);

        // Coach should be able to request hotel
        Livewire::test(Survey::class, ['event_logistic' => $logistic])
            ->set('participantId', 'p2')
            ->assertSet('can_request_hotel', true)
            ->set('hotel_needed', true)
            ->call('submit');

        $logistic->refresh();
        $p2 = collect($logistic->participants_data)->firstWhere('id', 'p2');
        $this->assertTrue($p2['survey_response']['hotel_needed']);
    }

    /** @test */
    public function it_handles_granular_transport_with_seats()
    {
        $logistic = EventLogistic::factory()->create();

        Livewire::test(Survey::class, ['event_logistic' => $logistic])
            ->set('participantId', 'new')
            ->set('newName', 'Parent Transport')
            ->set('responses.2026-02-05.aller.mode', 'car_seats')
            ->set('responses.2026-02-05.aller.seats', 3)
            ->set('responses.2026-02-05.retour.mode', 'absent')
            ->call('submit');

        $logistic->refresh();
        $p = $logistic->participants_data[0];
        $resp = $p['survey_response']['responses']['2026-02-05'];

        $this->assertEquals('car_seats', $resp['aller']['mode']);
        $this->assertEquals(3, $resp['aller']['seats']);
        $this->assertEquals('absent', $resp['retour']['mode']);
    }

    /** @test */
    public function it_enforces_fixed_date_deadline()
    {
        $logistic = EventLogistic::factory()->create([
            'settings' => [
                'survey_deadline_at' => now()->subDay()->toDateTimeString(),
            ],
        ]);

        Livewire::test(Survey::class, ['event_logistic' => $logistic])
            ->assertSet('is_survey_closed', true)
            ->set('participantId', 'new')
            ->set('newName', 'Jean Dupont')
            ->call('submit')
            ->assertHasErrors(['error' => 'Le sondage est fermé. Les modifications ne sont plus possibles en ligne.']);
    }

    /** @test */
    public function it_enforces_relative_deadline()
    {
        $logistic = EventLogistic::factory()->create([
            'settings' => [
                'start_date'                  => now()->addDays(2)->toDateString(),
                'survey_deadline_days_before' => 3,
            ],
        ]);

        // 2 days before event, deadline is 3 days before -> should be closed
        Livewire::test(Survey::class, ['event_logistic' => $logistic])
            ->assertSet('is_survey_closed', true);

        // 4 days before event, deadline is 3 days before -> should be open
        $logistic->update(['settings' => [
            'start_date'                  => now()->addDays(4)->toDateString(),
            'survey_deadline_days_before' => 3,
        ]]);

        Livewire::test(Survey::class, ['event_logistic' => $logistic])
            ->assertSet('is_survey_closed', false);
    }

    /** @test */
    public function it_updates_survey_updated_at_on_submission()
    {
        $logistic = EventLogistic::factory()->create();
        $this->assertNull($logistic->settings['survey_updated_at'] ?? null);

        Livewire::test(Survey::class, ['event_logistic' => $logistic])
            ->set('participantId', 'new')
            ->set('newName', 'Jean Dupont')
            ->call('submit');

        $logistic->refresh();
        $this->assertNotNull($logistic->settings['survey_updated_at']);
    }

    /** @test */
    public function it_correctly_identifies_responded_participants_only_if_filled_at_is_present()
    {
        $logistic = EventLogistic::factory()->create([
            'participants_data' => [
                [
                    'id'              => 'p1',
                    'name'            => 'Filament Created',
                    'survey_response' => [], // Empty response created by Filament
                ],
                [
                    'id'              => 'p2',
                    'name'            => 'Real Response',
                    'survey_response' => [
                        'filled_at' => now()->toDateTimeString(),
                        'responses' => [],
                    ],
                ],
                [
                    'id'   => 'p3',
                    'name' => 'No Response',
                ],
            ],
        ]);

        Livewire::test(Survey::class, ['event_logistic' => $logistic])
            ->assertSet('stats.responded_count', 1)
            ->assertSet('stats.not_responded_count', 2)
            ->assertSet('stats.responded', ['Real Response'])
            // Simulate filling the empty one
            ->set('participantId', 'p1')
            ->call('submit')
            ->assertSet('stats.responded_count', 2)
            ->assertSet('stats.not_responded_count', 1);
    }

    /** @test */
    public function it_validates_form_on_submit()
    {
        $logistic = EventLogistic::factory()->create();

        Livewire::test(Survey::class, ['event_logistic' => $logistic])
            ->set('participantId', 'new')
            ->set('newName', '') // Invalid
            ->call('submit')
            ->assertHasErrors(['newName' => 'required_if']);
    }

    /** @test */
    public function it_loads_existing_data_on_participant_change()
    {
        $logistic = EventLogistic::factory()->create([
            'participants_data' => [
                [
                    'id'              => 'p1',
                    'name'            => 'Existing',
                    'survey_response' => [
                        'filled_at'    => now()->toDateTimeString(),
                        'remarks'      => 'My Remarks',
                        'hotel_needed' => true,
                    ],
                ],
            ],
        ]);

        Livewire::test(Survey::class, ['event_logistic' => $logistic])
            ->set('participantId', 'p1')
            ->assertSet('remarks', 'My Remarks')
            ->assertSet('hotel_needed', true);
    }

    /** @test */
    public function it_updates_existing_response_without_duplication()
    {
        $logistic = EventLogistic::factory()->create([
            'participants_data' => [
                ['id' => 'p1', 'name' => 'Existing', 'role' => 'athlete'],
            ],
        ]);

        Livewire::test(Survey::class, ['event_logistic' => $logistic])
            ->set('participantId', 'p1')
            ->set('remarks', 'Initial')
            ->call('submit');

        $logistic->refresh();
        $this->assertCount(1, $logistic->participants_data);
        $this->assertEquals('Initial', $logistic->participants_data[0]['survey_response']['remarks']);

        Livewire::test(Survey::class, ['event_logistic' => $logistic])
            ->set('participantId', 'p1')
            ->set('remarks', 'Updated')
            ->call('submit');

        $logistic->refresh();
        $this->assertCount(1, $logistic->participants_data);
        $this->assertEquals('Updated', $logistic->participants_data[0]['survey_response']['remarks']);
    }

    /** @test */
    public function it_shows_correct_stats_in_view()
    {
        $logistic = EventLogistic::factory()->create([
            'participants_data' => [
                ['id' => 'p1', 'name' => 'Athlete 1', 'survey_response' => ['filled_at' => now()]],
                ['id' => 'p2', 'name' => 'Athlete 2'],
            ],
        ]);

        Livewire::test(Survey::class, ['event_logistic' => $logistic])
            ->assertSee('Ont répondu (1)')
            ->assertSee('Attente de réponse (1)')
            ->assertSee('Athlete 1')
            ->assertSee('Athlete 2');
    }
}
