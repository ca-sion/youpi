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
                                @if (data_get($event->trainersPresences->firstWhere('trainer_id', $trainer->id), 'presence') == true)
                                    <i class="bi bi-check-circle text-green-600"></i>
                                @elseif (data_get($event->trainersPresences->where('trainer_id', $trainer->id), 'presence') === 0)
                                <i class="bi bi-x-cross text-red-600"></i>
                                @else
                                    <i class="bi bi-dash text-gray-600"></i>
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
