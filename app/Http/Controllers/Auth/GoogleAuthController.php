<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Arahkan pengguna ke halaman autentikasi Google.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Tangani callback dari Google.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // 1. Cari user berdasarkan google_id
            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                // 2. Jika user ada, update token dan login
                $user->update(['google_token' => $googleUser->token]);
            } else {
                // 3. Jika user tidak ada by google_id, cari berdasarkan email
                $user = User::where('email', $googleUser->email)->first();

                if ($user) {
                    // 4. Jika email ada, tautkan akun (update google_id)
                    $user->update([
                        'google_id' => $googleUser->id,
                        'google_token' => $googleUser->token,
                        'avatar' => $user->avatar ?? $googleUser->avatar, // Ambil avatar jika belum ada
                    ]);
                } else {
                    // 5. Jika tidak ada sama sekali, buat user baru
                    $user = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                        'google_token' => $googleUser->token,
                        'avatar' => $googleUser->avatar,
                        'password' => null, // Password null karena login via Google
                    ]);
                }
            }

            // Login user dan redirect ke dashboard
            Auth::login($user, true); // 'true' untuk "remember me"
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            // Jika terjadi error, kembali ke halaman login
            return redirect()->route('login')->with('error', 'Gagal login dengan Google. Silakan coba lagi.');
        }
    }
}