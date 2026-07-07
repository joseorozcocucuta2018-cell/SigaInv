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
                    <p class="text-sm text-gray-500 dark:text-gray-400"># Vendedores</p>
                    <p class="text-2xl font-bold text-gray-950 dark:text-white">{{ $totales['total_vendedores'] }}</p>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div style="text-align:center;">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Facturas</p>
                    <p class="text-2xl font-bold text-gray-950 dark:text-white">{{ $totales['total_facturas'] }}</p>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div style="text-align:center;">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Ventas</p>
                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">${{ number_format($totales['total_valor'], 0, ',', '.') }}</p>
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
