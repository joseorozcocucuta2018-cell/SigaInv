<x-filament-panels::page>
    <div style="display:flex; flex-direction:column; gap:1rem;">

        {{-- Filtros --}}
        <x-filament::section>
            {{ $this->filtersForm }}
        </x-filament::section>

        {{-- Resumen --}}
        @php $totales = $this->getTotales(); @endphp
        <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:1rem;">
            <x-filament::section>
                <div style="text-align:center;">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ventas</p>
                    <p class="text-xl font-bold text-gray-950 dark:text-white">{{ $totales['cantidad'] }}</p>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div style="text-align:center;">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ingresos</p>
                    <p class="text-xl font-bold text-primary-600 dark:text-primary-400">${{ number_format($totales['total_ventas'], 0, ',', '.') }}</p>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div style="text-align:center;">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Costo</p>
                    <p class="text-xl font-bold text-gray-950 dark:text-white">${{ number_format($totales['total_costo'], 0, ',', '.') }}</p>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div style="text-align:center;">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Utilidad</p>
                    <p class="text-xl font-bold {{ $totales['utilidad'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                        ${{ number_format($totales['utilidad'], 0, ',', '.') }}
                    </p>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div style="text-align:center;">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Margen</p>
                    <p class="text-xl font-bold {{ $totales['margen'] >= 20 ? 'text-success-600 dark:text-success-400' : ($totales['margen'] >= 0 ? 'text-warning-600 dark:text-warning-400' : 'text-danger-600 dark:text-danger-400') }}">
                        {{ $totales['margen'] }}%
                    </p>
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
