<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Responses\LoginResponse;
use App\Http\Responses\LogoutResponse;
use App\Models\Banco;
use App\Models\Caja;
use App\Models\Compra;
use App\Models\Cotizacion;
use App\Models\DetalleCompra;
use App\Models\DetalleRemision;
use App\Models\DetalleVenta;
use App\Models\MovimientoBanco;
use App\Models\MovimientoCaja;
use App\Models\PagoCliente;
use App\Models\PagoProveedor;
use App\Models\Producto;
use App\Models\Remision;
use App\Models\Transformacion;
use App\Models\Venta;
use App\Observers\CompraObserver;
use App\Observers\CotizacionObserver;
use App\Observers\DetalleCompraObserver;
use App\Observers\DetalleRemisionObserver;
use App\Observers\DetalleVentaObserver;
use App\Observers\MovimientoBancoObserver;
use App\Observers\MovimientoCajaObserver;
use App\Observers\PagoClienteObserver;
use App\Observers\PagoProveedorObserver;
use App\Observers\ProductoObserver;
use App\Observers\RemisionObserver;
use App\Observers\TransformacionObserver;
use App\Observers\VentaObserver;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            \Filament\Http\Responses\Auth\Contracts\LoginResponse::class,
            LoginResponse::class
        );
        $this->app->singleton(
            \Filament\Http\Responses\Auth\Contracts\LogoutResponse::class,
            LogoutResponse::class
        );
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
        \URL::forceScheme('https');
         }

        TextInput::configureUsing(function (TextInput $input): void {
            $input->trim();

            $name = strtolower($input->getName() ?? '');

            // Capitalizar nombre/razon_social al salir del campo
            if (str_contains($name, 'nombre') || str_contains($name, 'razon_social')) {
                $input->live(onBlur: true);
                $input->afterStateUpdated(fn (Set $set, ?string $state) => $set(
                    $input->getName(),
                    mb_convert_case(trim($state ?? ''), MB_CASE_TITLE, 'UTF-8'),
                ));
            }

            // Excluir explícitamente campos de porcentaje/tasa
            if (str_contains($name, 'porcentaje') || str_contains($name, 'tasa')) {
                return;
            }

            $moneyFields = [
                'precio', 'costo', 'monto', 'valor', 'total', 'subtotal',
                'impuesto', 'impuestos', 'descuento', 'saldo', 'pago',
                'precio_unitario', 'precio_venta', 'precio_compra',
                'costo_promedio', 'costo_unitario', 'descuento_unitario',
            ];
            foreach ($moneyFields as $field) {
                if ($name === $field || str_contains($name, $field)) {
                    $input->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2);
                    break;
                }
            }
        });

        // Disco 'directo': en producción apunta al disco cloud (R2)
        // que Laravel Cloud registra dinámicamente via LARAVEL_CLOUD_DISK_CONFIG
        if ($this->app->environment('production')) {
            $cloudDisk = config('filesystems.disks.sigaInv');
            if ($cloudDisk) {
                config(['filesystems.disks.directo' => $cloudDisk]);
            }
        }

        Relation::morphMap([
            'caja' => Caja::class,
            'banco' => Banco::class,
        ]);

        // Optimizaciones de Base de Datos (laravel-database-optimization skill)
        Model::preventLazyLoading(! $this->app->isProduction());
        if (method_exists(Model::class, 'automaticallyEagerLoadRelationships')) {
            Model::automaticallyEagerLoadRelationships();
        }

        // Macro `currency` para columnas de tablas e infolists — formato $5.000.000,00
        $this->app->booted(function () {
            $formatCurrency = function (string|\Closure|null $currency = null, ?bool $shouldConvert = null): static {
                /** @var TextColumn|TextEntry $this */
                $this->formatStateUsing(function ($state) {
                    if (blank($state)) {
                        return null;
                    }
                    $amount = is_string($state) ? (float) $state : $state;

                    return '$'.number_format($amount, 2, ',', '.');
                });

                return $this;
            };

            TextColumn::macro('currency', $formatCurrency);
            TextEntry::macro('currency', $formatCurrency);
        });

        // Observers para detalles — actualización de stock
        DetalleVenta::observe(DetalleVentaObserver::class);
        DetalleCompra::observe(DetalleCompraObserver::class);
        DetalleRemision::observe(DetalleRemisionObserver::class);

        // Observers para documentos — actualización de saldos y validaciones
        Venta::observe(VentaObserver::class);
        Remision::observe(RemisionObserver::class);
        Cotizacion::observe(CotizacionObserver::class);

        // Observers para pagos — actualización de saldos
        PagoCliente::observe(PagoClienteObserver::class);
        PagoProveedor::observe(PagoProveedorObserver::class);

        // Observer para transformaciones / producción
        Transformacion::observe(TransformacionObserver::class);

        // Observer para movimientos de caja
        MovimientoCaja::observe(MovimientoCajaObserver::class);

        // Observer para movimientos bancarios
        MovimientoBanco::observe(MovimientoBancoObserver::class);

        // Observer para productos - auditoría de cambios
        Producto::observe(ProductoObserver::class);

        // Observer para compras - auditoría de cambios de campos
        Compra::observe(CompraObserver::class);
    }
}
