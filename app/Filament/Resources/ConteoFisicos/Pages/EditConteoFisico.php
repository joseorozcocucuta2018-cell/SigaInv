<?php

declare(strict_types=1);

namespace App\Filament\Resources\ConteoFisicos\Pages;

use App\Enums\ConteoFisicoEstado;
use App\Filament\Resources\AjusteInventarios\AjusteInventarioResource;
use App\Filament\Resources\ConteoFisicos\ConteoFisicoResource;
use App\Filament\Resources\Productos\Schemas\ProductoForm;
use App\Models\DetalleConteoFisico;
use App\Services\ConteoFisicoService;
use App\Services\ProductoService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Row;
use Symfony\Component\HttpFoundation\Response;

class EditConteoFisico extends EditRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = ConteoFisicoResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Sincronizar catálogo automáticamente si es saldo inicial
        if ($this->record->es_saldo_inicial && $this->record->estado?->value === ConteoFisicoEstado::ABIERTO->value) {
            app(ConteoFisicoService::class)->sincronizarProductosCatalogo($this->record);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información del Conteo')
                ->description(fn () => $this->record->es_saldo_inicial ? 'Este es un proceso de SALDO INICIAL' : null)
                ->columnSpanFull()
                ->columns(3)
                ->schema([
                    Select::make('bodega_id')
                        ->label('Bodega')
                        ->relationship('bodega', 'nombre')
                        ->disabled(),

                    DatePicker::make('fecha_inicio')
                        ->label('Fecha Inicio')
                        ->disabled(fn () => $this->record->estado?->value !== ConteoFisicoEstado::ABIERTO->value),

                    Textarea::make('observacion')
                        ->label('Observación')
                        ->rows(2)
                        ->columnSpanFull()
                        ->disabled(fn () => $this->record->estado?->value !== ConteoFisicoEstado::ABIERTO->value),
                ]),

            EmbeddedTable::make()->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        $esEditable = $this->record->estado?->value === ConteoFisicoEstado::ABIERTO->value;

        return $table
            ->query(
                DetalleConteoFisico::query()
                    ->where('conteo_fisico_id', $this->record->id)
                    ->with('producto.unidadMedida')
            )
            ->heading('Productos del Conteo')
            ->description('Ingresa la cantidad física contada en bodega. El sistema calcula la diferencia automáticamente.')
            ->columns([
                TextColumn::make('producto.codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->limit(35),

                TextColumn::make('producto.unidadMedida.nombre')
                    ->label('Unidad')
                    ->placeholder('—'),

                TextColumn::make('stock_sistema')
                    ->label('Stock Sistema')
                    ->numeric(3)
                    ->alignCenter(),

                TextInputColumn::make('cantidad_contada')
                    ->label('Cant. Contada')
                    ->type('number')
                    ->rules(['nullable', 'numeric', 'min:0'])
                    ->placeholder('—')
                    ->alignCenter()
                    ->disabled(! $esEditable),

                TextColumn::make('diferencia')
                    ->label('Diferencia')
                    ->numeric(3)
                    ->alignCenter()
                    ->badge()
                    ->placeholder('—')
                    ->color(fn ($state): string => match (true) {
                        $state !== null && (float) $state > 0 => 'success',
                        $state !== null && (float) $state < 0 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => $state !== null && (float) $state !== 0.0
                        ? (((float) $state > 0 ? '+' : '').number_format((float) $state, 2))
                        : ($state !== null ? '0' : null)
                    ),
            ])
            ->filters([
                Filter::make('sin_contar')
                    ->label('Sin contar')
                    ->query(fn (Builder $q) => $q->whereNull('cantidad_contada'))
                    ->toggle(),

                Filter::make('con_diferencias')
                    ->label('Con diferencias')
                    ->query(fn (Builder $q) => $q->whereNotNull('cantidad_contada')
                        ->whereRaw('cantidad_contada != stock_sistema'))
                    ->toggle(),

                Filter::make('contados')
                    ->label('Ya contados')
                    ->query(fn (Builder $q) => $q->whereNotNull('cantidad_contada'))
                    ->toggle(),
            ])
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([25, 50, 100])
            ->striped()
            ->headerActions([])
            ->recordAction(null)
            ->recordUrl(null);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            Action::make('crear_producto_form')
                ->label('Nuevo Producto')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->visible(fn () => $this->record->es_saldo_inicial && $this->record->estado?->value === ConteoFisicoEstado::ABIERTO->value)
                ->modalHeading('Crear Nuevo Producto')
                ->modalSubmitActionLabel('Crear')
                ->form(ProductoForm::getQuickCreateComponents())
                ->action(function (array $data) {
                    try {
                        $producto = ProductoService::crear($data);
                        app(ConteoFisicoService::class)->agregarProductoAConteo($this->record, $producto->id);

                        Notification::make()
                            ->title('Producto creado y agregado')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al crear producto')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            $this->getCancelFormAction(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('descargar_plantilla_conteo')
                    ->label('Descargar Plantilla (Excel)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(fn () => $this->descargarPlantilla()),

                Action::make('importar_conteo_excel')
                    ->label('Importar Conteo (Excel)')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->form([
                        FileUpload::make('archivo')
                            ->label('Archivo Excel con Conteo')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'])
                            ->required(),
                    ])
                    ->action(fn (array $data) => $this->importarConteo($data))
                    ->visible(fn () => $this->record->estado?->value === ConteoFisicoEstado::ABIERTO->value),
            ])
                ->label('Excel')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->button(),
            Action::make('procesar_saldo_inicial')
                ->label('Procesar')
                ->icon('heroicon-o-bolt')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Inicializar Inventario')
                ->modalDescription('Este proceso cerrará el conteo y aplicará las existencias al stock de forma inmediata. ¿Deseas continuar?')
                ->modalSubmitActionLabel('Sí, Procesar Stock')
                ->visible(fn () => $this->record->es_saldo_inicial && $this->record->estado?->value === ConteoFisicoEstado::ABIERTO->value)
                ->action(function () {
                    try {
                        app(ConteoFisicoService::class)->procesarConteoDirecto($this->record);

                        Notification::make()
                            ->title('Inventario Inicial Aplicado')
                            ->body('El conteo se cerró y el stock ha sido actualizado correctamente.')
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al procesar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('reporte_diferencias')
                ->label('Reporte de Diferencias')
                ->icon('heroicon-o-document-chart-bar')
                ->color('warning')
                ->modalHeading('Reporte de Discrepancias')
                ->modalContent(function () {
                    $diferencias = $this->record->detalles()
                        ->whereNotNull('cantidad_contada')
                        ->whereRaw('cantidad_contada != stock_sistema')
                        ->with('producto')
                        ->get();

                    if ($diferencias->isEmpty()) {
                        return view('filament.forms.components.empty-state', [
                            'message' => 'No hay discrepancias detectadas hasta el momento.',
                        ]);
                    }

                    return view('filament.conteos.reporte-diferencias', [
                        'diferencias' => $diferencias,
                    ]);
                })
                ->modalWidth('4xl')
                ->visible(fn () => $this->record->estado?->value === ConteoFisicoEstado::ABIERTO->value
                    && ! $this->record->es_saldo_inicial),

            Action::make('cerrar')
                ->label('Cerrar Conteo')
                ->icon('heroicon-o-lock-closed')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Cerrar Conteo Físico')
                ->modalDescription('¿Estás seguro? Se calcularán las diferencias. Las filas sin cantidad contada serán ignoradas.')
                ->modalSubmitActionLabel('Sí, Cerrar')
                ->visible(fn () => $this->record->estado?->value === ConteoFisicoEstado::ABIERTO->value
                    && ! $this->record->es_saldo_inicial
                    && (Auth::user()?->can('conteo_fisico.cerrar') ?? false))
                ->action(function () {
                    try {
                        app(ConteoFisicoService::class)->cerrarConteo($this->record);

                        Notification::make()
                            ->title('Conteo cerrado')
                            ->body('Las diferencias han sido calculadas.')
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al cerrar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('generar_ajuste')
                ->label('Generar Ajuste')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Generar Ajuste de Inventario')
                ->modalDescription('Se creará un ajuste de inventario en borrador con las diferencias encontradas.')
                ->modalSubmitActionLabel('Sí, Generar Ajuste')
                ->visible(fn () => $this->record->estado?->value === ConteoFisicoEstado::CERRADO->value
                    && (Auth::user()?->can('conteo_fisico.generar_ajuste') ?? false))
                ->action(function () {
                    try {
                        $ajuste = app(ConteoFisicoService::class)->generarAjuste($this->record);

                        Notification::make()
                            ->title('Ajuste generado')
                            ->body("Se creó el ajuste #{$ajuste->numero} en borrador.")
                            ->success()
                            ->actions([
                                Action::make('ver_ajuste')
                                    ->label('Ver Ajuste')
                                    ->url(AjusteInventarioResource::getUrl('edit', ['record' => $ajuste]))
                                    ->button(),
                            ])
                            ->send();

                        $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al generar ajuste')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            DeleteAction::make()
                ->visible(fn () => $this->record->estado?->value === ConteoFisicoEstado::ABIERTO->value),
        ];
    }

    public function descargarPlantilla(): Response
    {
        $conteo = $this->record;
        $detalles = $conteo->detalles()->with('producto')->get();

        $plantilla = new class($detalles) implements FromArray, WithHeadings
        {
            public function __construct(protected $detalles) {}

            public function headings(): array
            {
                return [
                    'id_detalle',
                    'codigo',
                    'producto',
                    'stock_sistema',
                    'cantidad_contada',
                ];
            }

            public function array(): array
            {
                return $this->detalles->map(fn ($d) => [
                    $d->id,
                    $d->producto->codigo,
                    $d->producto->nombre,
                    $d->stock_sistema,
                    $d->cantidad_contada ?? '',
                ])->toArray();
            }
        };

        return Excel::download($plantilla, "conteo_{$conteo->numero}_{$conteo->bodega->nombre}.xlsx");
    }

    public function importarConteo(array $data): void
    {
        $archivo = storage_path('app/'.$data['archivo']);

        if (! file_exists($archivo)) {
            Notification::make()->title('Archivo no encontrado')->danger()->send();

            return;
        }

        $importador = new class($this->record->id) implements OnEachRow, WithHeadingRow
        {
            public int $actualizados = 0;

            public int $errores = 0;

            public function __construct(protected int $conteoId) {}

            public function onRow(Row $row): void
            {
                $data = $row->toArray();
                $idDetalle = $data['id_detalle'] ?? null;
                $cantidad = $data['cantidad_contada'] ?? null;

                if ($idDetalle && $cantidad !== null && $cantidad !== '') {
                    $detalle = DetalleConteoFisico::where('id', $idDetalle)
                        ->where('conteo_fisico_id', $this->conteoId)
                        ->first();

                    if ($detalle) {
                        $detalle->update(['cantidad_contada' => (float) $cantidad]);
                        $this->actualizados++;
                    } else {
                        $this->errores++;
                    }
                }
            }
        };

        try {
            Excel::import($importador, $archivo);

            Notification::make()
                ->title('Importación de conteo completada')
                ->body("Registros actualizados: {$importador->actualizados}. Errores: {$importador->errores}")
                ->success()
                ->send();

            $this->refreshFormData(['detalles']);
        } catch (\Exception $e) {
            Notification::make()->title('Error en importación')->body($e->getMessage())->danger()->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeSave(): void
    {
        if ($this->record->estado?->value !== ConteoFisicoEstado::ABIERTO->value) {
            throw new \InvalidArgumentException(
                "No se puede editar un conteo en estado {$this->record->estado->label()}."
            );
        }
    }
}
