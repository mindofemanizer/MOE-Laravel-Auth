<?php

use Illuminate\Support\Facades\Route;
use Moe\Auth\Http\Controllers\GoogleAuthController;
use Moe\Auth\Http\Livewire\{
    ForgotPassword,
    Login,
    Register,
    ResetPassword,
};

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});

// Google OAuth
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
