<?php

namespace Tests\Feature;

use Tests\TestCase;
use Livewire\Livewire;
use App\Models\EventLogistic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Filament\Resources\EventLogisticResource\Pages\ManageTransport;

class ManageTransportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_switch_planning_modes()
    {
        $logistic = EventLogistic::factory()->create([
            'settings' => ['planning_mode' => 'survey'],
        ]);

        Livewire::test(ManageTransport::class, ['record' => $logistic->getRouteKey()])
            ->call('updatePlanningMode', 'schedule')
            ->assertSet('planningMode', 'schedule');

        $logistic->refresh();
        $this->assertEquals('schedule', $logistic->settings['planning_mode']);
    }

    /** @test */
    public function it_filters_unassigned_participants_based_on_schedule_mode()
    {
        // Athlete with competition but NO survey
        $logistic = EventLogistic::factory()->create([
            'settings'          => ['start_date' => '2024-07-15'],
            'participants_data' => [
                [
                    'id'               => 'p1',
                    'name'             => 'Athlete Schedule',
                    'competition_days' => ['2024-07-15' => true],
                    'survey_response'  => ['responses' => []],
                ],
            ],
        ]);

        // In survey mode, p1 is NOT unassigned (because no bus request in survey)
        $component = Livewire::test(ManageTransport::class, ['record' => $logistic->getRouteKey()])
            ->set('selectedDay', '2024-07-15')
            ->set('planningMode', 'survey');

        $this->assertEmpty($component->get('unassignedTransport'));

        // In schedule mode, p1 SHOULD be unassigned
        $component->call('updatePlanningMode', 'schedule');
        $this->assertNotEmpty($component->get('unassignedTransport'));
        $this->assertEquals('p1', $component->get('unassignedTransport')[0]['id']);
    }

    /** @test */
    public function it_can_lock_and_unlock_vehicles()
    {
        $logistic = EventLogistic::factory()->create([
            'settings'       => ['start_date' => '2024-07-15'],
            'transport_plan' => [
                '2024-07-15' => [
                    ['id' => 'v1', 'name' => 'Bus', 'locked' => false, 'passengers' => []],
                ],
            ],
        ]);

        Livewire::test(ManageTransport::class, ['record' => $logistic->getRouteKey()])
            ->set('selectedDay', '2024-07-15')
            ->call('toggleLock', 'vehicle', 0);

        $logistic->refresh();
        $this->assertTrue($logistic->transport_plan['2024-07-15'][0]['locked']);

        // Unlock
        Livewire::test(ManageTransport::class, ['record' => $logistic->getRouteKey()])
            ->set('selectedDay', '2024-07-15')
            ->call('toggleLock', 'vehicle', 0);

        $logistic->refresh();
        $this->assertFalse($logistic->transport_plan['2024-07-15'][0]['locked'] ?? false);
    }

    /** @test */
    public function it_respects_locked_vehicles_during_auto_dispatch()
    {
        $logistic = EventLogistic::factory()->create([
            'settings' => [
                'start_date'        => '2024-07-15',
                'distance_km'       => 100,
                'bus_speed'         => 100,
                'duration_prep_min' => 60,
            ],
            'participants_data' => [
                [
                    'id'              => 'p1', 'name' => 'Athlete 1',
                    'survey_response' => ['responses' => ['2024-07-15' => ['aller' => ['mode' => 'bus']]]],
                ],
            ],
            'transport_plan' => [
                '2024-07-15' => [
                    [
                        'id'         => 'locked-v',
                        'name'       => 'Special Van',
                        'locked'     => true,
                        'capacity'   => 2,
                        'passengers' => ['p1'],
                        'flow'       => 'aller',
                    ],
                ],
            ],
        ]);

        // Auto dispatch should NOT replace the locked vehicle
        Livewire::test(ManageTransport::class, ['record' => $logistic->getRouteKey()])
            ->set('selectedDay', '2024-07-15')
            ->call('autoDispatch');

        $logistic->refresh();
        $plan = $logistic->transport_plan['2024-07-15'];

        $this->assertCount(1, $plan);
        $this->assertEquals('locked-v', $plan[0]['id']);
        $this->assertContains('p1', $plan[0]['passengers']);
    }

    /** @test */
    public function it_can_add_manual_participants()
    {
        $logistic = EventLogistic::factory()->create(['participants_data' => []]);

        Livewire::test(ManageTransport::class, ['record' => $logistic->getRouteKey()])
            ->call('addManualParticipant', 'Coach Pierre', 'coach');

        $logistic->refresh();
        $participants = $logistic->participants_data;
        $this->assertCount(1, $participants);
        $this->assertEquals('coach', $participants[0]['role']);
        $this->assertTrue($participants[0]['is_manual']);
        $this->assertStringContainsString('Coach Pierre', $participants[0]['name']);
    }

    /** @test */
    public function manual_participants_are_always_included_in_unassigned()
    {
        $logistic = EventLogistic::factory()->create([
            'settings'          => ['start_date' => '2024-07-15'],
            'participants_data' => [
                [
                    'id'              => 'm1',
                    'name'            => 'Coach Pierre',
                    'role'            => 'coach',
                    'is_manual'       => true,
                    'survey_response' => ['responses' => []],
                ],
            ],
        ]);

        $component = Livewire::test(ManageTransport::class, ['record' => $logistic->getRouteKey()])
            ->set('selectedDay', '2024-07-15');

        $this->assertNotEmpty($component->get('unassignedTransport'));
        $this->assertEquals('m1', $component->get('unassignedTransport')[0]['id']);
    }

    /** @test */
    public function it_filters_unassigned_participants_based_on_all_mode()
    {
        $logistic = EventLogistic::factory()->create([
            'settings'          => ['start_date' => '2024-07-15'],
            'participants_data' => [
                [
                    'id'               => 'p1',
                    'name'             => 'Athlete Silent',
                    'competition_days' => [],
                    'survey_response'  => ['responses' => []],
                ],
            ],
        ]);

        $component = Livewire::test(ManageTransport::class, ['record' => $logistic->getRouteKey()])
            ->set('selectedDay', '2024-07-15')
            ->call('updatePlanningMode', 'all');

        $this->assertNotEmpty($component->get('unassignedTransport'));
    }

    /** @test */
    public function it_can_lock_and_unlock_rooms()
    {
        $logistic = EventLogistic::factory()->create([
            'settings'  => ['start_date' => '2024-07-15'],
            'stay_plan' => [
                '2024-07-15' => [
                    ['id' => 'r1', 'name' => 'Room 101', 'locked' => false, 'occupant_ids' => []],
                ],
            ],
        ]);

        Livewire::test(ManageTransport::class, ['record' => $logistic->getRouteKey()])
            ->set('selectedDay', '2024-07-15')
            ->call('toggleLock', 'room', 0);

        $logistic->refresh();
        $this->assertTrue($logistic->stay_plan['2024-07-15'][0]['locked']);
    }

    /** @test */
    public function it_calculates_departure_times_correcty_in_auto_dispatch()
    {
        $logistic = EventLogistic::factory()->create([
            'settings' => [
                'start_date'        => '2024-07-15',
                'distance_km'       => 100, // 100km
                'bus_speed'         => 100, // 1h travel
                'duration_prep_min' => 60, // 1h prep. Total 2h.
            ],
            'participants_data' => [
                [
                    'id'                         => 'p1', 'name' => 'Athlete 1',
                    'first_competition_datetime' => '2024-07-15 10:00:00',
                    'survey_response'            => ['responses' => ['2024-07-15' => ['aller' => ['mode' => 'bus']]]],
                ],
            ],
        ]);

        Livewire::test(ManageTransport::class, ['record' => $logistic->getRouteKey()])
            ->set('selectedDay', '2024-07-15')
            ->call('autoDispatch');

        $logistic->refresh();
        $plan = $logistic->transport_plan['2024-07-15'];

        $bus = collect($plan)->firstWhere('type', 'bus');
        // 10:00 - 2h = 08:00
        $this->assertEquals('2024-07-15 08:00:00', $bus['departure_datetime']);
    }
}
