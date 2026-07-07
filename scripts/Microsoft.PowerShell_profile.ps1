#Requires -Version 7.0

#region CONFIGURACION GLOBAL
$ErrorActionPreference = "Stop"
$OutputEncoding        = [System.Text.UTF8Encoding]::new($false)   # PS7: UTF-8 sin BOM
[Console]::OutputEncoding = [System.Text.UTF8Encoding]::new($false) # PS7: consola UTF-8

$Colors = @{
    Success = "Green"
    Info    = "Cyan"
    Warning = "Yellow"
    Error   = "Red"
    Process = "Magenta"
    Detail  = "Gray"
}

$Icons = @{
    Success = "[OK]"
    Error   = "[X]"
    Warning = "[!]"
    Info    = "[i]"
    Process = "[*]"
    Bullet  = "->"
}

# Nombre del perfil para mios
$PROFILE_NAME = $MyInvocation.MyCommand.Path
#endregion

#region FUNCIONES AUXILIARES
function Write-Status {
    param(
        [Parameter(Mandatory)]
        [ValidateSet("Success","Info","Warning","Error","Process")]
        [string]$Type,

        [Parameter(Mandatory)]
        [string]$Message,

        [switch]$NoNewline
    )

    $icon   = $Icons[$Type]
    $color  = $Colors[$Type]
    $prefix = switch ($Type) {
        "Success" { "$icon EXITO"       }
        "Error"   { "$icon ERROR"       }
        "Warning" { "$icon ADVERTENCIA" }
        "Info"    { "$icon INFO"        }
        "Process" { "$icon PROCESO"     }
    }

    Write-Host "[$prefix] $Message" -ForegroundColor $color -NoNewline:$NoNewline.IsPresent
}

function Test-LaravelProject {
    param([switch]$RequireArtisan, [switch]$RequireComposer)

    $checks = @()
    if ($RequireArtisan)  { $checks += "artisan"      }
    if ($RequireComposer) { $checks += "composer.json" }
    if ($checks.Count -eq 0) { $checks = @("artisan") }

    foreach ($file in $checks) {
        if (-not (Test-Path $file)) {
            Write-Status Error "No se encontro '$file'. Estas en la raiz del proyecto Laravel?"
            return $false
        }
    }
    return $true
}

function Get-LaravelEnvironment {
    try {
        $out = php artisan env 2>&1 | Out-String
        if ($out -match "Current application environment:\s*(\w+)") {
            return $Matches[1].ToLower()
        }
        # Fallback: leer .env directamente
        if (Test-Path ".env") {
            $line = Select-String -Path ".env" -Pattern "^APP_ENV=" | Select-Object -First 1
            if ($line) { return ($line.Line -replace "APP_ENV=","").Trim().ToLower() }
        }
        return "unknown"
    }
    catch { return "unknown" }
}

function Invoke-Artisan {
    <#
    .SYNOPSIS
    Ejecuta comandos de Artisan con manejo de errores mejorado.
    #>
    param(
        [Parameter(Mandatory)]
        [string]$Command,

        [string]$Description,
        [switch]$Silent
    )

    if ($Description) { Write-Status Process $Description }

    # PS7: splat seguro — no usar $args (variable reservada)
    $cmdParts = $Command -split " "
    try {
        if ($Silent) {
            php artisan @cmdParts 2>&1 | Out-Null
        }
        else {
            php artisan @cmdParts 2>&1
        }
        return ($LASTEXITCODE -eq 0)
    }
    catch {
        Write-Status Error "Fallo artisan $Command : $($_.Exception.Message)"
        return $false
    }
}

function Write-Log {
    <#
    .SYNOPSIS
    Escribe una linea al log y opcionalmente a la consola con color.
    #>
    param(
        [string]$Message,
        [string]$LogFile,
        [string]$Color = "",
        [switch]$ConsoleOnly,
        [switch]$LogOnly
    )

    if (-not $LogOnly -and $Color) {
        Write-Host $Message -ForegroundColor $Color
    }
    elseif (-not $LogOnly) {
        Write-Host $Message
    }

    if (-not $ConsoleOnly -and $LogFile) {
        $Message | Out-File $LogFile -Append -Encoding UTF8
    }
}
#endregion

#region FUNCIONES PRINCIPALES

# ─────────────────────────────────────────────
function limpiar {
    <#
    .SYNOPSIS
    Limpia y optimiza la cache de Laravel.
    .DESCRIPTION
    Ejecuta optimize:clear, limpia Filament si esta instalado,
    y regenera el autoloader. Con -Aggressive tambien limpia rutas.
    .EXAMPLE
    limpiar
    limpiar -Aggressive
    #>
    [CmdletBinding()]
    param([switch]$Aggressive)

    Clear-Host
    if (-not (Test-LaravelProject -RequireArtisan)) { return }

    Write-Status Process "Iniciando limpieza..."
    $startTime = Get-Date

    $comandos = @(
        @{ Command = "config:clear"; Desc = "Configuracion"   },
        @{ Command = "cache:clear";  Desc = "Cache general"   },
        @{ Command = "view:clear";   Desc = "Vistas compiladas" }
    )
    if ($Aggressive) {
        $comandos += @{ Command = "route:clear"; Desc = "Rutas" }
    }

    foreach ($cmd in $comandos) {
        Write-Status Process "Limpiando $($cmd.Desc)..."
        Invoke-Artisan -Command $cmd.Command -Silent | Out-Null
    }

    # Filament — detectar sin crashear
    try {
        $hayFilament = php artisan list 2>&1 | Select-String "filament:optimize"
        if ($hayFilament) {
            Write-Status Process "Limpiando cache Filament..."
            Invoke-Artisan -Command "filament:optimize-clear" -Silent | Out-Null
        }
    }
    catch { <# silencioso #> }

    if (-not $Aggressive) {
        Write-Status Process "Regenerando config cache..."
        Invoke-Artisan -Command "config:cache" -Silent | Out-Null
    }

    Write-Status Process "Optimizando autoloader..."
    composer dump-autoload --optimize 2>&1 | Out-Null

    $dur = (Get-Date) - $startTime
    Write-Status Success "Limpieza completada en $($dur.TotalSeconds.ToString('F2'))s"
}

# ─────────────────────────────────────────────
function update {
    <#
    .SYNOPSIS
    Actualiza dependencias Composer y NPM de forma inteligente.
    .DESCRIPTION
    Detecta si composer.json es mas reciente que composer.lock
    para decidir entre update e install. Compila assets al final.
    .EXAMPLE
    update
    update -SkipNpm
    update -DryRun
    #>
    [CmdletBinding()]
    param(
        [switch]$SkipNpm,
        [switch]$SkipComposer,
        [switch]$DryRun
    )

    Clear-Host
    if (-not (Test-LaravelProject -RequireComposer)) { return }

    $startTime = Get-Date

    if (-not $SkipComposer) {
        Write-Status Process "Analizando dependencias Composer..."

        $cJson = Get-Item "composer.json" -ErrorAction SilentlyContinue
        $cLock = Get-Item "composer.lock" -ErrorAction SilentlyContinue

        if ($cLock -and $cJson.LastWriteTime -gt $cLock.LastWriteTime) {
            Write-Status Warning "composer.json mas reciente que composer.lock — actualizando..."
            if (-not $DryRun) { composer update --optimize-autoloader }
        }
        else {
            Write-Status Info "Instalando dependencias..."
            if (-not $DryRun) { composer install --optimize-autoloader }
        }

        Write-Status Process "Verificando vulnerabilidades..."
        try { composer audit --format=table 2>&1 }
        catch { Write-Status Warning "No se pudo completar el audit" }
    }

    if (-not $SkipNpm -and (Test-Path "package.json")) {
        Write-Status Process "Instalando dependencias NPM..."
        npm install 2>&1

        Write-Status Process "Compilando assets..."
        npm run build 2>&1
    }

    $dur = (Get-Date) - $startTime
    Write-Status Success "Actualizacion completada en $($dur.TotalSeconds.ToString('F2'))s"
}

# ─────────────────────────────────────────────
function server {
    <#
    .SYNOPSIS
    Inicia el servidor de desarrollo Laravel.
    .DESCRIPTION
    Detecta Laravel Sail automaticamente. Soporta puerto y host custom.
    .EXAMPLE
    server
    server -Port 8080
    server -NoOpen
    #>
    [CmdletBinding()]
    param(
        [int]$Port     = 8000,
        [string]$Host  = "localhost",
        [switch]$NoOpen,
        [switch]$UseSail
    )

    Clear-Host

    $hasSail = (Test-Path "vendor/bin/sail") -or (Test-Path "sail")
    if ($hasSail -and -not $UseSail) {
        Write-Status Info "Laravel Sail detectado."
        $resp = Read-Host "Usar Sail en lugar de artisan serve? (S/N)"
        if ($resp -eq 'S') { $UseSail = $true }
    }

    if ($UseSail) {
        Write-Status Process "Iniciando Laravel Sail..."
        ./vendor/bin/sail up -d
        Start-Sleep -Seconds 3
        Write-Status Success "Sail disponible en http://localhost"
    }
    else {
        if (-not (Test-LaravelProject -RequireArtisan)) { return }

        $url = "http://$Host`:$Port"
        Write-Status Process "Iniciando servidor Laravel..."
        Write-Host "  URL     : $url"       -ForegroundColor $Colors.Detail
        Write-Host "  Detener : Ctrl+C`n"   -ForegroundColor $Colors.Warning

        if (-not $NoOpen) { Start-Process $url }
        php artisan serve --host=$Host --port=$Port
    }
}

# ─────────────────────────────────────────────
function www {
    <#
    .SYNOPSIS
    Navega al directorio www\web del drive indicado.
    .EXAMPLE
    www
    www D
    www E
    #>
    [CmdletBinding()]
    param(
        [Parameter(Position = 0)]
        [ValidatePattern("^[a-zA-Z]$")]
        [string]$Drive = "D"
    )

    try {
        Clear-Host
        $Drive      = $Drive.ToUpper()
        $targetPath = "$Drive`:\www\web"

        if (-not (Test-Path "$Drive`:\")) {
            Write-Status Error "El drive $Drive`: no existe."
            return
        }
        if (Test-Path $targetPath) {
            Set-Location $targetPath
            Write-Status Success "Directorio: $targetPath"
            Write-Host "  $(Get-Location)" -ForegroundColor $Colors.Info
        }
        else {
            Write-Status Error "El directorio '$targetPath' no existe."
        }
    }
    catch {
        Write-Status Error "Error al cambiar directorio: $($_.Exception.Message)"
    }
}

# ─────────────────────────────────────────────
function migrar {
    <#
    .SYNOPSIS
    Ejecuta migrate:fresh con seed opcional. Genera log completo.
    .DESCRIPTION
    Muestra el output en consola en tiempo real con colores
    y simultaneamente escribe todo al archivo migration_FECHA.log
    en la carpeta storage/logs del proyecto.
    .EXAMPLE
    migrar
    migrar -SkipSeed
    migrar -Force
    #>
    [CmdletBinding()]
    param(
        [switch]$SkipSeed,
        [switch]$Force
    )

    Clear-Host

    if (-not (Test-Path "artisan")) {
        Write-Status Error "No se encontro 'artisan'. Estas en la raiz del proyecto?"
        return
    }

    $startTime = Get-Date

    # --- Entorno desde .env ---
    $appEnv = "unknown"
    if (Test-Path ".env") {
        $envLine = Select-String -Path ".env" -Pattern "^APP_ENV=" | Select-Object -First 1
        if ($envLine) { $appEnv = ($envLine.Line -replace "APP_ENV=","").Trim() }
    }

    # --- Preparar log en storage/logs ---
    $logDir = Join-Path (Get-Location) "\logs"
    if (-not (Test-Path $logDir)) {
        New-Item -ItemType Directory -Path $logDir -Force | Out-Null
    }
    $logFile = Join-Path $logDir ("migration_{0}.log" -f (Get-Date -Format "yyyyMMdd_HHmmss"))

    # Funcion interna: escribe a consola Y al log
    function Dual-Write {
        param([string]$Line, [string]$Color = "")
        if ($Color) { Write-Host $Line -ForegroundColor $Color }
        else        { Write-Host $Line }
        $Line | Out-File $logFile -Append -Encoding UTF8
    }

    # --- Header del log ---
    $header = @(
        "========================================",
        " MIGRATION LOG",
        "========================================",
        " Fecha     : $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')",
        " Entorno   : $appEnv",
        " Directorio: $(Get-Location)",
        " SkipSeed  : $($SkipSeed.IsPresent)",
        " Force     : $($Force.IsPresent)",
        "========================================"
    )
    foreach ($h in $header) { Dual-Write $h "Cyan" }
    Write-Host ""

    try {
        # --- Confirmacion en produccion ---
        if ($appEnv -eq "production" -and -not $Force) {
            Write-Host ""
            Dual-Write " ATENCION: Entorno de PRODUCCION detectado!" "Red"
            $confirm = Read-Host " Escribe 'PRODUCCION' para confirmar"
            if ($confirm -ne "PRODUCCION") {
                Dual-Write " Operacion cancelada por el usuario." "Yellow"
                return
            }
        }

        # --- Construir comando ---
        $artisanCmd = @("migrate:fresh", "--force")
        if (-not $SkipSeed) { $artisanCmd += "--seed" }

        Dual-Write "" ""
        Dual-Write " Ejecutando: php artisan $($artisanCmd -join ' ')" "Magenta"
        Dual-Write "----------------------------------------" "DarkGray"

        # --- PS7: ejecutar y leer output linea a linea (consola + log) ---
        $exitCode = 0
        try {
            php artisan @artisanCmd 2>&1 | ForEach-Object {
                $line = $_
                # Colorear segun contenido
                $color = switch -Regex ($line) {
                    "FAILED|ERROR|error|Exception" { "Red"     }
                    "Seeding|Seeded"               { "Cyan"    }
                    "Migrated|migrated"            { "Green"   }
                    "Rolling back|Rolled back"     { "Yellow"  }
                    "Nothing to migrate"           { "Yellow"  }
                    default                        { "White"   }
                }
                Dual-Write $line $color
            }
            $exitCode = $LASTEXITCODE
        }
        catch {
            Dual-Write "Error al ejecutar artisan: $($_.Exception.Message)" "Red"
            $exitCode = 1
        }

        # --- Footer del log ---
        $dur = (Get-Date) - $startTime
        Dual-Write "----------------------------------------" "DarkGray"

        if ($exitCode -eq 0) {
            Dual-Write " ExitCode  : $exitCode (OK)"              "Green"
            Dual-Write " Duracion  : $($dur.TotalSeconds.ToString('F2'))s" "Green"
            Dual-Write " Log       : $logFile"                    "Cyan"
            Dual-Write "========================================" "DarkGray"
            Write-Host ""
            Write-Status Success "Migracion completada en $($dur.TotalSeconds.ToString('F2'))s"
            Write-Host "  Log generado en: $logFile" -ForegroundColor $Colors.Info
        }
        else {
            Dual-Write " ExitCode  : $exitCode (FALLO)"           "Red"
            Dual-Write " Duracion  : $($dur.TotalSeconds.ToString('F2'))s" "Red"
            Dual-Write " Log       : $logFile"                    "Cyan"
            Dual-Write "========================================" "DarkGray"
            Write-Host ""
            Write-Status Error "Migracion fallo. Revisa el log: $logFile"
        }
    }
    catch {
        $errMsg = $_.Exception.Message
        Dual-Write "EXCEPCION: $errMsg" "Red"
        Dual-Write "========================================" "DarkGray"
        Write-Status Error "Error durante migracion. Log: $logFile"
    }
}

# ─────────────────────────────────────────────
function modelo {
    <#
    .SYNOPSIS
    Crea modelo Laravel con opciones avanzadas.
    .DESCRIPTION
    Por defecto crea modelo con --all. Soporta API Resource,
    Policy y Form Requests adicionales.
    .EXAMPLE
    modelo Producto
    modelo Venta -Api -Policy
    modelo Cliente -NoAll
    #>
    [CmdletBinding()]
    param(
        [Parameter(Mandatory)]
        [string]$Name,

        [switch]$Api,
        [switch]$Policy,
        [switch]$Request,
        [switch]$NoAll
    )

    Clear-Host
    if (-not (Test-LaravelProject -RequireArtisan)) { return }

    Write-Status Process "Creando modelo: $Name"

    # PS7: no usar $args — es variable reservada
    $artisanParams = @("make:model", $Name)
    if (-not $NoAll) { $artisanParams += "--all" }
    php artisan @artisanParams

    if ($Api) {
        Invoke-Artisan -Command "make:resource $($Name)Resource" `
                       -Description "Creando API Resource" -Silent | Out-Null
    }
    if ($Policy) {
        Invoke-Artisan -Command "make:policy $($Name)Policy --model=$Name" `
                       -Description "Creando Policy" -Silent | Out-Null
    }
    if ($Request) {
        Invoke-Artisan -Command "make:request Store$($Name)Request" `
                       -Description "Creando Store Request" -Silent | Out-Null
        Invoke-Artisan -Command "make:request Update$($Name)Request" `
                       -Description "Creando Update Request" -Silent | Out-Null
    }

    Write-Status Success "Modelo '$Name' creado."
}
Set-Alias -Name model -Value modelo -Force -Option AllScope

# ─────────────────────────────────────────────
function actualiza {
    <#
    .SYNOPSIS
    Aplica migraciones pendientes (migrate sin fresh).
    .EXAMPLE
    actualiza
    #>
    try {
        Clear-Host
        if (-not (Test-LaravelProject -RequireArtisan)) { return }
        Write-Status Process "Aplicando migraciones pendientes..."
        php artisan migrate 2>&1
        Write-Status Success "Migraciones aplicadas."
    }
    catch {
        Write-Status Error "Error: $($_.Exception.Message)"
    }
}

# ─────────────────────────────────────────────
function recurso {
    <#
    .SYNOPSIS
    Crea un recurso Filament con --generate.
    .EXAMPLE
    recurso Producto
    #>
    [CmdletBinding()]
    param(
        [Parameter(Mandatory)]
        [string]$Name
    )

    try {
        Clear-Host
        if (-not (Test-LaravelProject -RequireArtisan)) { return }
        Write-Status Process "Creando recurso Filament: $Name..."
        php artisan make:filament-resource $Name --generate 2>&1
        Write-Status Success "Recurso Filament '$Name' creado."
    }
    catch {
        Write-Status Error "Error: $($_.Exception.Message)"
    }
}

# ─────────────────────────────────────────────
function creami {
    <#
    .SYNOPSIS
    Crea una migracion Laravel (create_NAME_table).
    .EXAMPLE
    creami productos
    #>
    [CmdletBinding()]
    param(
        [Parameter(Mandatory)]
        [string]$Name
    )

    try {
        Clear-Host
        if (-not (Test-LaravelProject -RequireArtisan)) { return }
        Write-Status Process "Creando migracion: create_$($Name)_table..."
        php artisan make:migration "create_$($Name)_table" 2>&1
        Write-Status Success "Migracion creada."
    }
    catch {
        Write-Status Error "Error: $($_.Exception.Message)"
    }
}

# ─────────────────────────────────────────────
function mios {
    <#
    .SYNOPSIS
    Lista todas las funciones definidas en este perfil.
    .EXAMPLE
    mios
    #>
    try {
        Clear-Host
        Write-Status Info "Funciones personalizadas disponibles:"
        Write-Host ("=" * 55) -ForegroundColor $Colors.Detail

        # PS7: filtrar por el archivo de este perfil especificamente
        $misFunciones = Get-Command -CommandType Function |
            Where-Object { $_.ScriptBlock.File -eq $PROFILE_NAME } |
            Sort-Object Name

        foreach ($func in $misFunciones) {
            $help     = Get-Help $func.Name -ErrorAction SilentlyContinue
            $synopsis = if ($help.Synopsis -and $help.Synopsis.Trim()) {
                $help.Synopsis.Trim()
            } else { "Sin descripcion" }
            Write-Host "  $($Icons.Bullet) $($func.Name.PadRight(12)) : $synopsis" -ForegroundColor White
        }

        Write-Host ("=" * 55) -ForegroundColor $Colors.Detail
        Write-Status Info "Usa 'Get-Help <funcion> -Full' para detalles."
    }
    catch {
        Write-Status Error "Error: $($_.Exception.Message)"
    }
}

# ─────────────────────────────────────────────
function prod {
    <#
    .SYNOPSIS
    Optimiza el proyecto para produccion.
    .EXAMPLE
    prod
    prod -SkipAssets
    #>
    [CmdletBinding()]
    param([switch]$SkipAssets)

    try {
        Clear-Host
        if (-not (Test-LaravelProject -RequireArtisan)) { return }

        $env = Get-LaravelEnvironment
        if ($env -in @("local","development")) {
            Write-Status Warning "Estas en entorno '$env'."
            $resp = Read-Host "Deseas continuar con la optimizacion? (SI / NO)"
            if ($resp -ne "SI") {
                Write-Status Info "Cancelado."
                return
            }
        }

        Write-Status Process "Optimizando para produccion..."
        foreach ($cmd in @("config:cache","route:cache","view:cache","optimize")) {
            Invoke-Artisan -Command $cmd -Description "  $cmd" -Silent | Out-Null
        }

        if (-not $SkipAssets -and (Test-Path "package.json")) {
            Write-Status Process "Compilando assets..."
            npm run build 2>&1
        }

        Write-Status Success "Proyecto optimizado para produccion."
        Write-Status Info   "Recuerda verificar el .env de produccion."
    }
    catch {
        Write-Status Error "Error: $($_.Exception.Message)"
    }
}

# ─────────────────────────────────────────────
function info {
    <#
    .SYNOPSIS
    Muestra informacion completa del proyecto Laravel.
    .EXAMPLE
    info
    #>
    try {
        Clear-Host
        if (-not (Test-LaravelProject -RequireArtisan)) { return }

        Write-Status Info "Informacion del Proyecto Laravel"
        Write-Host ("=" * 45) -ForegroundColor $Colors.Detail

        Write-Host "`n[VERSION]" -ForegroundColor $Colors.Warning
        php artisan --version 2>&1

        Write-Host "`n[ENTORNO]" -ForegroundColor $Colors.Warning
        php artisan env 2>&1

        Write-Host "`n[MIGRACIONES]" -ForegroundColor $Colors.Warning
        php artisan migrate:status --no-interaction 2>&1

        Write-Host "`n[DIRECTORIO]" -ForegroundColor $Colors.Warning
        Write-Host "  $(Get-Location)" -ForegroundColor White

        Write-Host "`n[DEPENDENCIAS]" -ForegroundColor $Colors.Warning
        foreach ($item in @(
            @{ File = "composer.json"; Label = "Composer" },
            @{ File = "package.json";  Label = "NPM"      },
            @{ File = ".env";          Label = "Archivo .env" }
        )) {
            if (Test-Path $item.File) {
                Write-Host "  $($Icons.Success) $($item.Label)" -ForegroundColor $Colors.Success
            } else {
                Write-Host "  $($Icons.Warning) $($item.Label) no encontrado" -ForegroundColor $Colors.Warning
            }
        }

        Write-Host "`n$("=" * 45)" -ForegroundColor $Colors.Detail
    }
    catch {
        Write-Status Error "Error: $($_.Exception.Message)"
    }
}

# ─────────────────────────────────────────────
function test {
    <#
    .SYNOPSIS
    Ejecuta tests PHPUnit o Pest con opciones.
    .EXAMPLE
    test
    test -Filter NombreTest
    test -Coverage
    #>
    [CmdletBinding()]
    param(
        [string]$Filter,
        [switch]$Coverage,
        [string]$Group
    )

    Clear-Host
    if (-not (Test-LaravelProject -RequireArtisan)) { return }

    # PS7: no usar $args
    $testParams = @()
    $usesPest   = Test-Path "pest.json"
    $testParams += if ($usesPest) { "pest" } else { "test" }

    if ($Filter)   { $testParams += "--filter=$Filter" }
    if ($Group)    { $testParams += "--group=$Group"   }
    if ($Coverage) { $testParams += "--coverage"       }

    Write-Status Process "Ejecutando tests$(if($usesPest){' (Pest)'})..."
    php artisan @testParams 2>&1
}

# ─────────────────────────────────────────────
function logs {
    <#
    .SYNOPSIS
    Muestra logs de Laravel con colores y filtrado.
    .DESCRIPTION
    Con -Follow hace tail en tiempo real (Ctrl+C para salir).
    .EXAMPLE
    logs
    logs -Follow
    logs -Filter "ERROR" -Lines 100
    #>
    [CmdletBinding()]
    param(
        [string]$Filter,
        [int]$Lines    = 50,
        [switch]$Follow
    )

    $logPath = "storage/logs/laravel.log"
    if (-not (Test-Path $logPath)) {
        Write-Status Error "No se encontro '$logPath'"
        return
    }

    $colorize = {
        param($line)
        if ($Filter -and $line -notmatch $Filter) { return }
        $color = switch -Regex ($line) {
            "ERROR|CRITICAL|ALERT|EMERGENCY" { "Red"     }
            "WARNING"                        { "Yellow"  }
            "INFO|DEBUG"                     { "Cyan"    }
            default                          { "White"   }
        }
        Write-Host $line -ForegroundColor $color
    }

    if ($Follow) {
        Write-Status Info "Siguiendo logs en tiempo real (Ctrl+C para salir)..."
        try {
            Get-Content $logPath -Tail $Lines -Wait | ForEach-Object { & $colorize $_ }
        }
        catch { Write-Host "" }  # Salida limpia con Ctrl+C
    }
    else {
        Get-Content $logPath -Tail $Lines | ForEach-Object { & $colorize $_ }
    }
}

# ─────────────────────────────────────────────
function tinker {
    <#
    .SYNOPSIS
    Inicia Laravel Tinker.
    .EXAMPLE
    tinker
    #>
    if (-not (Test-LaravelProject -RequireArtisan)) { return }
    Write-Status Info "Iniciando Tinker (Ctrl+C para salir)..."
    php artisan tinker 2>&1
}

# ─────────────────────────────────────────────
function deploy {
    <#
    .SYNOPSIS
    Checklist completo de despliegue a produccion.
    .DESCRIPTION
    Verifica entorno, APP_KEY, debug, ejecuta tests y optimiza.
    .EXAMPLE
    deploy
    deploy -SkipTests
    deploy -SkipAssets
    #>
    [CmdletBinding()]
    param(
        [switch]$SkipTests,
        [switch]$SkipAssets,
        [string]$Environment = "production"
    )

    Clear-Host
    if (-not (Test-LaravelProject -RequireArtisan)) { return }

    Write-Status Process "Checklist de despliegue"
    Write-Host ("=" * 50) -ForegroundColor $Colors.Detail

    $currentEnv = Get-LaravelEnvironment
    $envContent = if (Test-Path ".env") { Get-Content ".env" -Raw } else { "" }

    $checks = @(
        @{
            Name     = "Entorno correcto ($Environment)"
            Test     = { $currentEnv -eq $Environment }
            Required = $true
        },
        @{
            Name     = "APP_KEY configurada"
            Test     = { $envContent -match "APP_KEY=base64:" }
            Required = $true
        },
        @{
            Name     = "APP_DEBUG desactivado"
            Test     = { $envContent -match "APP_DEBUG=false" }
            Required = $false
        }
    )

    $failedRequired = 0
    foreach ($check in $checks) {
        Write-Host "  Verificando $($check.Name)... " -NoNewline
        $result = & $check.Test
        if ($result) {
            Write-Host "$($Icons.Success) OK" -ForegroundColor $Colors.Success
        }
        elseif ($check.Required) {
            Write-Host "$($Icons.Error) FALLO (requerido)" -ForegroundColor $Colors.Error
            $failedRequired++
        }
        else {
            Write-Host "$($Icons.Warning) FALLO (opcional)" -ForegroundColor $Colors.Warning
        }
    }

    if ($failedRequired -gt 0) {
        Write-Status Error "Corrige los problemas requeridos antes de continuar."
        return
    }

    if (-not $SkipTests) {
        Write-Status Process "Ejecutando tests..."
        php artisan test 2>&1
        if ($LASTEXITCODE -ne 0) {
            Write-Status Error "Tests fallaron. Despliegue abortado."
            return
        }
    }

    foreach ($opt in @("config:cache","route:cache","view:cache","event:cache","optimize")) {
        Invoke-Artisan -Command $opt -Description "  Ejecutando $opt" -Silent | Out-Null
    }

    if (-not $SkipAssets -and (Test-Path "package.json")) {
        Write-Status Process "Compilando assets para produccion..."
        npm run build 2>&1
    }

    Write-Status Success "Proyecto listo para despliegue."
    Write-Host "`nComandos sugeridos:" -ForegroundColor $Colors.Info
    Write-Host "  git push production main"   -ForegroundColor $Colors.Detail
    Write-Host "  php artisan migrate --force" -ForegroundColor $Colors.Detail
}
Set-Alias -Name d -Value deploy -Force
#endregion

#region IMPORTS
$ChocolateyProfile = "$env:ChocolateyInstall\helpers\chocolateyProfile.psm1"
if (Test-Path $ChocolateyProfile) {
    Import-Module $ChocolateyProfile -ErrorAction SilentlyContinue
}

$apagarScript = "D:\Carpetas de Usuario\Documents\WindowsPowerShell\Scripts\apagar.ps1"
if (Test-Path $apagarScript) {
    . $apagarScript
}
#endregion

function claudetg { claude $args --enable-auto-mode }