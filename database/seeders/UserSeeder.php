<?php

namespace Database\Seeders;

use App\Enums\UserEstado;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // USUARIO PROTEGIDO - Administrador Principal
        // ============================================================
        $admin = User::firstOrCreate(
            ['email' => 'joseforozco@gmail.com'],
            [
                'name' => 'José Francisco Orozco',
                'password' => Hash::make('Digital2019**'), // Cambiar en producción
                'celular' => '3001234567',
                'cargo' => 'Administrador del Sistema',
                'estado' => UserEstado::ACTIVO,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('administrador');
        $this->command->info("Usuario protegido creado: {$admin->email}");

        // ============================================================
        // USUARIOS DE PRUEBA
        // ============================================================

        // Auxiliar
        $auxiliar = User::firstOrCreate(
            ['email' => 'auxiliar@sigainv.test'],
            [
                'name' => 'Usuario Auxiliar',
                'password' => Hash::make('password'),
                'celular' => '3001111111',
                'cargo' => 'Auxiliar Administrativo',
                'estado' => UserEstado::ACTIVO,
                'email_verified_at' => now(),
            ]
        );
        $auxiliar->assignRole('auxiliar');
        $this->command->info("Usuario auxiliar creado: {$auxiliar->email}");

        // Contador
        $contador = User::firstOrCreate(
            ['email' => 'contador@sigainv.test'],
            [
                'name' => 'Usuario Contador',
                'password' => Hash::make('password'),
                'celular' => '3002222222',
                'cargo' => 'Contador',
                'estado' => UserEstado::ACTIVO,
                'email_verified_at' => now(),
            ]
        );
        $contador->assignRole('contador');
        $this->command->info("Usuario contador creado: {$contador->email}");

        // Vendedor
        $vendedor = User::firstOrCreate(
            ['email' => 'vendedor@sigainv.test'],
            [
                'name' => 'Usuario Vendedor',
                'password' => Hash::make('password'),
                'celular' => '3003333333',
                'cargo' => 'Vendedor',
                'estado' => UserEstado::ACTIVO,
                'email_verified_at' => now(),
            ]
        );
        $vendedor->assignRole('vendedor');
        $this->command->info("Usuario vendedor creado: {$vendedor->email}");

        $this->command->info('Usuarios de prueba creados correctamente.');
        $this->command->warn('Contraseña por defecto para todos: "password"');
    }
}
