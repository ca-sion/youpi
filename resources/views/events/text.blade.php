<x-layouts.app>

<main class="pt-8 pb-16 lg:pt-16 lg:pb-24 bg-white dark:bg-gray-900 antialiased">
    <div class="flex justify-between px-4 mx-auto max-w-screen-xl">
        <article class="mx-auto w-full max-w-2xl format format-sm sm:format-base lg:format-lg format-blue dark:format-invert">
            <header class="mb-4 lg:mb-6 not-format">
                <div class="mb-4 lg:mb-6">
                    <h1 class="text-3xl font-extrabold leading-tight text-gray-900 lg:text-4xl dark:text-white">{{ $event->name }} {{ $event->codes }}</h1>
                    @if (data_get($event, 'status.value') != 'planned')
                    <div>{{ $event->status->getLabel() }}</div>
                    @endif
                </div>
            </header>
            <strong>Entraîneurs</strong>
            <div class="w-full p-4" style="border: solid 1px black;" id="copy_a">
                <x-event-text-trainers-message :event="$event" />
            </div>
            <strong>Athlètes/Parents</strong>
            <div class="w-full p-4" style="border: solid 1px black;">
                <x-event-text-athletes-message :event="$event" />
            </div>
            <strong>Résumé</strong>
            <div class="w-full p-4" style="border: solid 1px black;">
                <x-event-text-resume-message :event="$event" />
            </div>

        </article>
    </div>
  </main>
</x-layouts.app>
