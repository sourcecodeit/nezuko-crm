<x-filament-panels::page>
    <div class="mb-6 flex justify-end">
        <select
            wire:model.live="selectedYear"
            class="w-32 rounded-lg border-gray-300 py-1 px-3 text-gray-900 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
        >
            @foreach ($years as $year)
                <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </select>
    </div>
    <x-filament-widgets::widgets :columns="1" :widgets="$this->getTopWidgets()" />
    <x-filament-widgets::widgets
        :columns="$this->getColumns()"
        :widgets="$this->getBodyWidgets()"
    />
</x-filament-panels::page>