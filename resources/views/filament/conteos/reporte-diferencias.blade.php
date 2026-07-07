<div class="space-y-4">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">Producto</th>
                <th scope="col" class="px-6 py-3">Stock Sistema</th>
                <th scope="col" class="px-6 py-3">Stock Contado</th>
                <th scope="col" class="px-6 py-3">Diferencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($diferencias as $detalle)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                        {{ $detalle->producto->nombre ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $detalle->stock_sistema }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $detalle->cantidad_contada }}
                    </td>
                    <td class="px-6 py-4 font-bold {{ ($detalle->cantidad_contada - $detalle->stock_sistema) > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $detalle->cantidad_contada - $detalle->stock_sistema }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
