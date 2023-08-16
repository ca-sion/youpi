<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\AthleteGroup;
use App\Models\Resource;
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
            ['name' => 'Sauts'],
            ['name' => 'Multiples'],
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
    }
}
