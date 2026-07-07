<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| SISTEMA DE NOTIFICACIONES — sigaInv
|--------------------------------------------------------------------------
*/

// FASE 2 — Alertas de inventario
Schedule::command('alertas:inventario --stock')->dailyAt('07:00')
    ->name('alertas-stock-bajo')
    ->withoutOverlapping();

Schedule::command('alertas:inventario --lotes --dias=30')->weeklyOn(1, '07:00')
    ->name('alertas-lotes-vencer')
    ->withoutOverlapping();

// FASE 3 — Recordatorios de facturas
Schedule::command('recordatorios:facturas')->dailyAt('08:00')
    ->name('recordatorios-facturas')
    ->withoutOverlapping();

// FASE 4 — Alertas de cotizaciones
Schedule::command('alertas:cotizaciones')->dailyAt('08:00')
    ->name('alertas-cotizaciones')
    ->withoutOverlapping();

// FASE 5 — Resumen mensual de clientes
Schedule::command('resumen:mensual')->monthlyOn(1, '07:00')
    ->name('resumen-mensual-clientes')
    ->withoutOverlapping();
