@php
    $empresaNombre = $empresa?->nombre_comercial ?? $empresa?->razon_social ?? 'SigaInv';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Credenciales Portal de Clientes</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1f2937;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background:#f4f6f8;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="560" style="max-width:560px;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.06);">
                    <tr>
                        <td style="background:#059669;padding:24px 32px;color:#ffffff;">
                            <h1 style="margin:0;font-size:20px;font-weight:600;">{{ $empresaNombre }}</h1>
                            <p style="margin:6px 0 0;font-size:14px;opacity:0.9;">Portal de Clientes</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px;font-size:16px;">Hola <strong>{{ $cliente->nombre }}</strong>,</p>
                            <p style="margin:0 0 24px;font-size:15px;line-height:1.5;">
                                Te hemos creado tus credenciales de acceso al Portal de Clientes. Desde allí podrás consultar tus facturas, remisiones y cotizaciones en cualquier momento.
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;margin-bottom:24px;">
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <p style="margin:0 0 8px;font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Correo de acceso</p>
                                        <p style="margin:0 0 16px;font-size:15px;font-weight:600;word-break:break-all;">{{ $cliente->email }}</p>

                                        <p style="margin:0 0 8px;font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Contraseña temporal</p>
                                        <p style="margin:0;font-size:18px;font-weight:700;color:#059669;font-family:Menlo,Monaco,Consolas,monospace;letter-spacing:0.04em;">{{ $passwordPlano }}</p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 24px;font-size:14px;line-height:1.5;background:#fef3c7;border-left:3px solid #f59e0b;padding:12px 16px;border-radius:4px;">
                                Por seguridad, el sistema te solicitará cambiar esta contraseña en tu primer ingreso.
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $portalUrl }}" style="display:inline-block;background:#059669;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:6px;font-size:15px;font-weight:600;">Ingresar al Portal</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0;font-size:13px;color:#6b7280;line-height:1.5;">
                                Si tienes alguna duda o problema para ingresar, contáctanos respondiendo este correo.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f9fafb;padding:20px 32px;border-top:1px solid #e5e7eb;">
                            <p style="margin:0;font-size:12px;color:#6b7280;text-align:center;">
                                {{ $empresaNombre }} &middot; Este es un mensaje automático, por favor no responder a esta dirección.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
