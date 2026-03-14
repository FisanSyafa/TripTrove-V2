<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // Pastikan baris ini ada
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        // 1. Konfigurasi Middleware Web (Inertia, dll)
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\SetLocale::class,
        ]);

        // Percayai semua proxy (Dibutuhkan jika deploy di belakang Load Balancer / Nginx cPanel dengan HTTPS)
        $middleware->trustProxies(at: '*');

        // 2. Alias Middleware (seperti 'admin')
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
        ]);

        // 3. MATIKAN CSRF UNTUK MIDTRANS (PENTING!)
        // Tambahkan blok ini agar Midtrans bisa mengirim notifikasi ke website Anda
        $middleware->validateCsrfTokens(except: [
            'api/midtrans-callback', // URL ini dibebaskan dari pengecekan CSRF
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();