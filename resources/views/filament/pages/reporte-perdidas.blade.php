<x-filament-panels::page>
    <div style="display:flex; flex-direction:column; gap:1rem;">

        {{-- Filtros --}}
        <x-filament::section>
            {{ $this->filtersForm }}
        </x-filament::section>

        {{-- Resumen --}}
        @php $totales = $this->getTotales(); @endphp
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem;">
            <x-filament::section>
                <div style="text-align:center;">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Pérdida</p>
                    <p class="text-xl font-bold text-danger-600 dark:text-danger-400">${{ number_format($totales['total_perdida'], 0, ',', '.') }}</p>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div style="text-align:center;">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Merma</p>
                    <p class="text-xl font-bold text-warning-600 dark:text-warning-400">${{ number_format($totales['total_merma'], 0, ',', '.') }}</p>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div style="text-align:center;">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Daño</p>
                    <p class="text-xl font-bold text-danger-600 dark:text-danger-400">${{ number_format($totales['total_daño'], 0, ',', '.') }}</p>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div style="text-align:center;">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Robo</p>
                    <p class="text-xl font-bold text-danger-600 dark:text-danger-400">${{ number_format($totales['total_robo'], 0, ',', '.') }}</p>
                </div>
            </x-filament::section>
        </div>

        {{-- Detalle --}}
        <x-filament::section>
            {{ $this->table }}
        </x-filament::section>

    </div>
</x-filament-panels::page>
