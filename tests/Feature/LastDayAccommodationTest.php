<?php

namespace Tests\Feature;

use App\Models\EventLogistic;
use App\Filament\Resources\EventLogisticResource\Pages\ManageTransport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Carbon\Carbon;

class LastDayAccommodationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_hides_accommodation_on_last_day()
    {
        $logistic = EventLogistic::factory()->create([
            'settings' => [
                'start_date' => '2026-02-05',
                'days_count' => 2,
            ],
            'participants_data' => [
                [
                    'id' => 'p1', 
                    'name' => 'Athlete needing hotel', 
                    'survey_response' => [
                        'hotel_needed' => true,
                        'responses' => [
                            '2026-02-05' => ['aller' => ['mode' => 'bus']],
                            '2026-02-06' => ['aller' => ['mode' => 'bus']],
                        ]
                    ]
                ]
            ]
        ]);

        // Day 1 (2026-02-05) - Should have accommodation logic
        $component = Livewire::test(ManageTransport::class, ['record' => $logistic->getRouteKey()])
            ->set('selectedDay', '2026-02-05');
        
        $this->assertNotEmpty($component->get('unassignedStay'));
        $this->assertContains(['type' => 'danger', 'msg' => "Dodo manquant: Athlete needing hotel"], $component->get('globalAlerts'));

        // Day 2 (2026-02-06, Last Day) - Should NOT have accommodation logic
        $component->set('selectedDay', '2026-02-06');
        
        $this->assertEmpty($component->get('unassignedStay'));
        // Check that "Dodo manquant" is NOT in globalAlerts for the last day
        $globalAlerts = $component->get('globalAlerts');
        foreach ($globalAlerts as $alert) {
            $this->assertStringNotContainsString('Dodo manquant', $alert['msg']);
        }
    }
}
