<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\ClientePanelProvider;
use Mccarlosen\LaravelMpdf\LaravelMpdfServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    ClientePanelProvider::class,
    LaravelMpdfServiceProvider::class,
];
