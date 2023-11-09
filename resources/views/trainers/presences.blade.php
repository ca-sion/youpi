<x-layouts.app>

    <main class="pt-8 pb-16 lg:pt-16 lg:pb-24 bg-white dark:bg-gray-900 antialiased">
        <section class="flex justify-between px-4 mx-auto max-w-screen-xl">
            <div class="relative overflow-x-auto w-full mx-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3"> </th>
                            @foreach ($events as $event)
                            <th scope="col" class="px-6 py-3 text-center">
                                {{ $event->name }}<br>
                                {{ $event->starts_at->isoFormat('L') }}<br>
                                <a href="{{ route('events.trainers.presences', ['event' => $event->id]) }}"><i class="bi bi-pencil"></i></a>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($trainers as $trainer)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-2 py-2 md:px-6 md:py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $trainer->name }}</th>
                            @foreach ($events as $event)
                            <td class="px-2 py-2 md:px-6 md:py-4 text-center">
                                @php
                                    $hasNote = data_get($event->trainersPresences->firstWhere('trainer_id', $trainer->id), 'note');
                                @endphp
                                <span @if ($hasNote) data-tooltip-target="tooltip-default" @endif class="relative">
                                @if (data_get($event->trainersPresences->firstWhere('trainer_id', $trainer->id), 'presence') === true)
                                    <i class="bi bi-check-circle text-green-600"></i>
                                @elseif (data_get($event->trainersPresences->firstWhere('trainer_id', $trainer->id), 'presence') === false)
                                <i class="bi bi-x-circle text-red-600"></i>
                                @else
                                    <i class="bi bi-dash text-gray-600"></i>
                                @endif

                                @if ($hasNote)
                                <span class="absolute -top-1 left-2 inline-block w-3 h-3 bg-yellow-500 border-2 border-white dark:border-gray-800 rounded-full"></span>
                                @endif

                                </span>

                                @if ($hasNote)
                                <div id="tooltip-default" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                    {{ data_get($event->trainersPresences->firstWhere('trainer_id', $trainer->id), 'note') }}
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                                @endif

                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</x-layouts.app>
