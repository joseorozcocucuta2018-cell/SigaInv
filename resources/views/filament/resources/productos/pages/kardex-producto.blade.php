<x-filament-panels::page>
    <div style="display:flex; flex-direction:column; gap:1rem;">

        {{-- Info del producto --}}
        <x-filament::section>
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
                <div>
                    <h3 class="text-lg font-bold text-gray-950 dark:text-white">
                        {{ $this->record->nombre }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Código: {{ $this->record->codigo ?? 'N/A' }} |
                        Categoría: {{ $this->record->categoria?->nombre ?? 'N/A' }} |
                        Costo promedio: ${{ number_format($this->record->costo_promedio ?? 0, 2, ',', '.') }}
                    </p>
                </div>
                <div style="display:flex; gap:0.5rem;">
                    <x-filament::button
                        href="{{ $this->getExcelUrl() }}"
                        tag="a"
                        color="gray"
                        icon="heroicon-m-table-cells">
                        Excel
                    </x-filament::button>
                    <x-filament::button
                        href="{{ $this->getExportUrl() }}"
                        tag="a"
                        target="_blank"
                        color="gray"
                        icon="heroicon-m-arrow-down-tray">
                        PDF
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        {{-- Filtros --}}
        <x-filament::section>
            {{ $this->filtersForm }}
        </x-filament::section>

        {{-- Tabla --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
