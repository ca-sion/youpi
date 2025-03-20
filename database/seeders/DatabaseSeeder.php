<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\AthleteGroup;
use App\Models\Document;
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

        $documents = [
            [
                'name' => 'Information aux parents',
                'published_on' => now()->subDays(100),
                'type' => 'information',
                'status' => 'draft',
                'salutation' => 'Chers athlètes,
                Chers parents,',
                'signature' => 'Jean Dupont
                Responsable des AAA',
                'author' => 'Jean Dupont',
                'sections' => [["type" => "paragraph","data" => ["content" => "<p>Pandente itaque viam fatorum sorte tristissima, qua praestitutum erat eum vita et imperio spoliari, itineribus interiectis permutatione iumentorum emensis venit Petobionem oppidum Noricorum, ubi reseratae sunt insidiarum latebrae omnes, et Barbatio repente apparuit comes, qui sub eo domesticis praefuit, cum Apodemio agente in rebus milites ducens, quos beneficiis suis oppigneratos elegerat imperator certus nec praemiis nec miseratione ulla posse deflecti.</p>"]],["type" => "description","data" => ["heading" => "Quod alia quaedam","content" => "Ac ne quis a nobis hoc ita dici forte miretur, quod alia quaedam in hoc facultas sit ingeni, neque haec dicendi ratio aut disciplina, ne nos quidem huic uni studio penitus umquam dediti fuimus. Etenim omnes artes, quae ad humanitatem pertinent, habent quoddam commune vinculum, et quasi cognatione quadam inter se continentur.\n\nHabent quoddam commune vinculum, et quasi cognatione quadam inter se continentur."]],["type" => "description","data" => ["heading" => "AAA","content" => "Ac ne quis a nobis hoc ita dici forte miretur\n* quod alia quaedam in hoc facultas sit ingeni, neque haec dicendi ratio aut disciplina, ne nos quidem huic uni studio penitus umquam dediti fuimus.\n* Etenim omnes artes, quae ad humanitatem pertinent, habent quoddam commune vinculum, et quasi cognatione quadam inter se continentur."]],["type" => "block","data" => ["content" => "<p><strong>Important</strong></p><ul><li>Ac ne quis a nobis hoc ita dici forte miretur</li><li>quod alia quaedam in hoc facultas sit ingeni, neque haec dicendi ratio aut disciplina, ne nos quidem huic uni studio penitus umquam dediti fuimus.</li><li>Etenim omnes artes, quae ad humanitatem pertinent, habent quoddam commune vinculum, et quasi cognatione quadam inter se continentur.</li></ul>"]],["type" => "paragraph","data" => ["content" => "<p>Pandente itaque viam fatorum sorte tristissima, qua praestitutum erat eum vita et imperio spoliari, itineribus interiectis permutatione iumentorum emensis venit Petobionem oppidum Noricorum, ubi reseratae sunt insidiarum latebrae omnes, et <strong>Barbatio repente apparuit</strong> comes, qui sub eo domesticis praefuit, cum Apodemio agente in rebus milites ducens, quos beneficiis suis<a href=\"https://google.ch\"> oppigneratos elegerat</a> imperator certus nec praemiis nec miseratione ulla posse deflecti.</p><p>Pandente itaque viam fatorum sorte tristissima, qua praestitutum erat eum vita et imperio spoliari, itineribus interiectis permutatione iumentorum emensis venit Petobionem oppidum Noricorum, ubi reseratae sunt insidiarum latebrae omnes, et Barbatio repente apparuit <span style=\"text-decoration: underline;\">comes</span>, qui sub eo domesticis praefuit, cum Apodemio agente in rebus milites ducens, quos beneficiis suis oppigneratos elegerat imperator certus nec praemiis nec miseratione ulla posse deflecti.</p>"]],["type" => "description","data" => ["heading" => "Insidiarum latebrae omnes","content" => "Vita et imperio spoliari, itineribus interiectis permutatione"]],["type" => "description","data" => ["heading" => "Oppigneratos","content" => "Miseratione ulla"]]],
            ],
            [
                'name' => 'Notice aux entraîneurs',
                'published_on' => now()->subDays(10),
                'type' => 'notice',
                'status' => 'validated',
                'salutation' => null,
                'signature' => null,
                'author' => null,
                'sections' => null,
            ],
            [
                'name' => 'Notice aux parents',
                'published_on' => now()->subDays(35),
                'type' => 'notice',
                'status' => 'validated',
                'salutation' => null,
                'signature' => null,
                'author' => null,
                'sections' => null,
            ],
            [
                'name' => 'Notice aux entraîneurs : deuxième notice',
                'published_on' => now()->subDays(400),
                'type' => 'notice',
                'status' => 'expired',
                'salutation' => null,
                'signature' => null,
                'author' => null,
                'sections' => null,
            ],
            [
                'name' => 'Lettre aux athlètes : sortie surprise',
                'published_on' => now()->subDays(2),
                'type' => 'letter',
                'status' => 'validated',
                'salutation' => null,
                'signature' => null,
                'author' => null,
                'sections' => null,
            ]
        ];

        foreach ($documents as $document) {
            Document::create($document);
        }
    }
}
