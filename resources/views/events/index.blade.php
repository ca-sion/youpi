<x-layouts.app>

    <main class="pt-8 pb-16 lg:pt-16 lg:pb-24 bg-white dark:bg-gray-900 antialiased">
        <section class="table px-4 mx-auto mb-6 max-w-screen-xl">
            <div class="inline-flex rounded-md shadow-sm" role="group">
                <a href="{{ route('events.index', ['acg' => null]) }}" class="px-4 py-2 text-sm font-medium text-gray-900 bg-transparent border border-gray-900 rounded-l-lg hover:bg-gray-900 hover:text-white focus:z-10 focus:ring-2 focus:ring-gray-500 focus:bg-gray-900 focus:text-white dark:border-white dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:bg-gray-700">
                Tout
                </a>
                <a href="{{ route('events.index', ['acg' => 'u14m']) }}" class="px-4 py-2 text-sm font-medium text-gray-900 bg-transparent border-t border-b border-gray-900 hover:bg-gray-900 hover:text-white focus:z-10 focus:ring-2 focus:ring-gray-500 focus:bg-gray-900 focus:text-white dark:border-white dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:bg-gray-700">
                    U14 et plus jeunes
                </a>
                <a href="{{ route('events.index', ['acg' => 'u16p']) }}" class="px-4 py-2 text-sm font-medium text-gray-900 bg-transparent border border-gray-900 rounded-r-md hover:bg-gray-900 hover:text-white focus:z-10 focus:ring-2 focus:ring-gray-500 focus:bg-gray-900 focus:text-white dark:border-white dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:bg-gray-700">
                    U16 et plus âgés
                </a>
            </div>
        </section>
        <section class="flex justify-between px-4 mx-auto max-w-screen-xl">
            <div class="relative overflow-x-auto mx-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                Nom
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Lieu
                            </th>
                            <th scope="col" class="px-6 py-3">
                                 
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Concerne
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($events as $event)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                <a href="{{ $event->url }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">{{ $event->name }}</a>
                            </th>
                            <td class="px-6 py-4">
                                {{ $event->starts_at->isoFormat('DD.MM.YYYY') }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $event->location }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $event->codes }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $event->getAthleteCategories }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</x-layouts.app>
