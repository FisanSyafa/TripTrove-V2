<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Import Controller Publik
use App\Http\Controllers\PublicPackageController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;

// Import Controller Admin
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;

use App\Http\Controllers\Admin\VehicleTypeController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\UserReviewController;
use App\Http\Controllers\ContactController;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Auth\GoogleAuthController;

use App\Http\Controllers\Admin\AnnouncementController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
|--------------------------------------------------------------------------
| Language Switcher Route
|--------------------------------------------------------------------------
*/
Route::get('/lang/{locale}', function (string $locale) {
    // 1. Validasi bahasa yang didukung
    if (! in_array($locale, ['en', 'id', 'ms'])) {
        abort(404);
    }

    // 2. Simpan bahasa pilihan ke Sesi
    Session::put('locale', $locale);

    // 3. Kembali ke halaman sebelumnya
    return Redirect::back();
})->name('lang.switch');

Route::get('/currency/{currency}', function (string $currency) {
    // 1. Validasi mata uang yang didukung
    if (! in_array($currency, ['IDR', 'USD', 'MYR', 'SGD', 'EUR'])) {
        abort(404);
    }

    // 2. Simpan mata uang pilihan ke Sesi
    Session::put('currency', $currency);

    // 3. Kembali ke halaman sebelumnya
    return Redirect::back();
})->name('currency.switch');

// Rute Google Auth
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// == Rute Publik ==
// Halaman Utama (Homepage)
Route::get('/', [PublicPackageController::class, 'index'])->name('home');

// Halaman Detail Paket
Route::get('/paket/{package:slug}', [PublicPackageController::class, 'show'])->name('packages.show');

// Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Proses booking tanpa auth
Route::get('/book/{package:slug}', [BookingController::class, 'create'])->name('booking.create');

// BARU: Halaman Semua Paket (dengan filter)
Route::get('/packages', [PublicPackageController::class, 'allPackages'])->name('packages.all');

// Halaman Detail Paket
Route::get('/paket/{package:slug}', [PublicPackageController::class, 'show'])->name('packages.show');

// == Rute Autentikasi ==
// Rute login, register, forgot password, dll. disertakan dari file auth.php
require __DIR__.'/auth.php';

// == Rute Khusus Pengguna Terautentikasi ==
Route::middleware(['auth', 'verified'])->group(function () {
    // Dasbor Pengguna Biasa
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pengelolaan Profil (dari Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Proses Booking
    Route::post('/book', [BookingController::class, 'store'])->name('booking.store');

    // Review
    Route::get('bookings/{booking}/review', [UserReviewController::class, 'create'])->name('reviews.create');
    Route::post('reviews', [UserReviewController::class, 'store'])->name('reviews.store');

    // Payment
    Route::get('bookings/{booking}/payment', [PaymentController::class, 'create'])->name('payment.create');
    Route::post('bookings/{booking}/payment', [PaymentController::class, 'store'])->name('payment.store');
    Route::post('bookings/{booking}/payment/success', [PaymentController::class, 'handleSuccess'])->name('payment.success');

    // Invoice
    Route::get('bookings/{booking}/invoice', [BookingController::class, 'invoice'])->name('booking.invoice');
    
    // Pay on Arrival
    Route::post('bookings/{booking}/pay-on-arrival', [BookingController::class, 'payOnArrival'])->name('booking.pay-on-arrival');

    // Route::get('/', [PublicPackageController::class, 'index'])->name('home');
});

// == Rute Khusus Admin ==
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dasbor Admin
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Manajemen Paket (CRUD) - Menggunakan alias controller
    Route::resource('packages', AdminPackageController::class);

    // RUTE GALERI
    Route::post('/packages/{package}/gallery', [AdminPackageController::class, 'storeGallery'])->name('packages.gallery.store');
    Route::delete('/gallery/{gallery}', [AdminPackageController::class, 'destroyGallery'])->name('packages.gallery.destroy');

    // Manajemen Booking (Index, Show, Update) - Menggunakan alias controller
    Route::get('bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::put('bookings/{booking}', [AdminBookingController::class, 'update'])->name('bookings.update');

    Route::resource('vehicle-types', VehicleTypeController::class);
    Route::resource('vehicles', VehicleController::class);

    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::put('reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
    Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
    
    Route::resource('announcements', AnnouncementController::class);

    // Export Routes
    Route::get('export/revenue', [AdminDashboardController::class, 'exportRevenueCsv'])->name('export.revenue');
    Route::get('export/packages', [AdminDashboardController::class, 'exportPackagesCsv'])->name('export.packages');
    Route::get('export/bookings', [AdminDashboardController::class, 'exportBookingsCsv'])->name('export.bookings');

});