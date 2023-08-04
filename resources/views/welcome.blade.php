<x-layouts.app>
    <div class="mx-auto max-w-4xl">
        <section class="bg-white dark:bg-gray-900">
            <div class="py-8 px-4 mx-auto max-w-3xl lg:py-10">
                <h2 class="mb-2 text-2xl font-semibold leading-none text-gray-900 md:text-3xl dark:text-white">Cette semaine</h2>

                @php
                    $sectionWeek = [
                        ['label' => 'Séances du jour', 'resources' => $today_resources],
                        ['label' => 'Plans hebdomadaires', 'resources' => $week_resources],
                        ['label' => 'Séances de la semaine', 'resources' => $all_week_resources],
                    ];
                @endphp

                @foreach ($sectionWeek as $section)
                <h3 class="mb-2 mt-10 text-lg font-semibold leading-none text-gray-900 md:text-xl dark:text-white">{{ $section['label'] }}</h3>
                <div class="mb-4">
                    @if (count($section['resources']) > 0)
                    <div class="relative overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <tbody>
                            @foreach ($section['resources'] as $resource)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row" class="pe-4 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white w-[150px] max-w-[250px]">
                                    {{ $resource->name }}
                                </th>
                                <td class="px-4 py-2 w-[100px]">
                                    {{ data_get($resource, 'athleteGroup.name') }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ data_get(config('youpi.resource_types'), $resource->type) }}
                                </td>
                                <td class="px-4 py-2 text-end">
                                    @if ($resource->attachment)<a href="{{ $resource->attachment }}" class="inline-flex items-center font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                        Afficher
                                        <i class="bi bi-arrow-right ml-2"></i>
                                    </a>@endif
                                    @if ($resource->text)<button data-modal-target="modal-{{ $resource->id }}" data-modal-toggle="modal-{{ $resource->id }}" type="button" class="inline-flex items-center font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                        Afficher
                                        <i class="bi bi-box-arrow-in-up ml-2"></i>
                                    </button>@endif
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    @push('modal')
                    @if ($resource->text)
                    <div id="modal-{{ $resource->id }}" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                        <div class="relative w-full max-w-2xl max-h-full">
                            <!-- Modal content -->
                            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                                <!-- Modal header -->
                                <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                        {{ $resource->name }}
                                    </h3>
                                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="modal-{{ $resource->id }}">
                                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                        </svg>
                                        <span class="sr-only">Fermer</span>
                                    </button>
                                </div>
                                <!-- Modal body -->
                                <div class="p-6 space-y-6 format dark:format-invert">
                                    {{ new \Illuminate\Support\HtmlString($resource->text) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endpush

                    @else
                        <p>Aucun·e</p>
                    @endif
                </div>
                @endforeach

            </div>
      </section>

      <section class="bg-white dark:bg-gray-900 mt-10">
        <div class="py-8 px-4 mx-auto max-w-3xl lg:py-10">

            @php
                    $sectionWeek = [
                        ['label' => 'Programmes', 'resources' => $sessions_exercises],
                        ['label' => 'Planifications', 'resources' => $year_plans],
                    ];
                @endphp

                @foreach ($sectionWeek as $section)
                <h3 class="mb-2 mt-10 text-lg font-semibold leading-none text-gray-900 md:text-xl dark:text-white">{{ $section['label'] }}</h3>
                <div class="mb-4">
                    @if (count($section['resources']) > 0)
                    <div class="relative overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <tbody>
                            @foreach ($section['resources'] as $resource)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row" class="pe-4 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white w-[150px] max-w-[250px]">
                                    {{ $resource->name }}
                                </th>
                                <td class="px-4 py-2 w-[100px]">
                                    {{ data_get($resource, 'athleteGroup.name') }}
                                </td>
                                <td class="px-4 py-2 text-end">
                                    @if ($resource->attachment)<a href="{{ $resource->attachment }}" class="inline-flex items-center font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                        Afficher
                                        <i class="bi bi-arrow-right ml-2"></i>
                                    </a>@endif
                                    @if ($resource->text)<button data-modal-target="modal-{{ $resource->id }}" data-modal-toggle="modal-{{ $resource->id }}" type="button" class="inline-flex items-center font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                        Afficher
                                        <i class="bi bi-box-arrow-in-up ml-2"></i>
                                    </button>@endif
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    @push('modal')
                    @if ($resource->text)
                    <div id="modal-{{ $resource->id }}" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                        <div class="relative w-full max-w-3xl max-h-full">
                            <!-- Modal content -->
                            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                                <!-- Modal header -->
                                <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                        {{ $resource->name }}
                                    </h3>
                                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="modal-{{ $resource->id }}">
                                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                        </svg>
                                        <span class="sr-only">Fermer</span>
                                    </button>
                                </div>
                                <!-- Modal body -->
                                <div class="p-6 space-y-6 format dark:format-invert">
                                    {{ new \Illuminate\Support\HtmlString($resource->text) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endpush

                    @else
                        <p>Aucun·e</p>
                    @endif
                </div>
                @endforeach
        </div>
      </section>

    </div>
</x-layouts.app>
