<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\EventLogistic;
use App\Services\LogisticsMessageGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LogisticsMessageGeneratorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_comp_info_long_with_exact_format()
    {
        $logistic = EventLogistic::factory()->create([
            'name' => 'Meeting du Soir',
            'settings' => [
                'start_date' => '2026-06-15',
                'survey_deadline_at' => '2026-06-10 20:00:00',
            ],
            'participants_data' => [
                ['id' => '1', 'name' => 'Pierre Dupont', 'role' => 'athlete'],
                ['id' => '2', 'name' => '[E] Marc Coach', 'role' => 'coach'],
            ],
        ]);

        $message = LogisticsMessageGenerator::generate($logistic, [
            'template' => 'comp_info_long',
            'location' => 'Sion',
            'trainers_XXX' => 'U14/U16',
        ]);

        $this->assertStringContainsString('*Aux entraîneurs U14/U16*', $message);
        $this->assertStringContainsString('*Aux athlètes concernés*', $message);
        $this->assertStringContainsString('Meeting du Soir', $message);
        $this->assertStringContainsString('Lundi 15 juin 2026', $message);
        $this->assertStringContainsString('Pierre Dupont', $message);
        $this->assertStringContainsString('---- ⏱ Accompagnement/présence', $message);
        $this->assertStringContainsString('https://casion.ch/entrainements', $message);
    }

    /** @test */
    public function it_generates_travel_preliminary_message()
    {
        $logistic = EventLogistic::factory()->create([
            'name' => 'Championnats Suisses',
            'settings' => [
                'start_date' => '2026-07-10',
            ],
            'participants_data' => [
                ['id' => '1', 'name' => 'Athlete 1', 'role' => 'athlete'],
            ],
        ]);

        $message = LogisticsMessageGenerator::generate($logistic, [
            'template' => 'travel_preliminary',
            'location' => 'Genève',
            'hotel_link' => 'https://hotel-example.com',
        ]);

        $this->assertStringContainsString('Bonjour,', $message);
        $this->assertStringContainsString('Championnats Suisses', $message);
        $this->assertStringContainsString('https://hotel-example.com', $message);
        $this->assertStringContainsString('Athlete 1', $message);
        $this->assertStringContainsString('Michael Ravedoni, Chef technique', $message);
    }

    /** @test */
    public function it_generates_travel_survey_message()
    {
        $logistic = EventLogistic::factory()->create([
            'name' => 'CSI U16',
            'settings' => [
                'start_date' => '2026-09-01',
            ],
        ]);

        $message = LogisticsMessageGenerator::generate($logistic, [
            'template' => 'travel_survey',
            'location' => 'Lausanne',
        ]);

        $this->assertStringContainsString('---- 🚗 *Déplacement*', $message);
        $this->assertStringContainsString('---- 🛏️ *Hébergement*', $message);
        $this->assertStringContainsString('Michael Ravedoni, Chef technique', $message);
    }

    /** @test */
    public function it_generates_travel_expenses_message()
    {
        $logistic = EventLogistic::factory()->create([
            'name' => 'Meeting de Genève',
            'settings' => [
                'start_date' => '2026-05-10',
                'distance_km' => 150,
            ],
        ]);

        $message = LogisticsMessageGenerator::generate($logistic, [
            'template' => 'travel_expenses',
            'location' => 'Genève',
        ]);

        $this->assertStringContainsString('Art. 20 du règlement du CA Sion', $message);
        $this->assertStringContainsString('300 km (Aller-Retour)', $message);
        $this->assertStringContainsString('60.00 CHF', $message);
        $this->assertStringContainsString('https://casion.ch/forms/demande-remboursement-note-de-frais', $message);
    }

    /** @test */
    public function it_resolves_passenger_ids_to_names_in_travel_plan()
    {
        $logistic = EventLogistic::factory()->create([
            'name' => 'Meeting de Lausanne',
            'settings' => ['start_date' => '2026-08-15'],
            'participants_data' => [
                ['id' => 'p1-uuid', 'name' => 'Jean Dupont', 'role' => 'athlete'],
                ['id' => 'p2-uuid', 'name' => 'Marie Curie', 'role' => 'athlete'],
            ],
            'transport_plan' => [
                '2026-08-15' => [
                    [
                        'name' => 'Bus Aller',
                        'driver' => 'Chauffeur Bus',
                        'departure_datetime' => '2026-08-15 07:30:00',
                        'departure_location' => 'Gare de Sion',
                        'passengers' => ['p1-uuid', 'p2-uuid'],
                    ],
                ],
            ],
        ]);

        $message = LogisticsMessageGenerator::generate($logistic, [
            'template' => 'travel_plan',
        ]);

        $this->assertStringContainsString('Départ 07h30 (Gare de Sion)', $message);
        $this->assertStringContainsString('Jean Dupont, Marie Curie', $message);
        $this->assertStringNotContainsString('p1-uuid', $message);
    }
}
