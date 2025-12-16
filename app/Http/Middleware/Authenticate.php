<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        // Ganti 'login' dengan nama rute kustom Anda
        return $request->expectsJson() ? null : route('login.wali'); 
    }
}