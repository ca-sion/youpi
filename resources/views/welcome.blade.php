<x-layouts.app>
    <div class="mx-auto max-w-4xl">
        <section class="bg-white dark:bg-gray-900">
            <div class="py-8 px-4 mx-auto max-w-2xl lg:py-16">
                <h2 class="mb-2 text-xl font-semibold leading-none text-gray-900 md:text-2xl dark:text-white">Cette semaine au programme</h2>

                @if ($today_resources)
                <h3 class="mb-2 mt-10 text-lg font-semibold leading-none text-gray-900 md:text-xl dark:text-white">Séances du jour</h3>
                <div class="mb-4">
                    <div class="relative overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <tbody>
                            @foreach ($today_resources as $resource)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row" class="pe-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $resource->name }}
                                </th>
                                <td class="px-6 py-4">
                                    {{ data_get($resource, 'athleteGroup.name') }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ data_get(config('youpi.resource_types'), $resource->type) }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($resource->attachment)<a href="{{ $resource->attachment }}" class="inline-flex items-center font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                        Afficher
                                        <svg class="w-4 h-4 ml-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                                        </svg>
                                    </a>@endif
                                    @if ($resource->text)<button data-modal-target="modal-{{ $resource->id }}" data-modal-toggle="modal-{{ $resource->id }}" type="button" class="inline-flex items-center font-medium text-blue-600 dark:text-blue-500 hover:underline">Afficher</button>@endif
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                @if ($week_resources)
                <h3 class="mb-2 mt-10 text-lg font-semibold leading-none text-gray-900 md:text-xl dark:text-white">Plans hebdomadaires</h3>
                <div class="mb-4">
                    <div class="relative overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <tbody>
                            @foreach ($week_resources as $resource)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row" class="pe-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $resource->name }}
                                </th>
                                <td class="px-6 py-4">
                                    {{ data_get($resource, 'athleteGroup.name') }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ data_get(config('youpi.resource_types'), $resource->type) }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($resource->attachment)<a href="{{ $resource->attachment }}" class="inline-flex items-center font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                        Afficher
                                        <svg class="w-4 h-4 ml-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                                        </svg>
                                    </a>@endif
                                    @if ($resource->text)<button data-modal-target="modal-{{ $resource->id }}" data-modal-toggle="modal-{{ $resource->id }}" type="button" class="inline-flex items-center font-medium text-blue-600 dark:text-blue-500 hover:underline">Afficher</button>@endif
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                @if ($all_week_resources)
                <h3 class="mb-2 mt-10 text-lg font-semibold leading-none text-gray-900 md:text-xl dark:text-white">Séances de la semaine</h3>
                <div class="mb-4">
                    <div class="relative overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <tbody>
                            @foreach ($all_week_resources as $resource)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row" class="pe-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $resource->name }}
                                </th>
                                <td class="px-6 py-4">
                                    {{ data_get($resource, 'athleteGroup.name') }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ data_get(config('youpi.resource_types'), $resource->type) }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($resource->attachment)<a href="{{ $resource->attachment }}" class="inline-flex items-center font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                        Afficher
                                        <svg class="w-4 h-4 ml-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                                        </svg>
                                    </a>@endif
                                    @if ($resource->text)<button data-modal-target="modal-{{ $resource->id }}" data-modal-toggle="modal-{{ $resource->id }}" type="button" class="inline-flex items-center font-medium text-blue-600 dark:text-blue-500 hover:underline">Afficher</button>@endif
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

            </div>
      </section>

      @push('modal')
      <div>
        @foreach ($week_resources as $resource)
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
                    <div class="p-6 space-y-6">
                        {{ new \Illuminate\Support\HtmlString($resource->text) }}
                    </div>
                </div>
            </div>
        </div>
        @endforeach
      </div>
      @endpush

    </div>
</x-layouts.app>
