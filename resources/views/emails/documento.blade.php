<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { margin: 0; background: #f4f6f9; font-family: Arial, Helvetica, sans-serif; }
        .wrapper { max-width: 600px; margin: 0 auto; background: #fff; }
        @media screen and (max-width: 600px) { .wrapper { width: 100% !important; } }
    </style>
</head>
<body>
    <div class="wrapper">
        @include('pdf.documento', ['logoUrl' => $logoUrl ?? null])
    </div>
</body>
</html>
