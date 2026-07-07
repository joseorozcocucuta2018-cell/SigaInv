<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\DocumentoPdfMail;
use App\Models\Cotizacion;
use App\Models\Empresa;
use App\Models\Remision;
use App\Models\Venta;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Servicio para envío de documentos (Venta/Remision/Cotizacion) por correo.
 * Centraliza la lógica de:
 *  - Verificar email del cliente
 *  - Generar PDF
 *  - Adjuntar y enviar
 *  - Limpiar archivo temporal
 *
 * Se invoca desde VentaService::confirmar(), RemisionService::confirmar(),
 * CotizacionService::cambiarEstado(ENVIADA) y desde acciones manuales de reenvío.
 */
class DocumentoEmailService
{
    public function __construct(
        private readonly PdfGeneratorService $pdf,
    ) {}

    /**
     * Enviar documento por email (con PDF adjunto).
     *
     * @return bool true si se envió, false si se omitió (sin email)
     */
    public function enviarDocumento(Venta|Remision|Cotizacion $documento, string $tipo, ?string $emailOverride = null): bool
    {
        $email = $emailOverride ?? $documento->cliente?->email;

        if (empty($email)) {
            return false;
        }

        // Cargar relaciones necesarias para el PDF
        $documento->load(['cliente.ciudad', 'bodega', 'detalles.producto', 'detalles.impuesto', 'usuario']);

        $rutaPdf = $this->pdf->generarPdfDesdeVista(
            vista: 'pdf.documento',
            datos: [
                'documento' => $documento,
                'empresa' => Empresa::actual()?->load('ciudad'),
                'tipo' => $tipo,
            ],
            opciones: [
                'nombre' => sprintf('%s-%s', $tipo, $documento->numero ?? 'doc'),
            ],
        );

        try {
            Mail::to($email)->send(new DocumentoPdfMail($documento, $tipo, $rutaPdf));

            return true;
        } catch (\Throwable $e) {
            Log::warning("Error enviando {$tipo} por correo: ".$e->getMessage(), [
                'documento_id' => $documento->id,
                'email' => $email,
            ]);

            return false;
        } finally {
            // Limpiar PDF temporal
            if (is_file($rutaPdf)) {
                @unlink($rutaPdf);
            }
        }
    }
}
