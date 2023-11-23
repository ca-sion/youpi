<x-layouts.app>

    <style>
        .ca-status {
            padding: 2px 8px;
            background-color: #222222;
            color: #ffffff;
            border-radius: 5%;
            font-size: x-small;
            color: #ffffff;
        }
    </style>

<main class="pt-8 pb-16 lg:pt-16 lg:pb-24 bg-white dark:bg-gray-900 antialiased">
    <section class="flex justify-between px-4 mx-auto max-w-screen-xl">
        <div class="relative overflow-x-auto mx-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">

                <thead>
                    <th> </th>
                    <th> </th>
                    <th> </th>
                    <th> </th>
                </thead>
                <tbody>
                    @foreach ($documents as $document)
                    <tr>
                        <td class="px-2 py-2">
                            <span class="ca-status" style="background-color: {{ $document->type->getColor() }};">
                                {{ $document->type->getLabel() }}
                            </span>
                        </td>
                        <td class="px-2 py-2">{{ $document->number }}</td>
                        <td class="px-2 py-2">
                            <a href="{{ route('documents.show', ['document' => $document]) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                {{ $document->name }}
                            </a>
                        </td>
                        <td class="px-2 py-2">
                            @if ($document->published_on > now()->subDays('60'))
                                <span class="ca-status" style="background-color: silver;">Nouveau</span>
                            @endif
                            @if ($document->status->value != 'validated')
                                <span class="ca-status me-1" style="background-color: {{ $document->status->getBackgroundColor() }};">
                                    {{ $document->status->getLabel() }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
  </main>
</x-layouts.app>
