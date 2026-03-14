<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // <-- Tambahkan ini
use Illuminate\Support\Facades\URL;
use App\Models\User; // <-- Tambahkan ini
use App\Models\Booking;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        if (!app()->runningInConsole()) {
            $host = request()->getHost();
            
            // Cek jika Production ATAU domain mengandung 'ngrok'
            if (config('app.env') !== 'local' || str_contains($host, 'ngrok-free.dev')) {
                URL::forceScheme('https');
            }
        }
        // Tambahkan Gate ini
        Gate::define('update-booking', function (User $user, Booking $booking) {
            return $user->id === $booking->user_id;
        });
    }
}
