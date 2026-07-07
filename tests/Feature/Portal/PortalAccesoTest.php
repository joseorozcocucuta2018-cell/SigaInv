<?php

/*
|--------------------------------------------------------------------------
| PortalAccesoTest.php — Tests de aislamiento del panel /clientes
| Tarea 8.14 — Reescritos para el nuevo modelo de auth donde el Cliente
| es Authenticatable directo (guard 'cliente') en vez de User con rol
| 'cliente'. Reemplaza completamente la versión anterior (Tarea 7.06).
|--------------------------------------------------------------------------
*/

use App\Enums\ClienteEstado;
use App\Enums\CotizacionEstado;
use App\Enums\PortalAccesoEnum;
use App\Enums\RemisionEstado;
use App\Enums\VentaEstado;
use App\Filament\Cliente\Pages\EditarPerfil;
use App\Filament\Resources\PortalClientes\CotizacionPortals\Pages\ListCotizacionPortals;
use App\Filament\Resources\PortalClientes\FacturaPortals\Pages\ListFacturaPortals;
use App\Filament\Resources\PortalClientes\RemisionPortals\Pages\ListRemisionPortals;
use App\Models\Bodega;
use App\Models\Ciudad;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Departamento;
use App\Models\Remision;
use App\Models\User;
use App\Models\Venta;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

use function Pest\Livewire\livewire;

/**
 * Helper: crea un cliente con password ya cambiado (puede loguearse y
 * entrar al dashboard). Por defecto, portal_acceso=activo.
 */
function crearClienteConAcceso(
    PortalAccesoEnum $portalEstado = PortalAccesoEnum::ACTIVO,
    ClienteEstado $estado = ClienteEstado::ACTIVO,
    ?string $password = 'password',
    bool $temporal = false,
): Cliente {
    if (Role::count() === 0) {
        (new RoleSeeder)->run();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    $attributes = [
        'portal_acceso' => $portalEstado,
        'estado' => $estado,
        'password' => $password,
    ];

    if (! $temporal) {
        $attributes['password_changed_at'] = now();
    }

    return Cliente::factory()->create($attributes);
}

function crearBodega(): Bodega
{
    return Bodega::factory()->create();
}

function crearVentaParaCliente(int $clienteId, VentaEstado $estado = VentaEstado::CONFIRMADA): Venta
{
    return Venta::factory()->create([
        'cliente_id' => $clienteId,
        'bodega_id' => crearBodega()->id,
        'estado' => $estado,
    ]);
}

function crearRemisionParaCliente(int $clienteId, RemisionEstado $estado = RemisionEstado::CONFIRMADA): Remision
{
    return Remision::create([
        'numero' => 'REM-'.uniqid(),
        'estado' => $estado,
        'cliente_id' => $clienteId,
        'bodega_id' => crearBodega()->id,
        'subtotal' => 0,
        'descuento' => 0,
        'impuestos' => 0,
        'total' => 0,
        'saldo_pendiente' => 0,
        'estado_pago' => 'pendiente',
    ]);
}

function crearCotizacionParaCliente(int $clienteId, CotizacionEstado $estado = CotizacionEstado::PENDIENTE): Cotizacion
{
    return Cotizacion::create([
        'numero' => 'COT-'.uniqid(),
        'cliente_id' => $clienteId,
        'bodega_id' => crearBodega()->id,
        'fecha' => now(),
        'subtotal' => 0,
        'descuento' => 0,
        'impuestos' => 0,
        'total' => 0,
        'estado' => $estado,
    ]);
}

// ═══════════════════════════════════════════════════════════════════════
// Panel access
// ═══════════════════════════════════════════════════════════════════════

describe('Portal — Acceso al panel /clientes', function () {

    it('cliente activo con portal_acceso=activo puede acceder al dashboard', function () {
        $cliente = crearClienteConAcceso();

        $this->actingAs($cliente, 'cliente')
            ->get('/clientes')
            ->assertSuccessful();
    });

    it('cliente con portal_acceso=sin_acceso es rechazado (403)', function () {
        $cliente = crearClienteConAcceso(PortalAccesoEnum::SIN_ACCESO);

        $this->actingAs($cliente, 'cliente')
            ->get('/clientes')
            ->assertForbidden();
    });

    it('cliente con portal_acceso=pendiente es rechazado (403)', function () {
        $cliente = crearClienteConAcceso(PortalAccesoEnum::PENDIENTE);

        $this->actingAs($cliente, 'cliente')
            ->get('/clientes')
            ->assertForbidden();
    });

    it('cliente inactivo es rechazado (403)', function () {
        $cliente = crearClienteConAcceso(PortalAccesoEnum::ACTIVO, ClienteEstado::INACTIVO);

        $this->actingAs($cliente, 'cliente')
            ->get('/clientes')
            ->assertForbidden();
    });

    it('usuario con rol vendedor NO puede acceder al panel clientes', function () {
        if (Role::count() === 0) {
            (new RoleSeeder)->run();
        }

        $user = User::factory()->create();
        $user->assignRole('vendedor');

        $this->actingAs($user)
            ->get('/clientes')
            ->assertRedirect('/clientes/login');
    });

    it('visitante anónimo es redirigido al login', function () {
        $this->get('/clientes')
            ->assertRedirect('/clientes/login');
    });
});

// ═══════════════════════════════════════════════════════════════════════
// Aislamiento de listados (getTableQuery)
// ═══════════════════════════════════════════════════════════════════════

describe('Portal — Aislamiento de listados', function () {

    it('el cliente solo ve SUS facturas en Mis Facturas', function () {
        $cliente = crearClienteConAcceso();
        $otro = Cliente::factory()->create();

        $mia = crearVentaParaCliente($cliente->id, VentaEstado::CONFIRMADA);
        $ajena = crearVentaParaCliente($otro->id, VentaEstado::CONFIRMADA);

        $this->actingAs($cliente, 'cliente');

        livewire(ListFacturaPortals::class)
            ->assertCanSeeTableRecords([$mia])
            ->assertCanNotSeeTableRecords([$ajena]);
    });

    it('el cliente solo ve SUS remisiones en Mis Remisiones', function () {
        $cliente = crearClienteConAcceso();
        $otro = Cliente::factory()->create();

        $mia = crearRemisionParaCliente($cliente->id, RemisionEstado::CONFIRMADA);
        $ajena = crearRemisionParaCliente($otro->id, RemisionEstado::CONFIRMADA);

        $this->actingAs($cliente, 'cliente');

        livewire(ListRemisionPortals::class)
            ->assertCanSeeTableRecords([$mia])
            ->assertCanNotSeeTableRecords([$ajena]);
    });

    it('el cliente solo ve SUS cotizaciones en Mis Cotizaciones', function () {
        $cliente = crearClienteConAcceso();
        $otro = Cliente::factory()->create();

        $mia = crearCotizacionParaCliente($cliente->id, CotizacionEstado::PENDIENTE);
        $ajena = crearCotizacionParaCliente($otro->id, CotizacionEstado::PENDIENTE);

        $this->actingAs($cliente, 'cliente');

        livewire(ListCotizacionPortals::class)
            ->assertCanSeeTableRecords([$mia])
            ->assertCanNotSeeTableRecords([$ajena]);
    });

    it('facturas en BORRADOR no son visibles en el portal', function () {
        $cliente = crearClienteConAcceso();

        $borrador = crearVentaParaCliente($cliente->id, VentaEstado::BORRADOR);
        $confirmada = crearVentaParaCliente($cliente->id, VentaEstado::CONFIRMADA);

        $this->actingAs($cliente, 'cliente');

        livewire(ListFacturaPortals::class)
            ->assertCanSeeTableRecords([$confirmada])
            ->assertCanNotSeeTableRecords([$borrador]);
    });
});

// ═══════════════════════════════════════════════════════════════════════
// PdfController — Ownership de documentos
// ═══════════════════════════════════════════════════════════════════════

describe('Portal — Seguridad de PDFs', function () {

    it('cliente puede descargar PDF de SU propia venta', function () {
        $cliente = crearClienteConAcceso();
        $venta = crearVentaParaCliente($cliente->id, VentaEstado::CONFIRMADA);

        $response = $this->actingAs($cliente, 'cliente')->get(route('pdf.venta', $venta));

        expect($response->status())->toBeIn([200, 302]);
    });

    it('cliente NO puede descargar PDF de una venta AJENA', function () {
        $cliente = crearClienteConAcceso();
        $otro = Cliente::factory()->create();
        $ventaAjena = crearVentaParaCliente($otro->id, VentaEstado::CONFIRMADA);

        $this->actingAs($cliente, 'cliente')
            ->get(route('pdf.venta', $ventaAjena))
            ->assertForbidden();
    });

    it('cliente puede descargar PDF de SU propia remisión', function () {
        $cliente = crearClienteConAcceso();
        $remision = crearRemisionParaCliente($cliente->id, RemisionEstado::CONFIRMADA);

        $response = $this->actingAs($cliente, 'cliente')->get(route('pdf.remision', $remision));

        expect($response->status())->toBeIn([200, 302]);
    });

    it('cliente NO puede descargar PDF de una remisión AJENA', function () {
        $cliente = crearClienteConAcceso();
        $otro = Cliente::factory()->create();
        $remisionAjena = crearRemisionParaCliente($otro->id, RemisionEstado::CONFIRMADA);

        $this->actingAs($cliente, 'cliente')
            ->get(route('pdf.remision', $remisionAjena))
            ->assertForbidden();
    });

    it('cliente puede descargar PDF de SU propia cotización', function () {
        $cliente = crearClienteConAcceso();
        $cotizacion = crearCotizacionParaCliente($cliente->id, CotizacionEstado::PENDIENTE);

        $response = $this->actingAs($cliente, 'cliente')->get(route('pdf.cotizacion', $cotizacion));

        expect($response->status())->toBeIn([200, 302]);
    });

    it('cliente NO puede descargar PDF de una cotización AJENA', function () {
        $cliente = crearClienteConAcceso();
        $otro = Cliente::factory()->create();
        $cotizacionAjena = crearCotizacionParaCliente($otro->id, CotizacionEstado::PENDIENTE);

        $this->actingAs($cliente, 'cliente')
            ->get(route('pdf.cotizacion', $cotizacionAjena))
            ->assertForbidden();
    });

    it('visitante anónimo NO puede acceder a PDFs', function () {
        $cliente = Cliente::factory()->create();
        $venta = crearVentaParaCliente($cliente->id, VentaEstado::CONFIRMADA);

        $this->get(route('pdf.venta', $venta))
            ->assertRedirect();
    });
});

// ═══════════════════════════════════════════════════════════════════════
// Flujo de password temporal
// ═══════════════════════════════════════════════════════════════════════

describe('Portal — Flujo de cambio de contraseña', function () {

    it('cliente con password temporal (password_changed_at=null) es redirigido a cambiar-password', function () {
        $cliente = crearClienteConAcceso(
            PortalAccesoEnum::ACTIVO,
            ClienteEstado::ACTIVO,
            'password',
            true,
        );

        $response = $this->actingAs($cliente, 'cliente')->get(route('filament.clientes.pages.cliente-dashboard'));

        $response->assertRedirect(route('filament.clientes.pages.cambiar-password'));
    });

    it('cliente con password definitivo accede al dashboard normalmente', function () {
        $cliente = crearClienteConAcceso();

        $this->actingAs($cliente, 'cliente')
            ->get(route('filament.clientes.pages.cliente-dashboard'))
            ->assertSuccessful();
    });

    it('Auth::guard(cliente)->check() funciona con el nuevo guard', function () {
        $cliente = crearClienteConAcceso();

        $this->actingAs($cliente, 'cliente');

        expect(Auth::guard('cliente')->check())->toBeTrue();
        expect(Auth::guard('cliente')->id())->toBe($cliente->id);
    });

    it('la página CambiarPassword puede ser renderizada por un cliente autenticado', function () {
        $cliente = crearClienteConAcceso(
            PortalAccesoEnum::ACTIVO,
            ClienteEstado::ACTIVO,
            'password',
            true,
        );

        $this->actingAs($cliente, 'cliente')
            ->get(route('filament.clientes.pages.cambiar-password'))
            ->assertSuccessful();
    });

    it('cliente con password temporal ve el formulario SIN campo de contraseña actual', function () {
        $cliente = crearClienteConAcceso(
            PortalAccesoEnum::ACTIVO,
            ClienteEstado::ACTIVO,
            'temporalPass1',
            true,
        );

        $this->actingAs($cliente, 'cliente')
            ->get(route('filament.clientes.pages.cambiar-password'))
            ->assertSuccessful()
            ->assertDontSee('Contraseña actual');
    });
});

// ═══════════════════════════════════════════════════════════════════════
// Mi Perfil — EditarPerfil page
// ═══════════════════════════════════════════════════════════════════════

describe('Portal — Mi Perfil (EditarPerfil)', function () {

    it('página Mi Perfil carga para cliente autenticado', function () {
        $cliente = crearClienteConAcceso();

        $this->actingAs($cliente, 'cliente')
            ->get(route('filament.clientes.pages.editar-perfil'))
            ->assertSuccessful();
    });

    it('cliente puede actualizar sus datos editables (nombre, telefono, direccion)', function () {
        $departamento = Departamento::factory()->create(['nombre' => 'Norte de Santander']);
        $ciudad = Ciudad::factory()->create([
            'nombre' => 'Cúcuta',
            'departamento_id' => $departamento->id,
        ]);
        $cliente = crearClienteConAcceso();
        $cliente->update([
            'departamento_id' => $departamento->id,
            'ciudad_id' => $ciudad->id,
        ]);

        $this->actingAs($cliente, 'cliente');

        livewire(EditarPerfil::class)
            ->fillForm([
                'nombre' => 'NUEVO NOMBRE SA',
                'contacto_principal' => 'Juan Pérez',
                'telefono' => '+57 311 5551234',
                'sitio_web' => 'https://nuevo-ejemplo.com',
                'direccion1' => 'Calle 10 # 5-22',
                'direccion2' => 'Oficina 301',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $cliente->refresh();
        expect($cliente->nombre)->toBe('NUEVO NOMBRE SA');
        expect($cliente->contacto_principal)->toBe('Juan Pérez');
        expect($cliente->telefono)->toBe('+57 311 5551234');
        expect($cliente->sitio_web)->toBe('https://nuevo-ejemplo.com');
        expect($cliente->direccion1)->toBe('Calle 10 # 5-22');
        expect($cliente->direccion2)->toBe('Oficina 301');
    });

    it('cliente NO puede inyectar campos admin-only (email, estado, limite_credito)', function () {
        $cliente = crearClienteConAcceso();
        $emailOriginal = $cliente->email;
        $estadoOriginal = $cliente->estado;
        $limiteOriginal = (string) $cliente->limite_credito;

        $this->actingAs($cliente, 'cliente');

        livewire(EditarPerfil::class)
            ->fillForm([
                'nombre' => 'INYECCION',
                'email' => 'hacker@evil.com',
                'estado' => 'inactivo',
                'limite_credito' => 9999999,
                'portal_acceso' => 'sin_acceso',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $cliente->refresh();
        expect($cliente->nombre)->toBe('INYECCION');
        expect($cliente->email)->toBe($emailOriginal);
        expect($cliente->estado)->toBe($estadoOriginal);
        expect((string) $cliente->limite_credito)->toBe($limiteOriginal);
    });
});

// ═══════════════════════════════════════════════════════════════════════
// CreateCliente — auto-generación de acceso al portal
// ═══════════════════════════════════════════════════════════════════════

use App\Filament\Resources\ClienteResource\Pages\CreateCliente;
use App\Mail\PortalCredencialesMail;
use Illuminate\Support\Facades\Mail;

describe('Admin — Crear cliente con acceso al portal automático', function () {

    it('crear cliente genera password temporal y activa portal por defecto', function () {
        loginComoAdmin();
        [$depto, $ciudad] = crearUbicacion();
        Mail::fake();

        livewire(CreateCliente::class)
            ->fillForm([
                'nombre' => 'Cliente Nuevo SAS',
                'documento' => '900777888',
                'tipo_documento' => 'NIT',
                'telefono' => '3001112222',
                'email' => 'nuevo@cliente.com',
                'departamento_id' => $depto->id,
                'ciudad_id' => $ciudad->id,
                'direccion1' => 'Calle 1 # 1-1',
                'estado' => ClienteEstado::ACTIVO,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $cliente = Cliente::where('documento', '900777888')->first();
        expect($cliente)->not->toBeNull();
        expect($cliente->portal_acceso?->value)->toBe(PortalAccesoEnum::ACTIVO->value);
        expect($cliente->password)->not->toBeNull();
        expect($cliente->password_changed_at)->toBeNull();
    });

    it('crear cliente con email válido encola email con credenciales', function () {
        loginComoAdmin();
        [$depto, $ciudad] = crearUbicacion();
        Mail::fake();

        livewire(CreateCliente::class)
            ->fillForm([
                'nombre' => 'Cliente Con Email SAS',
                'documento' => '900888999',
                'tipo_documento' => 'NIT',
                'telefono' => '3003334444',
                'email' => 'conemail@cliente.com',
                'departamento_id' => $depto->id,
                'ciudad_id' => $ciudad->id,
                'direccion1' => 'Calle 2 # 2-2',
                'estado' => ClienteEstado::ACTIVO,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Mail::assertQueued(PortalCredencialesMail::class);
    });

    it('crear cliente con email placeholder NO encola email pero sí activa portal', function () {
        loginComoAdmin();
        [$depto, $ciudad] = crearUbicacion();
        Mail::fake();

        livewire(CreateCliente::class)
            ->fillForm([
                'nombre' => 'Cliente Sin Email Real',
                'documento' => '900999000',
                'tipo_documento' => 'NIT',
                'telefono' => '3005556666',
                'email' => 'no_tiene_correo@correo.com',
                'departamento_id' => $depto->id,
                'ciudad_id' => $ciudad->id,
                'direccion1' => 'Calle 3 # 3-3',
                'estado' => ClienteEstado::ACTIVO,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $cliente = Cliente::where('documento', '900999000')->first();
        expect($cliente->portal_acceso?->value)->toBe(PortalAccesoEnum::ACTIVO->value);
        expect($cliente->password)->toBeNull();
        Mail::assertNotQueued(PortalCredencialesMail::class);
    });
});
