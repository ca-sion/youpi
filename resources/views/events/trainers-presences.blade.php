<x-layouts.app>

<main class="pt-8 pb-16 lg:pt-16 lg:pb-24 bg-white dark:bg-gray-900 antialiased">
    <div class="flex justify-between px-4 mx-auto max-w-screen-xl">
        <article class="mx-auto w-full max-w-2xl format format-sm sm:format-base lg:format-lg format-blue dark:format-invert">
            <header class="mb-4 lg:mb-6 not-format">
                <div class="mb-4 lg:mb-6">
                    <h1 class="text-3xl font-extrabold leading-tight text-gray-900 lg:text-4xl dark:text-white">{{ $event->name }} {{ $event->codes }}</h1>
                    @if (data_get($event, 'status.value') != 'planned')
                    <span class="bg-gray-100 text-gray-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-400 border border-gray-500">{{ $event->status->getLabel() }}</span>
                    @endif
                </div>

                <div class="text-gray-900">
                    <div class="flex">
                        <div class="font-bold min-w-[100px]">Quand</div>
                        <div>{{ $event->starts_at->isoFormat('dddd') }} {{ $event->starts_at->isoFormat('DD.MM.YYYY') }}</div>
                    </div>
                    @if ($event->location)
                    <div class="flex">
                        <div class="font-bold min-w-[100px]">Où</div>
                        <div>{{ $event->location }}</div>
                    </div>
                    @endif
                    <div class="flex">
                        <div class="font-bold min-w-[100px]">Concerne</div>
                        <div>{{ $event->getAthleteCategories }}</div>
                    </div>
                </div>
            </header>
        </article>
    </div>
</main>

<main class="py-8 bg-white dark:bg-gray-900 antialiased">
    <div class="flex justify-between px-4 mx-auto">
        <article class="mx-auto w-full">
            <strong class="block mb-4">Tableau des présences</strong>
            <div class="w-full">

                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    Nom
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Présence
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Note
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($trainers as $trainer)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row" class="px-2 py-2 md:px-6 md:py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    <div class="block md:inline">{{ $trainer->name }}</div>
                                    @foreach ($trainer->athleteGroups->pluck('name') as $ag)
                                    <span class="bg-gray-100 text-gray-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">{{ $ag }}</span>
                                    @endforeach
                                </th>
                                <td class="px-2 py-2 md:px-6 md:py-4">
                                    <livewire:trainers-presences-presence :trainer="$trainer->id" :event="$event->id" />
                                </td>
                                <td class="px-2 py-2 md:px-6 md:py-4">
                                    <livewire:trainers-presences-note :trainer="$trainer->id" :event="$event->id" />
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>

        </article>
    </div>
  </main>
</x-layouts.app>
