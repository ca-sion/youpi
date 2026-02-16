<?php

namespace Tests\Feature;

use Tests\TestCase;
use Livewire\Livewire;
use App\Models\Document;
use App\Enums\DocumentType;
use App\Enums\DocumentStatus;
use App\Models\EventLogistic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Filament\Resources\EventLogisticResource\Pages\EditEventLogistic;

class DocumentPreparationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_new_document_if_none_linked()
    {
        $logistic = EventLogistic::factory()->create([
            'name'     => 'Event Test',
            'settings' => ['start_date' => '2024-07-15'],
        ]);

        $this->assertNull($logistic->document_id);

        Livewire::test(EditEventLogistic::class, ['record' => $logistic->getRouteKey()])
            ->callAction('prepare_document');

        $logistic->refresh();
        $this->assertNotNull($logistic->document_id);

        $document = $logistic->document;
        $this->assertEquals('Event Test', $document->name);
        $this->assertEquals(DocumentType::TRAVEL, $document->type);
    }

    /** @test */
    public function it_preserves_data_if_document_already_exists()
    {
        $document = Document::create([
            'name'        => 'Existing Doc',
            'type'        => DocumentType::TRAVEL,
            'status'      => DocumentStatus::VALIDATED,
            'travel_data' => [
                'data' => [
                    'old_key' => 'old_value',
                    'accomodation' => 'Hotel Plaza',
                    'competition_schedules' => 'Old Schedule'
                ]
            ],
        ]);

        $logistic = EventLogistic::factory()->create([
            'name'        => 'Event Updated',
            'document_id' => $document->id,
            'settings'    => ['start_date' => '2024-07-15'],
            'participants_data' => [
                [
                    'id' => 'p1',
                    'name' => 'Fast Runner',
                    'competition_days' => [
                        '2024-07-15' => ['first' => '2024-07-15 10:00:00', 'last' => '2024-07-15 11:00:00']
                    ],
                    'first_competition_datetime' => '2024-07-15 10:00:00'
                ]
            ]
        ]);

        Livewire::test(EditEventLogistic::class, ['record' => $logistic->getRouteKey()])
            ->callAction('prepare_document');

        $document->refresh();
        $this->assertArrayNotHasKey('old_key', $document->travel_data['data']);
        $this->assertEquals('Hotel Plaza', $document->travel_data['data']['accomodation']); // Preserved
        $this->assertStringContainsString('Fast Runner', $document->travel_data['data']['competition_schedules']); // Overwritten
        $this->assertStringNotContainsString('Old Schedule', $document->travel_data['data']['competition_schedules']);
    }

    /** @test */
    public function it_maps_transport_plan_and_independents()
    {
        $startDate = '2024-07-15';
        $logistic = EventLogistic::factory()->create([
            'name'              => 'Transport Event',
            'settings'          => ['start_date' => $startDate, 'days_count' => 1],
            'participants_data' => [
                ['id' => 'p1', 'name' => 'Bus Athlete', 'survey_response' => ['responses' => [$startDate => ['aller' => ['mode' => 'bus']]]]],
                ['id' => 'p2', 'name' => 'Train Athlete', 'survey_response' => ['responses' => [$startDate => ['aller' => ['mode' => 'train']]]]],
            ],
            'transport_plan' => [
                $startDate => [
                    [
                        'id'                 => 'v1',
                        'type'               => 'bus',
                        'flow'               => 'aller',
                        'departure_datetime' => $startDate.' 08:00:00',
                        'departure_location' => 'Sion',
                        'driver'             => 'Jean',
                        'passengers'         => ['p1'],
                    ],
                ],
            ],
        ]);

        Livewire::test(EditEventLogistic::class, ['record' => $logistic->getRouteKey()])
            ->callAction('prepare_document');

        $document = $logistic->refresh()->document;
        $travelData = $document->travel_data['data'];

        // Check departures (1 Bus + 1 Independent)
        $this->assertCount(2, $travelData['departures']); 
        
        $busEntry = collect($travelData['departures'])->firstWhere('means', 'Bus');
        $this->assertEquals('Bus Athlete', $busEntry['travelers']);
        $this->assertEquals('Jean', $busEntry['driver']);

        // Check Independent
        $indepEntry = collect($travelData['departures'])->firstWhere('day_hour', 'Par ses propres moyens');
        $this->assertEquals('Train Athlete', $indepEntry['travelers']);
    }

    /** @test */
    public function it_maps_stay_plan_grouped_by_day()
    {
        $startDate = '2024-07-15';
        $logistic = EventLogistic::factory()->create([
            'settings'          => ['start_date' => $startDate],
            'participants_data' => [
                ['id' => 'p1', 'name' => 'Sleeper 1'],
                ['id' => 'p2', 'name' => 'Sleeper 2'],
            ],
            'stay_plan' => [
                $startDate => [
                    [
                        'id'           => 'r1',
                        'occupant_ids' => ['p1'],
                    ],
                    [
                        'id'           => 'r2',
                        'occupant_ids' => ['p2'],
                    ],
                ],
            ],
        ]);

        Livewire::test(EditEventLogistic::class, ['record' => $logistic->getRouteKey()])
            ->callAction('prepare_document');

        $document = $logistic->refresh()->document;
        $nights = $document->travel_data['data']['nights'];

        $this->assertCount(1, $nights);
        $this->assertEquals('Sleeper 1 | Sleeper 2', $nights[0]['travelers']);
    }

    /** @test */
    public function it_maps_athlete_schedules_from_competition_days()
    {
        $startDate = '2024-07-15';
        $logistic = EventLogistic::factory()->create([
            'settings'          => ['start_date' => $startDate],
            'participants_data' => [
                [
                    'id'                         => 'p1',
                    'name'                       => 'Fast Runner',
                    'competition_days'           => [
                        '2024-07-15' => [
                            'first' => $startDate.' 10:00:00',
                            'last'  => $startDate.' 11:00:00',
                        ],
                        '2024-07-16' => [
                            'first' => '2024-07-16 09:00:00',
                            'last'  => '2024-07-16 09:00:00',
                        ],
                    ],
                ],
            ],
        ]);

        Livewire::test(EditEventLogistic::class, ['record' => $logistic->getRouteKey()])
            ->callAction('prepare_document');

        $document = $logistic->refresh()->document;
        $schedules = $document->travel_data['data']['competition_schedules'];

        $this->assertStringContainsString('Fast Runner : (lun.) 10:00 - 11:00, (mar.) 09:00', $schedules);
    }
}
