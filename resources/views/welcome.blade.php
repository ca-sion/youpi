<x-layouts.app>

    <div class="mx-auto max-w-4xl mb-10 mt-6 px-4">

        <section>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="grid mx-auto">
                    <a href="{{ route('program') }}" class="max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                        <h2 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Programmes</h2>
                        <p class="font-normal text-gray-700 dark:text-gray-400">Tous les plans d'entraînements, le planification aisni que les resources nécessaires aux athlètes.</p>
                    </a>
                </div>
                @if (session()->get('protect_in'))
                <div class="grid mx-auto">
                    <a href="{{ route('documents.index') }}" class="max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                        <h2 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Documents</h2>
                        <p class="font-normal text-gray-700 dark:text-gray-400">Tous les documents publics officiels du CA Sion à destination des entraîneurs et athlètes.</p>
                    </a>
                </div>
                @endif
            </div>
        </section>

    </div>

</x-layouts.app>
