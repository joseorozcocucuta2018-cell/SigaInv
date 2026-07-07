<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema en configuración</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            text-align: center;
            padding: 2rem;
            max-width: 480px;
        }

        .icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }

        .title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #f8fafc;
            margin-bottom: 1rem;
        }

        .mensaje {
            font-size: 1rem;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .badge {
            display: inline-block;
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 9999px;
            padding: 0.4rem 1rem;
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 2rem;
        }

        .btn-salir {
            display: inline-block;
            background-color: #f59e0b;
            color: #0f172a;
            font-weight: 600;
            padding: 0.65rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background-color 0.2s;
        }

        .btn-salir:hover {
            background-color: #d97706;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">⚙️</div>
        <h1 class="title">Sistema en configuración</h1>
        <p class="mensaje">{{ $mensaje }}</p>
        <div class="badge">HTTP 503 · Servicio no disponible temporalmente</div>
        <br>
        <a href="{{ route('filament.admin.auth.logout') }}" class="btn-salir">
            Cerrar sesión
        </a>
    </div>
</body>
</html>
