<?php

namespace Tests\Feature;

use App\Models\EventLogistic;
use App\Filament\Resources\EventLogisticResource\Pages\ManageTransport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Carbon\Carbon;

class ManageTransportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_unassigned_participants_by_selected_day()
    {
        $logistic = EventLogistic::factory()->create([
            'settings' => [
                'start_date' => '2026-02-05',
                'days_count' => 2,
            ],
            'participants_data' => [
                [
                    'id' => 'p1', 
                    'name' => 'Day 1 Bus', 
                    'survey_response' => [
                        'responses' => [
                            '2026-02-05' => ['aller' => ['mode' => 'bus']],
                            '2026-02-06' => ['aller' => ['mode' => 'car']],
                        ]
                    ]
                ],
                [
                    'id' => 'p2', 
                    'name' => 'Day 2 Bus', 
                    'survey_response' => [
                        'responses' => [
                            '2026-02-05' => ['aller' => ['mode' => 'car']],
                            '2026-02-06' => ['aller' => ['mode' => 'bus']],
                        ]
                    ]
                ]
            ]
        ]);

        $component = Livewire::test(ManageTransport::class, ['record' => $logistic->getRouteKey()]);
        
        // On Day 1, only p1 should be in unassigned transport
        $component->set('selectedDay', '2026-02-05');
        $unassigned = array_values($component->get('unassignedTransport'));
        $this->assertCount(1, $unassigned);
        $this->assertEquals('p1', $unassigned[0]['id']);

        // On Day 2, only p2 should be in unassigned transport
        $component->set('selectedDay', '2026-02-06');
        $unassigned = array_values($component->get('unassignedTransport'));
        $this->assertCount(1, $unassigned);
        $this->assertEquals('p2', $unassigned[0]['id']);
    }

    /** @test */
    public function it_auto_dispatches_for_selected_day()
    {
        $logistic = EventLogistic::factory()->create([
            'settings' => [
                'start_date' => '2026-02-05',
                'days_count' => 2,
                'bus_capacity' => 50,
            ],
            'participants_data' => [
                [
                    'id' => 'p1', 
                    'name' => 'Need Bus Day 1', 
                    'survey_response' => [
                        'responses' => [
                            '2026-02-05' => ['aller' => ['mode' => 'bus']],
                        ]
                    ]
                ],
                [
                    'id' => 'p2', 
                    'name' => 'Offers Car Day 1', 
                    'survey_response' => [
                        'responses' => [
                            '2026-02-05' => ['aller' => ['mode' => 'car_seats', 'seats' => 2]],
                        ]
                    ]
                ]
            ]
        ]);

        Livewire::test(ManageTransport::class, ['record' => $logistic->getRouteKey()])
            ->set('selectedDay', '2026-02-05')
            ->call('autoDispatch');

        $logistic->refresh();
        $plan = $logistic->transport_plan['2026-02-05'] ?? [];
        
        // Should have 1 Bus and 1 Car
        $this->assertCount(2, $plan);
        
        $car = collect($plan)->firstWhere('type', 'car');
        $this->assertNotNull($car);
        $this->assertEquals(2, $car['capacity']);
        $this->assertContains('p2', $car['passengers']);
        
        // p1 should be in the Bus or Car depending on fill order
        $bus = collect($plan)->firstWhere('type', 'bus');
        $allPassengers = array_merge($bus['passengers'] ?? [], $car['passengers'] ?? []);
        $this->assertContains('p1', $allPassengers);
    }
}
