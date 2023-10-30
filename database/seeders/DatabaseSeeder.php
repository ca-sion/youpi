<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\AthleteGroup;
use App\Models\Event;
use App\Models\Resource;
use App\Models\Trainer;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Michael',
            'email' => 'ravemiki@gmail.com',
            'password' => '$2y$10$pYCzZCK7apOxKN452BMr.u9hCVUhemMmKbeiZcR86/CAhOiDEesAC'
        ]);

        $athleteGroups = [
            [
                'name' => 'Sauts',
                'categories' => ['u18', 'u20', 'u23', 'senior'],
            ],
            [
                'name' => 'Multiples',
                'categories' => ['u18', 'u20', 'u23', 'senior'],
            ],
            [
                'name' => now()->subYears(13)->format('Y'),
                'categories' => ['u14'],
            ],
        ];

        foreach ($athleteGroups as $athleteGroup) {
            AthleteGroup::create($athleteGroup);
        }

        $resources = [
            [
                'name' => 'Semaine dure',
                'type' => 'week_plan',
                'attachment_type' => 'text',
                'text'=> '<p>Ceci est le <strong>plan</strong>.</p><p><span style=""text-decoration: underline;"">Youpi !</span></p>',
                'author' => 'Michael',
                'date' => now()->startOfWeek()->toDateString(),
                'athlete_group_id' => 1,
            ],
            [
                'name' => 'Semaine facile',
                'type' => 'week_plan',
                'attachment_type' => 'text',
                'text'=> '<p>Texte du plan facile</p>',
                'author' => 'Michael',
                'date' => now()->addWeek()->startOfWeek()->toDateString(),
                'athlete_group_id' => 1,
            ],
            [
                'name' => 'Plan annuel',
                'type' => 'year_plan',
                'attachment_type' => 'url',
                'url'=> 'https://example.com',
                'author' => 'Michael',
                'date' => now()->startOfYear()->toDateString(),
                'date_end' => now()->endOfYear()->toDateString()
            ],
            [
                'name' => 'Session protégée',
                'type' => 'session',
                'attachment_type' => 'url',
                'url'=> 'https://example.com',
                'author' => 'Michael',
                'date' => now()->toDateString(),
                'athlete_group_id' => 1,
                'is_protected' => 1,
                'available_time_start' => '18:00',
            ],
            [
                'name' => null,
                'type' => 'session',
                'attachment_type' => 'text',
                'text'=> '<p>Texte</p>',
                'author' => 'Michael',
                'date' => now()->addDay()->toDateString(),
                'athlete_group_id' => 2,
            ],
            [
                'name' => null,
                'type' => 'session',
                'attachment_type' => 'text',
                'text'=> '<p>Texte</p>',
                'author' => 'Michael',
                'date' => now()->toDateString(),
                'athlete_group_id' => 2,
            ],
            [
                'name' => 'Renforcement du dos - Niveau 1',
                'type' => 'exercises',
                'attachment_type' => 'text',
                'text'=> '<p>C\'est une suite d\'exercices.</p>',
                'author' => 'Jean',
                'date' => now()->subMonth()->toDateString(),
                'athlete_group_id' => 2,
            ],
        ];

        foreach ($resources as $resource) {
            Resource::create($resource);
        }

        $events = [
            [
                'name' => 'Compétition du soleil',
                'starts_at' => now()->addDays(60),
                'status' => 'planned',
                'types' => ['competition','club_life'],
                'athlete_categories' => ['u10','u12','u14'],
                'athlete_category_groups' => ['u14m'],
                'has_deadline' => true,
                'deadline_type' => 'tiiva',
                'deadline_at' => now()->addDays(40),
                'has_entrants' => true,
                'entrants_type' => 'url',
                'entrants_url' => 'https://example.com',
                'has_provisional_timetable' => true,
                'provisional_timetable_url' => 'https://example.com',
                'provisional_timetable_text' => 'Horaire à vérifier le jeudi avant la compétition.',
                'has_trainers_presences' => true,
                'trainers_presences_type' => 'table',
            ],
            [
                'name' => 'Compétition du soir',
                'starts_at' => now()->addDays(30),
                'status' => 'planned',
                'athlete_category_groups' => ['u16p'],
            ],
            [
                'name' => 'Sortie du soleil',
                'starts_at' => now()->addDays(5),
                'status' => 'provisional',
                'types' => ['club_life'],
                'athlete_category_groups' => ['u14m', 'u16p'],
            ],
        ];

        foreach ($events as $event) {
            Event::create($event);
        }

        $trainers = [
            [
                'name' => 'Arthur Rimbaud',
                'email' => 'arthur@gmail.com',
                'phone' => '+41791231212',
            ],
            [
                'name' => 'Ana Lunay',
                'email' => null,
                'phone' => null,
            ],
            [
                'name' => 'David Lunay',
                'email' => null,
                'phone' => null,
            ]
        ];

        foreach ($trainers as $trainer) {
            Trainer::create($trainer);
        }
    }
}
