<x-layouts.app>
    <div class="mx-auto max-w-4xl mb-10 mt-6">

        <section>
            <div class="flex justify-center gap-x-4">
                <a href="{{ route('program') }}" class="block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                    <h2 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Programmes</h2>
                    <p class="font-normal text-gray-700 dark:text-gray-400">Tous les plans d'entraînements, le planification aisni que les resources nécessaires aux athlètes.</p>
                </a>
                <a href="{{ route('events.index') }}" class="block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                    <h2 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Calendrier</h2>
                    <p class="font-normal text-gray-700 dark:text-gray-400">Calendrier des compétitions et de la vie du club.</p>
                </a>
            </div>
        </section>

    </div>

</x-layouts.app>
