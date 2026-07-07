<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\UserEstado;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Services\UserService;
use App\Traits\HasNavigationBadgeColor;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    use HasNavigationBadgeColor;

    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Administración';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    /**
     * Ocultar usuario protegido de todas las consultas
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        return $query->where('email', '!=', 'joseforozco@gmail.com');
    }

    public static function form(Schema $schema): Schema
    {
        $isView = request()->routeIs('*.view.*');

        return $schema->components([
            Section::make('Información principal')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(255)
                        ->readonly($isView),
                    TextInput::make('email')
                        ->label('Correo Electrónico')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->readonly($isView),
                    TextInput::make('password')
                        ->label('Contraseña')
                        ->password()
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $operation) => $operation === 'create')
                        ->visible(fn (string $operation) => $operation !== 'view'),
                    Select::make('roles')
                        ->label('Rol')
                        ->multiple()
                        ->relationship('roles', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('Seleccionar rol')
                        ->disabled($isView),
                ]),

            Section::make('Información adicional')
                ->columns(2)
                ->schema([
                    TextInput::make('celular')
                        ->label('Celular')
                        ->tel()
                        ->maxLength(20)
                        ->readonly($isView),
                    TextInput::make('cargo')
                        ->label('Cargo')
                        ->maxLength(100)
                        ->readonly($isView),
                    DatePicker::make('fecha_nacimiento')
                        ->label('Fecha de Nacimiento')
                        ->readonly($isView),
                    Select::make('estado')
                        ->label('Estado')
                        ->options(UserEstado::class)
                        ->default(UserEstado::ACTIVO)
                        ->disabled($isView),
                ]),

            Section::make('Avatar')
                ->schema([
                    FileUpload::make('avatar')
                        ->label('Avatar')
                        ->image()
                        ->disk('directo')
                        ->visibility('public')
                        ->directory('avatars')
                        ->maxSize(2048)
                        ->disabled($isView),
                ]),

            Section::make('Seguridad')
                ->schema([
                    DateTimePicker::make('password_changed_at')
                        ->label('Último cambio de contraseña')
                        ->disabled(),
                ]),

        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información principal')
                ->columns(2)
                ->schema([
                    TextEntry::make('name')
                        ->label('Nombre'),
                    TextEntry::make('email')
                        ->label('Correo Electrónico')
                        ->copyable(),
                    TextEntry::make('roles.name')
                        ->label('Rol')
                        ->badge()
                        ->color(fn ($state) => match ($state) {
                            'administrador' => 'danger',
                            'contador' => 'warning',
                            'auxiliar' => 'info',
                            'vendedor' => 'success',
                            default => 'gray',
                        }),
                    TextEntry::make('estado')
                        ->label('Estado')
                        ->badge()
                        ->formatStateUsing(fn (UserEstado $state): string => $state->label())
                        ->color(fn (UserEstado $state): string => $state->color()),
                ]),

            Section::make('Información adicional')
                ->columns(2)
                ->schema([
                    TextEntry::make('celular')
                        ->label('Celular')
                        ->placeholder('—'),
                    TextEntry::make('cargo')
                        ->label('Cargo')
                        ->placeholder('—'),
                    TextEntry::make('fecha_nacimiento')
                        ->label('Fecha de Nacimiento')
                        ->date('d/m/Y')
                        ->placeholder('—'),
                    TextEntry::make('password_changed_at')
                        ->label('Último cambio de contraseña')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder('—'),
                ]),

            Section::make('Avatar')
                ->visible(fn ($record) => ! is_null($record->avatar))
                ->schema([
                    ImageEntry::make('avatar')
                        ->label('')
                        ->circular()
                        ->disk('directo')
                        ->alignCenter(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->circular()
                    ->disk('directo')
                    ->defaultImageUrl(url('/images/avatar-default.svg')),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label('Rol')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'administrador' => 'danger',
                        'contador' => 'warning',
                        'auxiliar' => 'info',
                        'vendedor' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (UserEstado $state): string => $state->label())
                    ->color(fn (UserEstado $state): string => $state->color()),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('desactivar')
                    ->label('Desactivar')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('¿Desactivar usuario?')
                    ->modalDescription('El usuario perderá acceso al sistema. Esta acción se puede revertir editando el usuario.')
                    ->modalSubmitActionLabel('Sí, desactivar')
                    ->visible(fn (User $record) => Auth::user()?->hasRole('administrador') &&
                        $record->id !== 1 &&
                        $record->email !== 'joseforozco@gmail.com' &&
                        $record->estado?->value === UserEstado::ACTIVO->value
                    )
                    ->action(function (User $record) {
                        app(UserService::class)->desactivar($record);

                        Notification::make()
                            ->title('Usuario desactivado')
                            ->body('El usuario ya no tiene acceso al sistema.')
                            ->success()
                            ->send();
                    }),
                Action::make('aprobar')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->modalHeading('Aprobar solicitud de acceso')
                    ->modalDescription('Asigna un rol al usuario para activar su cuenta.')
                    ->modalSubmitActionLabel('Aprobar y activar')
                    ->form([
                        Select::make('rol')
                            ->label('Rol')
                            ->options([
                                'administrador' => 'Administrador',
                                'auxiliar' => 'Auxiliar',
                                'contador' => 'Contador',
                                'vendedor' => 'Vendedor',
                            ])
                            ->required()
                            ->placeholder('Seleccionar rol'),
                    ])
                    ->visible(fn (User $record) => Auth::user()?->hasRole('administrador') &&
                        $record->estado?->value === UserEstado::PENDIENTE->value
                    )
                    ->action(function (User $record, array $data) {
                        app(UserService::class)->aprobar($record, $data['rol']);

                        Notification::make()
                            ->title("Acceso aprobado: {$record->name}")
                            ->body("El usuario ha sido activado con el rol '{$data['rol']}'.")
                            ->success()
                            ->send();
                    }),
                Action::make('rechazar')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('¿Rechazar solicitud de acceso?')
                    ->modalDescription(fn (User $record) => "Se eliminará la cuenta de {$record->name} ({$record->email}). Esta acción no se puede deshacer.")
                    ->modalSubmitActionLabel('Sí, rechazar y eliminar')
                    ->visible(fn (User $record) => Auth::user()?->hasRole('administrador') &&
                        $record->estado?->value === UserEstado::PENDIENTE->value
                    )
                    ->action(function (User $record) {
                        $nombre = $record->name;
                        app(UserService::class)->rechazar($record);

                        Notification::make()
                            ->title("Solicitud rechazada: {$nombre}")
                            ->body('La cuenta ha sido eliminada del sistema.')
                            ->warning()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction eliminado para proteger usuarios
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('usuarios.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('usuarios.crear') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('usuarios.editar') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        if (! Auth::user()?->can('usuarios.eliminar')) {
            return false;
        }

        return $record instanceof User && $record->canBeDeleted();
    }

    public static function isProtected(User $user): bool
    {
        return $user->email === 'joseforozco@gmail.com';
    }
}
