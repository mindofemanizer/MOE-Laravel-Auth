# MOE Laravel Auth

Reusable authentication primitives for Laravel applications, including
registration, login, password reset, OTP, OAuth integration points, role
middleware, and portal access helpers.

## Install

```bash
composer require moe/laravel-auth
php artisan vendor:publish --provider="Moe\\Auth\\MoeAuthServiceProvider" --tag=moe-auth-config
php artisan migrate
```

Configure the host user model, guards, password policy, and optional
integrations in `config/moe-auth.php`.

## Testing

```bash
composer test
```

The package is framework-integrated but keeps application-specific models and
branding in the consuming project.
