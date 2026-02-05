<?php

namespace Tests\Feature;

use App\Models\EventLogistic;
use App\Models\Document;
use App\Enums\DocumentType;
use App\Enums\DocumentStatus;
use App\Filament\Resources\EventLogisticResource\Pages\EditEventLogistic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Carbon\Carbon;

class DocumentPreparationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_new_document_if_none_linked()
    {
        $logistic = EventLogistic::factory()->create([
            'name' => 'Event Test',
            'settings' => ['start_date' => '2024-07-15'],
        ]);

        $this->assertNull($logistic->document_id);

        Livewire::test(EditEventLogistic::class, ['record' => $logistic->getRouteKey()])
            ->callAction('prepare_document');

        $logistic->refresh();
        $this->assertNotNull($logistic->document_id);
        
        $document = $logistic->document;
        $this->assertEquals('Document Voyage - Event Test', $document->name);
        $this->assertEquals(DocumentType::TRAVEL, $document->type);
    }

    /** @test */
    public function it_replaces_data_if_document_already_exists()
    {
        $document = Document::create([
            'name' => 'Existing Doc',
            'type' => DocumentType::TRAVEL,
            'status' => DocumentStatus::VALIDATED,
            'travel_data' => ['data' => ['old_key' => 'old_value']]
        ]);

        $logistic = EventLogistic::factory()->create([
            'name' => 'Event Updated',
            'document_id' => $document->id,
            'settings' => ['start_date' => '2024-07-15'],
        ]);

        Livewire::test(EditEventLogistic::class, ['record' => $logistic->getRouteKey()])
            ->callAction('prepare_document');

        $document->refresh();
        $this->assertArrayNotHasKey('old_key', $document->travel_data['data']);
        $this->assertEquals('Event Updated', $document->travel_data['data']['competition']);
    }

    /** @test */
    public function it_maps_transport_plan_and_independents()
    {
        $startDate = '2024-07-15';
        $logistic = EventLogistic::factory()->create([
            'name' => 'Transport Event',
            'settings' => ['start_date' => $startDate, 'days_count' => 1],
            'participants_data' => [
                ['id' => 'p1', 'name' => 'Bus Athlete', 'survey_response' => ['responses' => [$startDate => ['aller' => ['mode' => 'bus']]]]],
                ['id' => 'p2', 'name' => 'Train Athlete', 'survey_response' => ['responses' => [$startDate => ['aller' => ['mode' => 'train']]]]],
            ],
            'transport_plan' => [
                $startDate => [
                    [
                        'id' => 'v1',
                        'type' => 'bus',
                        'flow' => 'aller',
                        'departure_datetime' => $startDate . ' 08:00:00',
                        'departure_location' => 'Sion',
                        'driver' => 'Jean',
                        'passengers' => ['p1']
                    ]
                ]
            ]
        ]);

        Livewire::test(EditEventLogistic::class, ['record' => $logistic->getRouteKey()])
            ->callAction('prepare_document');

        $document = $logistic->refresh()->document;
        $travelData = $document->travel_data['data'];

        // Check Bus
        $this->assertCount(2, $travelData['departures']); // 1 Bus + 1 Independent
        $busEntry = collect($travelData['departures'])->firstWhere('means', 'Bus');
        $this->assertEquals('Bus Athlete', $busEntry['travelers']);
        $this->assertEquals('Jean', $busEntry['driver']);

        // Check Independent
        $indepEntry = collect($travelData['departures'])->firstWhere('means', 'Par ses propres moyens');
        $this->assertEquals('Train Athlete', $indepEntry['travelers']);
        $this->assertEquals('Individuel', $indepEntry['location']);
    }

    /** @test */
    public function it_maps_stay_plan()
    {
        $startDate = '2024-07-15';
        $logistic = EventLogistic::factory()->create([
            'settings' => ['start_date' => $startDate],
            'participants_data' => [
                ['id' => 'p1', 'name' => 'Sleeper'],
            ],
            'stay_plan' => [
                $startDate => [
                    [
                        'id' => 'r1',
                        'occupant_ids' => ['p1'],
                    ]
                ]
            ]
        ]);

        Livewire::test(EditEventLogistic::class, ['record' => $logistic->getRouteKey()])
            ->callAction('prepare_document');

        $document = $logistic->refresh()->document;
        $nights = $document->travel_data['data']['nights'];

        $this->assertCount(1, $nights);
        $this->assertEquals('Sleeper', $nights[0]['travelers']);
    }

    /** @test */
    public function it_maps_athlete_schedules()
    {
        $startDate = '2024-07-15';
        $logistic = EventLogistic::factory()->create([
            'settings' => ['start_date' => $startDate],
            'participants_data' => [
                [
                    'id' => 'p1', 
                    'name' => 'Fast Runner', 
                    'first_competition_datetime' => $startDate . ' 10:00:00',
                    'last_competition_datetime' => $startDate . ' 11:00:00',
                ],
            ],
        ]);

        Livewire::test(EditEventLogistic::class, ['record' => $logistic->getRouteKey()])
            ->callAction('prepare_document');

        $document = $logistic->refresh()->document;
        $schedules = $document->travel_data['data']['competition_schedules'];

        $this->assertStringContainsString('Fast Runner : (lun.) 10:00 - 11:00', $schedules);
    }
}
