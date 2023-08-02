<div class="mx-auto max-w-4xl">
    <form wire:submit="create">
        {{ $this->form }}

        <button type="submit" class="mt-4 text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">
            Ajouter
        </button>
    </form>

    <x-filament-actions::modals />
</div>
