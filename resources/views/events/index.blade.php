<x-layouts.app>

    <main class="pt-8 pb-16 lg:pt-16 lg:pb-24 bg-white dark:bg-gray-900 antialiased">
        <section class="table px-4 mx-auto mb-6 max-w-screen-xl print:hidden">
            <div class="inline-flex rounded-md shadow-sm" role="group">
                <a href="{{ route('events.index', ['acg' => null]) }}" class="px-4 py-2 text-sm font-medium text-gray-900 border border-gray-900 rounded-l-lg hover:bg-gray-900 hover:text-white focus:z-10 focus:ring-2 focus:ring-gray-500 focus:bg-gray-900 focus:text-white dark:border-white dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:bg-gray-700 @if (! $acg)bg-gray-900 text-white @else bg-transparent @endif">
                Tout
                </a>
                <a href="{{ route('events.index', ['acg' => 'u14m']) }}" class="px-4 py-2 text-sm font-medium text-gray-900 border-t border-b border-gray-900 hover:bg-gray-900 hover:text-white focus:z-10 focus:ring-2 focus:ring-gray-500 focus:bg-gray-900 focus:text-white dark:border-white dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:bg-gray-700 @if ($acg == 'u14m')bg-gray-900 text-white  @else bg-transparent @endif">
                    U14 et plus jeunes
                </a>
                <a href="{{ route('events.index', ['acg' => 'u16p']) }}" class="px-4 py-2 text-sm font-medium text-gray-900 border-l border-t border-b border-gray-900 hover:bg-gray-900 hover:text-white focus:z-10 focus:ring-2 focus:ring-gray-500 focus:bg-gray-900 focus:text-white dark:border-white dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:bg-gray-700 @if ($acg == 'u16p')bg-gray-900 text-white  @else bg-transparent @endif">
                    U16 et plus âgés
                </a>
                <a href="{{ route('events.index', ['acg' => 'u18p_mid_dist']) }}" class="px-4 py-2 text-sm font-medium text-gray-900 border border-gray-900 rounded-r-md hover:bg-gray-900 hover:text-white focus:z-10 focus:ring-2 focus:ring-gray-500 focus:bg-gray-900 focus:text-white dark:border-white dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:bg-gray-700 @if ($acg == 'u18p_mid_dist')bg-gray-900 text-white @else bg-transparent @endif">
                    Demi-fond U18+
                </a>
            </div>
        </section>
        <section class="flex justify-between px-4 mx-auto max-w-screen-xl">
            <div class="relative overflow-x-auto mx-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-2 py-2 md:px-6 md:py-4">
                                Nom
                            </th>
                            <th scope="col" class="px-2 py-2 md:px-6 md:py-4">
                                Date
                            </th>
                            <th scope="col" class="px-2 py-2 md:px-6 md:py-4">
                                Lieu
                            </th>
                            <th scope="col" class="px-2 py-2 md:px-6 md:py-4">
                                 
                            </th>
                            <th scope="col" class="px-2 py-2 md:px-6 md:py-4">
                                 
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($events as $event)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-2 py-2 md:px-6 md:py-4 font-medium text-gray-900 dark:text-white">
                                <a href="{{ $event->url }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline block max-w-[200px] truncate md:inline md:mx-w-none md:overflow-visible">{{ $event->name }}</a>
                                @if (data_get($event, 'status.value') != 'planned')
                                <span class="bg-gray-100 text-gray-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-400 border border-gray-500">{{ $event->status->getLabel() }}</span>
                                @endif
                            </th>
                            <td class="px-2 py-2 md:px-6 md:py-4">
                                {{ $event->starts_at->isoFormat('DD.MM.YYYY') }}
                            </td>
                            <td class="px-2 py-2 md:px-6 md:py-4">
                                {{ $event->location }}
                            </td>
                            <td class="px-2 py-2 md:px-6 md:py-4">
                                <span class="whitespace-nowrap">
                                    {{ $event->codes }}
                                    @if ($event->has_publication)
                                    <a href="{{ $event->publication_url }}" class="text-blue-700 border border-blue-700 hover:bg-blue-700 hover:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm text-center inline-flex items-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:focus:ring-blue-800 dark:hover:bg-blue-500 w-4 h-4"><i class="bi bi-info"></i></a>
                                    @endif
                                    @if ($event->has_provisional_timetable || $event->has_final_timetable)
                                    <a href="{{ $event->has_final_timetable ?? $event->has_provisional_timetable }}" class="text-blue-700 hover:bg-blue-700 hover:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm text-center inline-flex items-center dark:text-blue-500 dark:hover:text-white dark:focus:ring-blue-800 dark:hover:bg-blue-500 w-4 h-4"><i class="bi bi-clock"></i></a>
                                    @endif
                                </span>
                            </td>
                            <td class="px-2 py-2 md:px-6 md:py-4">
                                <span class="whitespace-nowrap">
                                    @if ($event->has_deadline)
                                    <span data-tooltip-target="tooltip-event-{{ $event->id }}">
                                        <i class="bi bi-alarm"></i> {{ $event->deadline_at->diffForHumans() }}
                                    </span>
                                    <div id="tooltip-event-{{ $event->id }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                        Délai d'inscription au {{ $event->deadline_at->isoFormat('DD.MM.YYYY') }}
                                        <div class="tooltip-arrow" data-popper-arrow></div>
                                    </div>
                                    @endif
                                    @if ($event->has_deadline && $event->description) · @endif
                                    @if ($event->description)
                                    {{ str($event->description)->limit('20') }}</span>
                                    @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</x-layouts.app>
