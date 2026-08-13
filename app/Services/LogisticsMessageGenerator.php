<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\EventLogistic;

class LogisticsMessageGenerator
{
    public static function generate(EventLogistic $eventLogistic, array $options = []): string
    {
        $template = $options['template'] ?? 'comp_info_long';

        return match ($template) {
            'comp_info_short'   => static::generateCompInfoShort($eventLogistic, $options),
            'travel_preliminary'=> static::generateTravelPreliminary($eventLogistic, $options),
            'travel_survey'     => static::generateTravelSurvey($eventLogistic, $options),
            'travel_plan'       => static::generateTravelPlan($eventLogistic, $options),
            'travel_expenses'   => static::generateTravelExpenses($eventLogistic, $options),
            default             => static::generateCompInfoLong($eventLogistic, $options),
        };
    }

    /**
     * Modèle 1 : Compétition - Info Générale (Long)
     * Basé exactement sur Compétition-Info.txt (Lignes 1-61)
     */
    protected static function generateCompInfoLong(EventLogistic $eventLogistic, array $options): string
    {
        $settings = $eventLogistic->settings ?? [];
        $startDateStr = $settings['start_date'] ?? null;
        $dateFormatted = $startDateStr ? ucfirst(Carbon::parse($startDateStr)->translatedFormat('l d F Y')) : 'JOUR DATE';
        $location = ! empty($options['location']) ? $options['location'] : ($settings['location'] ?? 'LIEU');

        $deadlineStr = static::getFormattedDeadline($eventLogistic);
        $surveyUrl = route('logistics.survey', $eventLogistic);
        $showUrl = route('logistics.show', $eventLogistic);

        $participants = collect($eventLogistic->participants_data ?? []);
        $athletesList = $participants
            ->filter(fn ($p) => ($p['role'] ?? '') !== 'coach' && ! str_contains($p['name'] ?? '', '[E]'))
            ->pluck('name')
            ->implode(', ');

        $trainersStr = $options['trainers_XXX'] ?? 'XXX';

        $lines = [];
        $lines[] = "*Aux entraîneurs {$trainersStr}*";
        $lines[] = "*Aux athlètes concernés*";
        $lines[] = "*Aux parents concernés*";
        $lines[] = "Le {$dateFormatted} a lieu les *{$eventLogistic->name}* à {$location}. Vous trouverez ci-après les informations nécessaires.";
        $lines[] = "";
        $lines[] = "---- 📝 Inscription";

        $regType = $options['registration_type'] ?? 'tiiva';
        if ($regType === 'convocation') {
            $lines[] = "<CONVOCATION>";
            $lines[] = "Les convocations ont normalement été transmises soit par vous, soit directement aux athlètes par les chefs d'équipe. Il n'y a donc pas besoin de s'inscrire. *La présence de tous les athlètes convoqués est obligatoire*. Prière de contacter le chef d'équipe en cas de désistement.";
        } elseif ($regType === 'qualification') {
            $qualUrl = $options['qualification_url'] ?? 'URL';
            $lines[] = "<QUALIFICATION>";
            $lines[] = "- Liste des qualifiés : {$qualUrl}";
            if (! empty($options['qualified_athletes'])) {
                $lines[] = "Les athlètes qualifiés sont les suivants :";
                $lines[] = $options['qualified_athletes'];
            }
            $lines[] = "Les invitations ont peut-être été reçues par les parents. Merci alors de regarder avec eux.";
        } else {
            $lines[] = "- Délai : {$deadlineStr}";
            $lines[] = "- Où : sur Tiiva <OU> Sondage logistique : {$surveyUrl}";
        }

        $lines[] = "";
        $lines[] = "---- ✅ Liste des inscrits : {$showUrl}";
        $lines[] = "Merci de contrôler la liste des athlètes inscrits.";
        $lines[] = "---- ✅ Inscrits pour le moment : " . ($athletesList ?: 'ATHLETES');

        $docUrl = $eventLogistic->document ? route('documents.show', $eventLogistic->document) : 'URL';
        $lines[] = "";
        $lines[] = "---- 🕔 Horaires provisoire / définitif : {$docUrl}";
        $lines[] = "---- ℹ️ Informations : {$docUrl}";
        $lines[] = "---- 📐 Règlement : {$docUrl}";

        $lines[] = "";
        $lines[] = "---- 🚘 Déplacement";
        if (! empty($eventLogistic->transport_plan)) {
            $lines[] = "Un déplacement est organisé (selon Art. 20 du Règlement). Voici les informations : {$showUrl}";
            $lines[] = "Merci de communiquer rapidement si des changements sont à opérer. 🕒 Délai : *{$deadlineStr}*";
        } else {
            $lines[] = "Aucun déplacement n'est organisé (ou sondage en cours).";
            $lines[] = "Merci de compléter le sondage logistique : {$surveyUrl}";
        }

        $lines[] = "";
        $lines[] = "📝 Inscription : sur Tiiva par vos soins.";
        $lines[] = "⏱️ Attention au délai.";
        $lines[] = "⚠️ Rappel : 3 disciplines maximum par compétition (Art. 43 Politique sportive). Merci de contrôler/changer.";

        if ($regType === 'convocation') {
            $lines[] = "ℹ️ Rappel : Les CSI sont une compétition par équipe. Les chefs d'équipe de la CoAVR font les équipes avec les athlètes de 6 clubs valaisans qui se mesurent aux autres clubs suisses pour obtenir une médaille.";
            $lines[] = "❔ Indiquer le désidérata de la discipline dans le champ note. Le chef d'équipe essayera de faire de son mieux pour vous l'attribuer. Mais s'il ne peut pas, vous aurez peut-être une autre discipline. C'est le jeu des CSI ! Si vous vous inscrivez, vous devez venir. Question de respect vis-à-vis de l'équipe et des autres athlètes.";
        }

        $lines[] = "";
        $lines[] = "---- ⏱ Accompagnement/présence";
        $coachesCat = $options['coaches_by_cat'] ?? [];
        $lines[] = "- U10 : " . ($coachesCat['u10'] ?? 'NB ➝ ENTRAINEUR');
        $lines[] = "- U12 : " . ($coachesCat['u12'] ?? 'NB ➝ ENTRAINEUR');
        $lines[] = "- U14 : " . ($coachesCat['u14'] ?? 'NB ➝ ENTRAINEUR');
        $lines[] = "- U16 : " . ($coachesCat['u16'] ?? 'NB ➝ ENTRAINEUR');
        $lines[] = "- U18+ : " . ($coachesCat['u18'] ?? 'NB ➝ ENTRAINEUR');
        $lines[] = "Je cherche encore des entraîneurs pour les postes avec ?.";
        $lines[] = "⚠️ N'oubliez pas SVP de donner vos disponibilités dans Tiiva ! Vous recevez les emails pour vous rappeler de dire oui ou non. 2 clics suffisent ! 🙏";
        $lines[] = "Merci de me communiquer ⚠️ si vous ne pouvez pas être présent pour accompagner les athlètes inscrits pour la compétition.";

        $lines[] = "";
        $lines[] = "Pour rappel les 🗓 calendriers des compétitions et de la vie du club sont consultables en ligne. De plus, vous pouvez trouver plein de 📚 ressources et d'aide en ligne (fil rouge, exercices, FAQ, recommandations, formations) :";
        $lines[] = "---- https://casion.ch/entrainements";
        $lines[] = "---- https://casion.ch/hc-trainers";

        if (! empty($options['custom_note'])) {
            $lines[] = "";
            $lines[] = "ℹ️ " . trim($options['custom_note']);
        }

        $lines[] = "";
        $lines[] = "Je reste à disposition si vous avez des questions.";
        $lines[] = "Michael";

        return implode("\n", $lines);
    }

    /**
     * Modèle 2 : Compétition - Briefing Jour J / WhatsApp (Court)
     * Basé exactement sur Compétition-Info.txt (Lignes 63-101)
     */
    protected static function generateCompInfoShort(EventLogistic $eventLogistic, array $options): string
    {
        $settings = $eventLogistic->settings ?? [];
        $startDateStr = $settings['start_date'] ?? null;
        $dateFormatted = $startDateStr ? ucfirst(Carbon::parse($startDateStr)->translatedFormat('l d F Y')) : 'JOUR DATE';
        $location = ! empty($options['location']) ? $options['location'] : ($settings['location'] ?? 'LIEU');
        $showUrl = route('logistics.show', $eventLogistic);
        $docUrl = $eventLogistic->document ? route('documents.show', $eventLogistic->document) : $showUrl;

        $participants = collect($eventLogistic->participants_data ?? []);
        $athletes = $participants->filter(fn ($p) => ($p['role'] ?? '') !== 'coach' && ! str_contains($p['name'] ?? '', '[E]'));
        $coaches = $participants->filter(fn ($p) => ($p['role'] ?? '') === 'coach' || str_contains($p['name'] ?? '', '[E]'));

        $athletesStr = $athletes->pluck('name')->implode(', ') ?: 'ATHLETES';
        $coachesStr = $coaches->pluck('name')->map(fn ($n) => Str::replace('[E] ', '', $n))->implode(', ') ?: '@AAAA';

        $meetingTime = $options['meeting_time'] ?? 'xxhxx';
        $spikesInfo = $options['spikes_info'] ?? 'en céramique de 5mm. Vous pouvez en acheter sur place (6 CHF).';

        $lines = [];
        $lines[] = "---- court";
        $lines[] = "Infos samedi/dimanche";
        $lines[] = "*{$eventLogistic->name}*, {$dateFormatted} à {$location}";
        $lines[] = "- 👥 Inscrits : {$athletesStr}";
        $lines[] = "- 🕒 Rendez-vous : {$meetingTime}";
        $lines[] = "- 📍 Lieu : {$location} (plan : {$showUrl})";
        $lines[] = "- 👤 Entraîneur : {$coachesStr}";
        $lines[] = "- 🗓️ Horaire : Voir site / document";
        $lines[] = "- 🔢 Tours : Voir horaire";
        $lines[] = "- ℹ️ Infos : {$docUrl}";
        $lines[] = "- 🕔 Horaires : {$docUrl}";
        $lines[] = "- 📐 Règlement (à lire SVP): {$docUrl}";
        $lines[] = "- 👟 Pointes : {$spikesInfo}";
        $lines[] = "- 👥 Groupes : GROUPE : {$showUrl}";
        $lines[] = "- ⚠️ *Cocher* les listes de départ (Confirmation Board) au plus tard *60 minutes avant* le début de chaque compétition. Ne cocher pas pour les autres SVP, chacun est responsable (sauf urgence) ! Pas de coche = disqualification.";
        $lines[] = "- ⚠️ *Callroom* : arriver à la callroom au plus tard 60 minutes (perche), 30 minutes (sauts/lancers), 15 minutes avant le début du concours. Pour les demi-finales et finales aussi. Retard = disqualification. Lieu : voir plan.";
        $lines[] = "";
        $lines[] = "⚠️ Lisez le règlement, les directives et les modes de qualification pour être sûr. Vous êtes responsable de suivre le règlement et d'arriver à l'heure à la confirmation board et à la callroom. Prévoyez de l'avance avec les horaires de départ des voitures. Attention au parking (voir plan ci-dessus). Pour les nouveaux c'est une compétition où le respect des règles est primordiale sous peine de disqualification.";
        $lines[] = "> Résumé : garder *dossard deux jours* / *croix* 60 minutes avant horaire définitif (perche 90) / *callroom* 15-40-60 minutes avant, par série / *pas d'échauffement sur la piste* (voir plan) / quitter le concours ou la piste sur l'accord d'un juge (même toilettes) / *appareils électroniques interdits* / seuls les athlètes peuvent descendre au sous-sol.";
        $lines[] = "🎽 Prenez des habits chauds pour vous échauffer dehors : pantalons, bonnet, couverture. Attention à la météo s'il pleut/neige.";
        $lines[] = "🎽 Prenez des habits pour vous protéger du soleil : casquette, pantalons, linge. Attention à la météo s'il pleut.";
        $lines[] = "🍴 Prenez de quoi manger à midi ou avant/après vos concours et de l'eau (avec une pincée de sel et sirop).";

        $weather = (array) ($options['weather'] ?? []);
        if (in_array('hot', $weather)) {
            $lines[] = "🥵 Il va faire extrêmement chaud. Il y a très peu d'ombre sur ce stade. 🎽 Prenez des habits pour vous protéger du soleil en tout temps : casquette, pantalons, linge, parasol/pluie. *Casquette et gourde obligatoire*.";
        }
        if (in_array('cold', $weather)) {
            $lines[] = "🥶 Il va faire froid. 🎽 Prenez des habits pour vous protéger du froid en tout temps : bonnet, gants, *coupe-vent*, deux pulls, *couverture*, pantalons.";
        }
        if (in_array('rain', $weather)) {
            $lines[] = "☔️ Il va pleuvoir. 🎽 Prenez des habits pour vous protéger de la pluie en tout temps : *deux imperméables*, casquette, parapluie, pantalons, linge.";
        }

        if (! empty($options['include_checklist'])) {
            $lines[] = "🎒 A prendre dans le sac :";
            $lines[] = "- Baskets";
            $lines[] = "- T-shirt du club";
            $lines[] = "- Pointes";
            $lines[] = "- Scotch pour marques";
            $lines[] = "- *Casquette (obligatoire)*";
            $lines[] = "- *Gourde (obligatoire)*";
            $lines[] = "- Linge";
            $lines[] = "- Bas de training";
            $lines[] = "- Effets personnels (douche, habits de rechange, rouleau, etc.)";
            $lines[] = "- Vêtements selon le temps";
            $lines[] = "- Petit sac pour la callroom (pointes, scotch, gourde, habits selon le temps)";
        }

        if (! empty($options['custom_note'])) {
            $lines[] = "";
            $lines[] = "ℹ️ " . trim($options['custom_note']);
        }

        $lines[] = "";
        $lines[] = "Je reste à disposition si vous avez des questions.";

        return implode("\n", $lines);
    }

    /**
     * Modèle 3 : Déplacement - Infos Préliminaires
     * Basé exactement sur Déplacement-Infos_préliminaires.txt
     */
    protected static function generateTravelPreliminary(EventLogistic $eventLogistic, array $options): string
    {
        $settings = $eventLogistic->settings ?? [];
        $startDateStr = $settings['start_date'] ?? null;
        $dateFormatted = $startDateStr ? Carbon::parse($startDateStr)->translatedFormat('d F Y') : 'DATE MOIS YYYY';
        $location = ! empty($options['location']) ? $options['location'] : ($settings['location'] ?? 'LIEU');

        $participants = collect($eventLogistic->participants_data ?? []);
        $athletesList = $participants
            ->filter(fn ($p) => ($p['role'] ?? '') !== 'coach' && ! str_contains($p['name'] ?? '', '[E]'))
            ->pluck('name')
            ->implode(', ');

        $hotelUrl = $options['hotel_link'] ?? 'URL';
        $docUrl = $eventLogistic->document ? route('documents.show', $eventLogistic->document) : route('logistics.show', $eventLogistic);

        $lines = [];
        $lines[] = "Bonjour,";
        $lines[] = "Je vous contacte à propos des / du *{$eventLogistic->name}* qui se dérouleront / déroulera le {$dateFormatted} à {$location}. Le CA Sion est en train d'organiser l'hébergement et le déplacement des athlètes.";
        $lines[] = "";
        $lines[] = "🚗 *Transport*";
        $lines[] = "Le transport des athlètes se fera en bus ou en transports publics depuis Sion. Si l'athlète prévoit de voyager par ses propres moyens (en dehors de l'organisation), merci de nous en avertir (lors du sondage qui sera effectué prochainement). Petite note : en principe, un athlète qui viendrait par ses propres moyens, en dehors de l'organisation finale, ne verrait pas son trajet remboursé.";
        $lines[] = "";
        $lines[] = "🛏️ *Hébergement*";
        $lines[] = "Des chambres vont être réservées / ont déjà été réservées pour les athlètes qui devront dormir sur place. Il s'agit des athlètes dont la discipline a lieu le matin, qui ont une discipline sur plusieurs jours (qualification/finale) ou dont les disciplines sont étalées sur plusieurs jours de compétition.";
        $lines[] = "Si les parents et les accompagnants souhaitent réserver dans le même hôtel, voici le site web de l'établissement : {$hotelUrl}.";
        $lines[] = "";
        $lines[] = "✅ Liste des athlètes concernés : " . ($athletesList ?: 'ATHLETES') . ".";
        $lines[] = "Veuillez m'avertir en cas d'erreur.";
        $lines[] = "";
        $lines[] = "Je reste à votre disposition pour tout renseignement complémentaire.";
        $lines[] = "Vous trouverez plus d'informations sur la compétition à l'adresse : {$docUrl}.";
        $lines[] = "";
        $lines[] = "Michael Ravedoni, Chef technique";
        $lines[] = "Envoyé à : athlètes, entraîneurs et parents d'athlètes concernés";

        return implode("\n", $lines);
    }

    /**
     * Modèle 4 : Déplacement - Inscription & Sondage
     * Basé exactement sur Déplacement-Inscription.txt
     */
    protected static function generateTravelSurvey(EventLogistic $eventLogistic, array $options): string
    {
        $settings = $eventLogistic->settings ?? [];
        $startDateStr = $settings['start_date'] ?? null;
        $dateFormatted = $startDateStr ? Carbon::parse($startDateStr)->translatedFormat('l d F Y') : 'JOUR DATE';
        $location = ! empty($options['location']) ? $options['location'] : ($settings['location'] ?? 'LIEU');

        $surveyUrl = route('logistics.survey', $eventLogistic);
        $showUrl = route('logistics.show', $eventLogistic);
        $deadlineStr = static::getFormattedDeadline($eventLogistic);
        $docUrl = $eventLogistic->document ? route('documents.show', $eventLogistic->document) : $showUrl;

        $participants = collect($eventLogistic->participants_data ?? []);
        $stayAthletes = $participants
            ->filter(fn ($p) => ($p['survey_response']['hotel_needed'] ?? false) || ($p['hotel_override'] ?? false))
            ->pluck('name')
            ->implode(', ');

        $lines = [];
        $lines[] = "Bonjour, voici le groupe pour les/le *{$eventLogistic->name}* qui se dérouleront/déroulera le {$dateFormatted} à *{$location}*. Vous êtes sur ce groupe car vous êtes inscrit pour y participer.";
        $lines[] = "---- ✅ Liste des participants : {$showUrl}";
        $lines[] = "";
        $lines[] = "---- 🚗 *Déplacement*";
        $lines[] = "Un déplacement est organisé (selon Art. 20 du Règlement). Voici les informations : {$showUrl}";
        $lines[] = "OU";
        $lines[] = "Veuillez ✍️ remplir le sondage ci-dessous pour le déplacement. Ainsi je pourrai organiser au mieux. Le bus n'a que 9/12 places, merci donc d'indiquer si vous venez par vos propres moyens afin que je puisse remplir les voitures au départ de Sion.";
        $lines[] = "-- ✍️ Sondage : {$surveyUrl}";
        $lines[] = "-- 🕒 Délai : *{$deadlineStr}*";
        $lines[] = "";
        $lines[] = "---- 🛏️ *Hébergement*";
        $lines[] = "Des chambres ont déjà été réservées pour les athlètes qui devront dormir sur place. Il s'agit des athlètes qui ont des disciplines étalées sur plusieurs jours de compétition : " . ($stayAthletes ?: 'ATHLETES') . ".";
        $lines[] = "";
        $lines[] = "-- ℹ️ Informations : {$docUrl}";
        $lines[] = "-- 🕔 Horaire (provisoire) : {$docUrl}";
        $lines[] = "";
        $lines[] = "Je reste à votre disposition en cas de questions ou remarques.";
        $lines[] = "Michael Ravedoni, Chef technique";
        $lines[] = "Envoyé à : athlètes, entraîneurs et parents d'athlètes concernés";

        return implode("\n", $lines);
    }

    /**
     * Modèle 5 : Déplacement - Plan de Transport Définitif
     */
    protected static function generateTravelPlan(EventLogistic $eventLogistic, array $options): string
    {
        $settings = $eventLogistic->settings ?? [];
        $startDateStr = $settings['start_date'] ?? null;
        $dateFormatted = $startDateStr ? ucfirst(Carbon::parse($startDateStr)->translatedFormat('l d F Y')) : 'DATE';
        $showUrl = route('logistics.survey', $eventLogistic);

        $participants = collect($eventLogistic->participants_data ?? []);
        $participantsMap = $participants->pluck('name', 'id')->toArray();

        $lines = [];
        $lines[] = "*Aux athlètes, parents et entraîneurs*";
        $lines[] = "Voici les informations d'organisation des déplacements pour *{$eventLogistic->name}* ({$dateFormatted}) :";
        $lines[] = "";
        $lines[] = "---- 🚘 Plan de transport";

        $transportPlan = $eventLogistic->transport_plan ?? [];

        if (! empty($transportPlan)) {
            foreach ($transportPlan as $date => $vehicles) {
                $dateTitle = ucfirst(Carbon::parse($date)->translatedFormat('l d F'));
                $lines[] = "📅 *{$dateTitle}*";

                if (is_array($vehicles)) {
                    foreach ($vehicles as $vehicle) {
                        $vName = $vehicle['name'] ?? 'Véhicule';
                        $driver = $vehicle['driver'] ?? 'À définir';

                        // Parse departure time cleanly
                        $depTime = 'xxhxx';
                        if (! empty($vehicle['departure_datetime'])) {
                            $depTime = Carbon::parse($vehicle['departure_datetime'])->format('H\hi');
                        } elseif (! empty($vehicle['departure_time'])) {
                            $depTime = Str::replace(':', 'h', $vehicle['departure_time']);
                        }

                        $depLoc = $vehicle['departure_location'] ?? 'Sion';

                        // Map IDs to Participant Names
                        $passengerNames = [];
                        if (is_array($vehicle['passengers'] ?? null)) {
                            foreach ($vehicle['passengers'] as $pId) {
                                $passengerNames[] = $participantsMap[$pId] ?? $pId;
                            }
                            $passengers = ! empty($passengerNames) ? implode(', ', $passengerNames) : 'Aucun';
                        } else {
                            $passengers = $vehicle['passengers'] ?? 'Aucun';
                        }

                        $lines[] = "- *{$vName}* (Chauffeur: {$driver}) - Départ {$depTime} ({$depLoc}) : {$passengers}";
                    }
                }
                $lines[] = "";
            }
        } else {
            $lines[] = "Le plan détaillé des véhicules est en cours de finalisation.";
        }

        $lines[] = "---- 🔗 Consulter la vue complète en ligne : {$showUrl}";
        $lines[] = "";
        $lines[] = "Prévoyez de l'avance avec les horaires de départ des voitures. Merci de communiquer rapidement en cas d'imprévu.";
        $lines[] = "";
        $lines[] = "Je reste à disposition si vous avez des questions.";
        $lines[] = "Michael";

        return implode("\n", $lines);
    }

    /**
     * Modèle 6 : Déplacement - Remboursement des Frais (Art. 20)
     * Basé exactement sur Frais-Déplacement.txt
     */
    protected static function generateTravelExpenses(EventLogistic $eventLogistic, array $options): string
    {
        $settings = $eventLogistic->settings ?? [];
        $startDateStr = $settings['start_date'] ?? null;
        $dateFormatted = $startDateStr ? Carbon::parse($startDateStr)->translatedFormat('d F Y') : 'DATE';
        $location = ! empty($options['location']) ? $options['location'] : ($settings['location'] ?? 'LIEU');

        $distanceKm = (float) ($settings['distance_km'] ?? 0);
        $totalKm = $distanceKm * 2; // Aller-retour
        $ratePerKm = 0.20; // 20 centimes par km
        $totalChf = number_format($totalKm * $ratePerKm, 2, '.', '');

        $distStr = $totalKm > 0 ? "{$totalKm} km (Aller-Retour)" : "XX km";
        $chfStr = $totalKm > 0 ? "{$totalChf} CHF" : "XX CHF";

        $lines = [];
        $lines[] = "Bonjour, selon l'Art. 20 du règlement du CA Sion (https://casion.ch/reglement), le Club rembourse les trajets *prévus officiellement* dans l'organisation des déplacements. Ainsi, il est possible de se faire rembourser les frais de déplacement. Le Club rembourse ces frais à hauteur de 20 centimes le kilomètre ainsi que les billets en 2e classe des transports publics.";
        $lines[] = "";
        $lines[] = "Pour le déplacement à {$location} du {$dateFormatted}, le kilométrage est de :";
        $lines[] = "- {$distStr} ; soit";
        $lines[] = "- {$chfStr}.";
        $lines[] = "";
        $lines[] = "Pour demander le remboursement, il suffit de remplir le formulaire suivant avec vos coordonnées : https://casion.ch/forms/demande-remboursement-note-de-frais";
        $lines[] = "";
        $lines[] = "Je reste à disposition en cas de question.";
        $lines[] = "Michael, Chef technique";

        return implode("\n", $lines);
    }

    protected static function getFormattedDeadline(EventLogistic $eventLogistic): string
    {
        $settings = $eventLogistic->settings ?? [];
        $deadlineAt = $settings['survey_deadline_at'] ?? null;
        $deadlineDaysBefore = $settings['survey_deadline_days_before'] ?? null;
        $startDateStr = $settings['start_date'] ?? null;

        $deadline = null;
        if ($deadlineAt) {
            $deadline = \Carbon\Carbon::parse($deadlineAt);
        } elseif ($deadlineDaysBefore !== null && $startDateStr) {
            $deadline = \Carbon\Carbon::parse($startDateStr)->subDays((int) $deadlineDaysBefore)->startOfDay();
        }

        if (! $deadline) {
            return 'JOUR DATE 20h';
        }

        $formatted = ucfirst($deadline->translatedFormat('l d F Y'));
        if ($deadline->format('H:i') !== '00:00') {
            $formatted .= ' ' . $deadline->format('H\hi');
        } else {
            $formatted .= ' 20h';
        }

        return $formatted;
    }
}
