<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages;

use App\Filament\Resources\ClienteResource\Schemas\ClienteForm;
use App\Models\Cliente;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

/**
 * Página de "Mi Perfil" del portal /clientes.
 *
 * Permite al cliente autenticado actualizar sus datos personales:
 *  - nombre, contacto_principal, telefono, sitio_web, pais
 *  - departamento, ciudad, direccion1, direccion2
 *
 * NO permite editar (admin-only):
 *  - documento, email  (el email se usa para login; cambiarlo
 *    manualmente en BD podría romper la sesión)
 *  - estado, portal_acceso, limite_credito, dias_credito,
 *    dias_pago, porcentaje_descuento, saldo
 *
 * Reutiliza el schema del recurso admin (DRY) — ver
 * App\Filament\Resources\ClienteResource\Schemas\ClienteForm.
 */
class EditarPerfil extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static string|\UnitEnum|null $navigationGroup = 'Cuenta';

    protected static ?string $navigationLabel = 'Mi Perfil';

    protected static ?string $title = 'Mi Perfil';

    protected static ?string $modelLabel = 'Mi Perfil';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.cliente.pages.editar-perfil';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $cliente = $this->cliente();

        if ($cliente instanceof Cliente) {
            $this->form->fill($cliente->only(ClienteForm::camposEditables(ClienteForm::SCOPE_CLIENTE)));
        }
    }

    public function form(Schema $schema): Schema
    {
        return ClienteForm::configure($schema, ClienteForm::SCOPE_CLIENTE)
            ->statePath('data');
    }

    public function save(): void
    {
        $cliente = $this->cliente();

        if (! $cliente instanceof Cliente) {
            Notification::make()
                ->title('Sesión inválida')
                ->body('No se pudo identificar al cliente autenticado. Vuelve a iniciar sesión.')
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();
        $permitidos = ClienteForm::camposEditables(ClienteForm::SCOPE_CLIENTE);
        $dataFiltrada = array_intersect_key($data, array_flip($permitidos));

        $cliente->fill($dataFiltrada)->save();

        Notification::make()
            ->title('Perfil actualizado')
            ->body('Tus datos de contacto se han guardado exitosamente.')
            ->success()
            ->send();
    }

    protected function cliente(): ?Cliente
    {
        $user = Auth::guard('cliente')->user();

        return $user instanceof Cliente ? $user : null;
    }
}
