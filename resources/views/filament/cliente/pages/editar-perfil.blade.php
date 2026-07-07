<x-filament-panels::page>
    <div class="max-w-3xl mx-auto">
        <x-filament::section
            heading="Mi Perfil"
            description="Mantén tus datos de contacto actualizados para que podamos enviarte tus documentos."
            icon="heroicon-o-user"
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
                        Guardar cambios
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
    </div>
</x-filament-panels::page>
