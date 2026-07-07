<x-filament-panels::page>
    <div style="display:flex; flex-direction:column; gap:1rem;">

        {{-- Filtros --}}
        <x-filament::section>
            {{ $this->filtersForm }}
        </x-filament::section>

        {{-- Resumen --}}
        @php $totales = $this->getTotales(); @endphp
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem;">
            <x-filament::section>
                <div style="text-align:center;">
                    <p class="text-sm text-gray-500 dark:text-gray-400"># Productos</p>
                    <p class="text-2xl font-bold text-gray-950 dark:text-white">{{ $totales['total_productos'] }}</p>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div style="text-align:center;">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Unidades Inmovilizadas</p>
                    <p class="text-2xl font-bold text-gray-950 dark:text-white">{{ number_format($totales['total_unidades_inmovilizadas'], 0, ',', '.') }}</p>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div style="text-align:center;">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Valor Inmovilizado</p>
                    <p class="text-2xl font-bold text-danger-600 dark:text-danger-400">${{ number_format($totales['valor_total_inmovilizado'], 0, ',', '.') }}</p>
                </div>
            </x-filament::section>
        </div>

        {{-- Botones exportar --}}
        <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
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

        {{-- Tabla --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
