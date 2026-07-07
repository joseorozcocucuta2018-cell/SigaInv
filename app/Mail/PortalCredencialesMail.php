<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Cliente;
use App\Models\Empresa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable para enviar las credenciales de acceso al portal de clientes.
 *
 * Se usa en dos puntos:
 *  1. ClientesPortalSeeder — para los 2 clientes existentes al migrar
 *     del modelo User-con-rol-cliente a Cliente-Autenticable.
 *  2. Acción "Generar contraseña" en ClienteResource (Tarea 8.12) —
 *     cuando el admin regenera manualmente la contraseña de un cliente.
 *
 * Reglas:
 *  - ShouldQueue: el envío NO bloquea la transacción.
 *  - Password plana: NUNCA en logs (Log facade) ni dump/dd.
 *  - El password se pasa como propiedad pública para que esté disponible
 *    en la vista, pero la vista no lo loguea.
 */
class PortalCredencialesMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Cliente $cliente,
        public readonly string $passwordPlano,
    ) {}

    public function envelope(): Envelope
    {
        $empresa = Empresa::actual();
        $remitente = $empresa?->nombre_comercial ?? $empresa?->razon_social ?? 'SigaInv';

        return new Envelope(
            subject: "Credenciales de acceso al Portal de Clientes — {$remitente}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.portal.credenciales',
            with: [
                'cliente' => $this->cliente,
                'passwordPlano' => $this->passwordPlano,
                'empresa' => Empresa::actual(),
                'portalUrl' => url('/clientes'),
            ],
        );
    }
}
