<?php

return [

    'resource_types' => [
        'year_plan' => 'Plan annuel (macro-cycle)',
        'meso_plan' => 'Plan de méso-cycle (période)',
        'micro_plan' => 'Plan de micro-cycle (jours)',
        'week_plan' => 'Plan hebdomadaire',
        'day_plan' => 'Plan journalier',
        'session' => 'Séance',
        'sessions' => 'Suite de séances',
        'exercises' => 'Suite d\'exercices',
        'documentation' => 'Documentation',
    ],

    'weekdays' => [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
        7 => 'Dimanche',
    ],

    'date_format' => 'd.m.Y',

    'timezone' => 'Europe/Zurich',

    'password_protected' => true,
    'passwords' => ['youpi!'],
];
