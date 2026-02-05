<?php

namespace Tests\Feature;

use App\Models\EventLogistic;
use App\Livewire\Logistics\Survey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Carbon\Carbon;

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
            ]
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
            ]
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
}
