<x-filament-panels::page>
    <div class="max-w-2xl mx-auto">
        <x-filament::section
            heading="Cambiar contraseña"
            description="Por seguridad, te recomendamos usar una contraseña única que no utilices en otros servicios."
            icon="heroicon-o-key"
        >
            <form wire:submit="save" class="space-y-6">
                {{ $this->form }}

                <div class="flex items-center justify-end gap-x-3">
                    <x-filament::button
                        type="submit"
                        color="primary"
                        icon="heroicon-o-check"
                        wire:target="save"
                    >
                        Actualizar contraseña
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
    </div>
</x-filament-panels::page>
