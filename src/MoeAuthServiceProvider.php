<?php

namespace Moe\Auth;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Moe\Auth\Middleware\RequireRole;
use Moe\Auth\Services\GoogleService;
use Moe\Auth\Services\OtpService;

class MoeAuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerConfigs();
        $this->registerViews();
        $this->registerTranslations();
        $this->registerMiddleware();
        $this->registerPublishables();
    }

    protected function registerRoutes(): void
    {
        // Multi-portal apps (dual guard, custom prefixes) should set load_routes=false
        // and define their own routes while still using package actions/services.
        if (! config('moe-auth.load_routes', true)) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/auth.php');
    }

    public function register(): void
    {
        $this->app->singleton(OtpService::class, fn () => new OtpService);
        $this->app->singleton(GoogleService::class, fn () => new GoogleService);
    }

    protected function registerConfigs(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/moe-auth.php', 'moe-auth');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/moe-auth.php' => config_path('moe-auth.php'),
            ], 'moe-auth-config');
        }
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'moe-auth');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/moe-auth'),
            ], 'moe-auth-views');
        }
    }

    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'moe-auth');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/lang' => resource_path('lang/vendor/moe-auth'),
            ], 'moe-auth-translations');
        }
    }

    protected function registerMiddleware(): void
    {
        $this->app['router']->aliasMiddleware('role', RequireRole::class);
    }

    protected function registerPublishables(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/moe-auth.php' => config_path('moe-auth.php'),
        ], 'moe-auth-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'moe-auth-migrations');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/moe-auth'),
        ], 'moe-auth-views');

        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/moe-auth'),
        ], 'moe-auth-translations');
    }
}
