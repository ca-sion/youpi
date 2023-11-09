<div>
    <div class="flex flex-col md:flex-row">
        <div class="flex items-center pl-4 border border-gray-200 rounded dark:border-gray-700 w-full md:me-2" wire:click="updatePresence(true)">
            <input id="presence-{{ $trainer->id }}-present" type="radio" name="presence-{{ $trainer->id }}" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 text-green-600 focus:ring-green-500 dark:focus:ring-green-600" @checked($trainerPresenceValue === true)>
            <label for="presence-{{ $trainer->id }}-present" class="w-full py-1 md:py-4 mx-2 text-sm font-medium text-gray-900 dark:text-gray-300">Présent</label>
        </div>
        <div class="flex items-center pl-4 border border-gray-200 rounded dark:border-gray-700 w-full mt-2 md:mt-0" wire:click="updatePresence(false)">
            <input id="presence-{{ $trainer->id }}-absent" type="radio" name="presence-{{ $trainer->id }}" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 text-red-600 focus:ring-red-500 dark:focus:ring-red-600" @checked($trainerPresenceValue === false)>
            <label for="presence-{{ $trainer->id }}-absent" class="w-full py-1 md:py-4 mx-2 text-sm font-medium text-gray-900 dark:text-gray-300">Absent</label>
        </div>
    </div>
</div>
