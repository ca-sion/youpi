<div>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f7f9fc; }
        .voucher-card {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-left: 8px solid #ce1f1f; /* Bleu club */
        }
        .dotted-border {
            border: 2px dashed #e5e7eb;
        }
    </style>

    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-2 text-center">
            Émission d'un bon
        </h1>
        <p class="text-xl font-extrabold text-gray-900 mb-6 text-center">T-shirt gratuit</p>

        @if ($newVoucher)
            <!-- AFFICHAGE DU BON ÉMIS -->
            <div class="bg-white p-8 rounded-xl voucher-card shadow-lg text-gray-800">
                <h2 class="text-2xl font-bold text-red-600 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.275a1.99 1.99 0 00-2.827 0L6 14.174V18h3.826l8.82-8.82a1.99 1.99 0 000-2.827z"></path></svg>
                    Bon T-shirt émis !
                </h2>

                <p class="text-lg mb-6">
                    Copiez le lien ci-dessous et transférez-le immédiatement au parent ou à l'athlète. C'est la preuve numérique à présenter au magasin.
                </p>

                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Lien du bon numérique (à transmettre à l'athlète/parent) :</label>
                    @php
                        $voucherUrl = route('vouchers.show', ['code' => $newVoucher->code_unique]);
                    @endphp
                    <a href="{{ $voucherUrl }}" target="_blank" class="block text-blue-600 hover:underline break-all">
                        {{ $voucherUrl }}
                    </a>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 text-sm bg-blue-50 p-4 rounded-lg">
                    <p><strong>Athlète :</strong> {{ $newVoucher->athlete_name }}</p>
                    <p><strong>Taille Demandée :</strong> <span class="font-bold text-xl text-red-600">{{ $newVoucher->tshirt_size }}</span></p>
                    <p><strong>Émis par :</strong> {{ $newVoucher->coach_name }}</p>
                    <p><strong>Valide jusqu'au :</strong> {{ \Carbon\Carbon::parse($newVoucher->date_validity)->isoFormat('LL') }}</p>
                </div>
            </div>

            <div class="mt-6 text-center">
                <button wire:click="$set('newVoucher', null)" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-150 shadow-md">
                    Émettre un autre Bon
                </button>
            </div>

        @else
            <!-- AFFICHAGE DU FORMULAIRE D'ÉMISSION -->
            <form wire:submit="create" class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-100">
                <div class="space-y-6">
                    {{ $this->form }}
                </div>

                <div class="mt-8">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-150 shadow-lg flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Générer le Bon
                    </button>
                </div>
            </form>
        @endif

    </div>
</div>