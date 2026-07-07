<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\VentaEstado;
use App\Models\Cliente;
use App\Models\Devolucion;
use App\Models\PagoCliente;
use App\Models\Venta;
use App\Notifications\ResumenMensualClienteNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class EnviarResumenMensual extends Command
{
    protected $signature = 'resumen:mensual';

    protected $description = 'Envía el estado de cuenta mensual a clientes con actividad en el mes anterior';

    public function handle(): int
    {
        $inicio = now()->subMonth()->startOfMonth();
        $fin = now()->subMonth()->endOfMonth();
        $mes = ucfirst($inicio->translatedFormat('F Y'));

        $clientes = Cliente::whereNotNull('email')
            ->where('id', '!=', 1) // excluir CLIENTES VARIOS
            ->get();

        $enviados = 0;

        foreach ($clientes as $cliente) {
            $ventas = Venta::with('cliente')
                ->where('cliente_id', $cliente->id)
                ->where('estado', VentaEstado::CONFIRMADA->value)
                ->whereBetween('fecha', [$inicio, $fin])
                ->get();

            $pagos = PagoCliente::with('detalles')
                ->where('cliente_id', $cliente->id)
                ->whereBetween('fecha', [$inicio, $fin])
                ->get();

            $devoluciones = Devolucion::where('cliente_id', $cliente->id)
                ->whereBetween('created_at', [$inicio, $fin])
                ->get();

            $saldoPendiente = (float) $cliente->saldo;

            // Criterio: >= 1 venta o saldo pendiente > 0
            $tieneActividad = $ventas->isNotEmpty() || $saldoPendiente > 0;

            if (! $tieneActividad) {
                continue;
            }

            $notif = new ResumenMensualClienteNotification(
                cliente: $cliente,
                mes: $mes,
                ventas: $ventas,
                pagos: $pagos,
                devoluciones: $devoluciones,
                saldoPendiente: $saldoPendiente,
            );

            Notification::route('mail', $cliente->email)->notify($notif);
            $enviados++;
        }

        $this->info("Resúmenes enviados: {$enviados} clientes.");

        return self::SUCCESS;
    }
}
