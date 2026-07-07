<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Empresa;
use App\Services\PdfGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentoPdfMail extends Mailable
{
    use Queueable, SerializesModels;

    private static array $titulos = [
        'venta' => 'Factura de Venta',
        'remision' => 'Remisión',
        'cotizacion' => 'Cotización',
    ];

    public function __construct(
        public readonly mixed $documento,
        public readonly string $tipo,
        public readonly ?string $pdfPath = null,
    ) {}

    public function envelope(): Envelope
    {
        $empresa = Empresa::actual();
        $remitente = $empresa?->nombre_comercial ?? $empresa?->razon_social ?? 'sigaInv';
        $titulo = self::$titulos[$this->tipo] ?? 'Documento';

        return new Envelope(
            subject: "{$titulo} {$this->documento->numero} — {$remitente}",
        );
    }

    public function content(): Content
    {
        $empresa = Empresa::actual();
        $logoUrl = app(PdfGeneratorService::class)->obtenerLogoUrl();

        return new Content(
            view: 'emails.documento',
            with: [
                'titulo' => self::$titulos[$this->tipo] ?? 'Documento',
                'documento' => $this->documento,
                'empresa' => $empresa,
                'tipo' => $this->tipo,
                'logoUrl' => $logoUrl,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if ($this->pdfPath === null) {
            return [];
        }

        if (! is_file($this->pdfPath)) {
            return [];
        }

        return [
            Attachment::fromPath($this->pdfPath)
                ->as($this->nombreAdjunto())
                ->withMime('application/pdf'),
        ];
    }

    private function nombreAdjunto(): string
    {
        $titulo = self::$titulos[$this->tipo] ?? 'Documento';
        $numero = $this->documento->numero ?? 'doc';

        return sprintf('%s-%s.pdf', $titulo, $numero);
    }
}
