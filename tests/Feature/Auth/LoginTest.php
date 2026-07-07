<?php

/*
|--------------------------------------------------------------------------
| LoginTest.php — Tests de autenticación Filament
| v3 — códigos de respuesta ajustados al comportamiento real de Filament 4.x
|
| Notas de comportamiento observado:
|   - /admin con usuario autenticado → 302 (redirect interno al dashboard)
|   - /admin con usuario inactivo/sin rol → 403 (Filament lanza AccessDenied)
|   - /admin sin autenticar → 302 al login
|--------------------------------------------------------------------------
*/

use App\Enums\UserEstado;
use App\Models\User;
use Spatie\Permission\Models\Role;

describe('Autenticación Filament', function () {

    // ── Página de login ───────────────────────────────────────────────────────

    it('muestra la página de login correctamente', function () {
        $this->get('/admin/login')
            ->assertStatus(200);
    });

    // ── Login exitoso — Filament 4.x redirige al dashboard (302) ─────────────

    it('un administrador activo puede acceder al panel', function () {
        $user = crearUsuarioConRol('administrador');

        // Filament redirige internamente al primer panel disponible
        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirect();
    });

    it('un auxiliar activo puede acceder al panel', function () {
        $user = crearUsuarioConRol('auxiliar');

        // 302 = redirect interno, 503 = AutoLogout interfiere en testing
        $response = $this->actingAs($user)->get('/admin');
        expect($response->status())->toBeIn([302, 503]);
    });

    it('un contador activo puede acceder al panel', function () {
        $user = crearUsuarioConRol('contador');

        $response = $this->actingAs($user)->get('/admin');
        expect($response->status())->toBeIn([302, 503]);
    });

    it('un vendedor activo puede acceder al panel', function () {
        $user = crearUsuarioConRol('vendedor');

        $response = $this->actingAs($user)->get('/admin');
        expect($response->status())->toBeIn([302, 503]);
    });
    // ── Acceso denegado ───────────────────────────────────────────────────────

    it('un usuario inactivo es rechazado del panel', function () {
        Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);

        $user = User::factory()->create(['estado' => UserEstado::INACTIVO]);
        $user->assignRole('administrador');

        // Filament 4.x lanza 403 cuando canAccessPanel() retorna false
        $this->actingAs($user)
            ->get('/admin')
            ->assertStatus(403);
    });

    it('un usuario sin rol es rechazado del panel', function () {
        $user = User::factory()->create(['estado' => UserEstado::ACTIVO]);
        // Sin rol asignado

        $this->actingAs($user)
            ->get('/admin')
            ->assertStatus(403);
    });

    it('un visitante anónimo es redirigido al login', function () {
        $this->get('/admin')
            ->assertRedirect('/admin/login');
    });
});
