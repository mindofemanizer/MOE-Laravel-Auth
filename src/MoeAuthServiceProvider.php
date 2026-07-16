<?php

namespace Moe\Auth;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Moe\Auth\Http\Livewire\ForgotPassword;
use Moe\Auth\Http\Livewire\Login;
use Moe\Auth\Http\Livewire\Register;
use Moe\Auth\Http\Livewire\ResetPassword;

class MoeAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/moe-auth.php', 'moe-auth');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'moe-auth');

        Livewire::component('moe-auth-login', Login::class);
        Livewire::component('moe-auth-register', Register::class);
        Livewire::component('moe-auth-forgot-password', ForgotPassword::class);
        Livewire::component('moe-auth-reset-password', ResetPassword::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/moe-auth.php' => config_path('moe-auth.php'),
            ], 'moe-auth-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/moe-auth'),
            ], 'moe-auth-views');
        }
    }
}
