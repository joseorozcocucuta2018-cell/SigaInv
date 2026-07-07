# Tests Automatizados — sigaInv
## Laravel 12 + Filament 4.x + Pest PHP

---

## CONTENIDO DEL PAQUETE

```
.env.testing                          ← Configuración para BD de pruebas
setup-test-db.ps1                     ← Script para crear sigainv_test en MySQL

tests/
├── Pest.php                          ← Configuración global + helpers
├── TestCase.php                      ← Clase base
├── Unit/Models/
│   ├── UserTest.php                  ← Modelo User (fillable, casts, canAccessPanel)
│   ├── ClienteTest.php               ← Modelo Cliente (relaciones, reglas negocio)
│   └── ProveedorTest.php             ← Modelo Proveedor (relaciones, reglas negocio)
├── Feature/Auth/
│   └── LoginTest.php                 ← Autenticación Filament (acceso, denegado)
├── Feature/Resources/
│   ├── UserResourceTest.php          ← CRUD usuarios desde el panel
│   └── ClienteResourceTest.php       ← CRUD clientes desde el panel
└── Feature/Database/
    ├── MigrationsTest.php            ← Todas las tablas existen
    └── SeedersTest.php               ← Datos iniciales correctos
```

---

## INSTALACIÓN — PASO A PASO

### Paso 1: Copiar archivos al proyecto

Copiar todo el contenido de este ZIP a la raíz del proyecto:
```
D:\www\Web\sigaInv\
```

Los archivos de `tests/` reemplazan los existentes (son compatibles).

### Paso 2: Crear la BD de pruebas y configurar el entorno

Desde PowerShell 7, en la raíz del proyecto:
```powershell
.\setup-test-db.ps1
```

Este script hace automáticamente:
- Crea la BD `sigainv_test` en MySQL
- Genera el APP_KEY en `.env.testing`
- Ejecuta `migrate:fresh --seed` en la BD de pruebas

### Paso 3: Instalar dependencias de testing de Filament

```bash
composer require filament/filament --dev
php artisan pest:install
```

Si Pest ya está instalado (caso normal con `--pest` en la creación):
```bash
composer require pestphp/pest-plugin-livewire --dev
```

---

## EJECUTAR LOS TESTS

### Todos los tests
```bash
php artisan test
```

### Solo tests unitarios (más rápidos)
```bash
php artisan test tests/Unit
```

### Solo tests de base de datos
```bash
php artisan test tests/Feature/Database
```

### Un test específico
```bash
php artisan test --filter="UserTest"
php artisan test --filter="puede crear un cliente"
```

### Con detalle (verbose)
```bash
php artisan test --verbose
```

---

## FACTORIES REQUERIDAS

Los tests de modelos y resources necesitan factories para crear datos de prueba.
Si no las tienes creadas, ejecutar:

```bash
php artisan make:factory ClienteFactory --model=Cliente
php artisan make:factory ProveedorFactory --model=Proveedor
php artisan make:factory DepartamentoFactory --model=Departamento
php artisan make:factory CiudadFactory --model=Ciudad
```

Ejemplo mínimo de `ClienteFactory`:
```php
public function definition(): array
{
    return [
        'nombre'          => fake()->company(),
        'documento'       => fake()->numerify('##########'),
        'tipo_documento'  => 'NIT',
        'telefono'        => fake()->phoneNumber(),
        'email'           => fake()->unique()->safeEmail(),
        'direccion1'      => fake()->address(),
        'saldo'           => 0,
        'activo'          => true,
        'pais'            => 'Colombia',
        'limite_credito'  => 0,
        'dias_credito'    => 0,
        'dias_pago'       => 0,
    ];
}
```

---

## HELPERS DISPONIBLES EN TODOS LOS TESTS

Definidos en `tests/Pest.php`:

```php
// Crea usuario con rol y lo autentica
$user = loginComoAdmin();
$user = loginComoRol('auxiliar');

// Solo crea el usuario con rol (sin autenticar)
$user = crearUsuarioConRol('contador');
```

---

## RESULTADO ESPERADO

```
PASS  Tests\Unit\Models\UserTest
  ✓ tiene los campos fillable correctos
  ✓ castea activo como boolean
  ✓ permite acceso al panel si está activo y tiene rol válido
  ✓ deniega acceso al panel si está inactivo
  ...

PASS  Tests\Feature\Auth\LoginTest
  ✓ muestra la página de login correctamente
  ✓ un administrador activo puede acceder al panel
  ✓ un usuario inactivo es rechazado del panel
  ...

Tests:  38 passed
Time:   4.52s
```

---

## NOTAS IMPORTANTES

1. **Nunca ejecutar en la BD de producción** — `.env.testing` apunta a `sigainv_test`
2. **RefreshDatabase** borra y recrea la BD en cada test — es el comportamiento esperado
3. **Los tests de Resources** usan Livewire — requieren `pestphp/pest-plugin-livewire`
4. Si un test falla con `Table not found`, ejecutar `setup-test-db.ps1` nuevamente
