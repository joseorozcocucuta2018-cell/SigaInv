<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as BaseLogoutResponse;
use Illuminate\Http\RedirectResponse;

class LogoutResponse implements BaseLogoutResponse
{
    public function toResponse($request): RedirectResponse
    {
        return redirect()->to('/login');
    }
}
