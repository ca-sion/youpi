<x-layouts.full>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background-color: #eef2ff;
        }

        .voucher-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-top: 10px solid #ce1f1f;
        }
    </style>
    <div>
        <div class="voucher-container">
            <div class="mb-6 flex items-center justify-between border-b pb-4">
                <img src="https://casion.ch/assets/logo/logo-casion.svg"
                    alt="Logo"
                    width="80">
                <h1 class="text-3xl font-extrabold text-gray-800">CA Sion</h1>
                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">BON GRATUIT</span>
            </div>

            <h2 class="mb-6 text-xl font-bold text-gray-800">
                Bon pour un T-Shirt
            </h2>

            <div class="space-y-4">
                <div class="rounded-lg bg-blue-50 p-4">
                    <p class="text-sm text-blue-800">Athlète concerné :</p>
                    <p class="text-2xl font-extrabold text-blue-900">{{ $voucher->athlete_name }}</p>
                </div>

                <div class="rounded-lg bg-red-50 p-4">
                    <p class="text-sm text-red-800">Taille demandée :</p>
                    <p class="text-3xl font-extrabold text-red-900">{{ $voucher->tshirt_size }}</p>
                </div>

                <div class="rounded-lg bg-gray-50 p-4 text-center">
                    <p class="mb-2 text-sm font-semibold text-gray-500">Code du bon :</p>
                    <p class="break-all font-mono text-lg font-bold tracking-wider text-gray-900">{{ $voucher->code_unique }}</p>
                </div>
            </div>

            <div class="mt-6 text-center text-sm text-gray-500">
                <p>Date d'émission : {{ \Carbon\Carbon::parse($voucher->date_emission)->isoFormat('LL') }}</p>
                <p class="font-bold">Valide jusqu'au : {{ \Carbon\Carbon::parse($voucher->date_validity)->isoFormat('LL') }}</p>
                @if ($voucher->status === 'used')
                    <p class="mt-2 text-xl font-bold text-red-600">STATUT : UTILISÉ LE {{ \Carbon\Carbon::parse($voucher->used_at)->isoFormat('LL') }}</p>
                @endif
                <p class="mt-4">Émis par : {{ $voucher->coach_name }}</p>
            </div>

            <div class="mt-6 border-t border-dashed pt-4">
                <p class="mb-2 text-lg font-semibold text-gray-700">Instructions pour l'athlète :</p>
                <ul class="list-inside list-disc space-y-1 text-sm text-gray-600">
                    <li>Montrer ce bon à Theytaz Excursions pour recevoir <strong class="underline">un</strong> T-shirt du CA Sion.</li>
                    <li>Adresse de Theytaz Excursions : Avenue des Mayennets 7, 1950 Sion (<a class="underline"
                            href="https://maps.app.goo.gl/8Q9zkHVXcXGaXx6aA"
                            target="_blank">Carte</a>)</li>
                </ul>
            </div>

            <div class="mt-6 border-t border-dashed pt-4">
                <p class="mb-2 text-lg font-semibold text-gray-700">Instructions pour Theytaz :</p>
                <ul class="list-inside list-disc space-y-1 text-sm text-gray-600">
                    <li>Ce bon est valable pour <strong class="underline">un</strong> T-shirt du CA Sion.</li>
                    <li>Veuillez remettre la taille indiquée ci-dessus.</li>
                </ul>
            </div>
        </div>

        {{-- Script pour l'impression si le parent le souhaite --}}
        <div class="my-6 text-center">
            <button class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white shadow-md hover:bg-indigo-700 print:hidden" onclick="window.print()">
                Imprimer (Optionnel)
            </button>
        </div>
    </div>
</x-layouts.full>
