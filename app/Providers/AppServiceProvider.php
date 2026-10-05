<?php

namespace App\Providers;

use App\Services\TopUp\MockTopUpProvider;
use App\Services\TopUp\TopUpProviderInterface;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TopUpProviderInterface::class, MockTopUpProvider::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return config('app.frontend_url') . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        });
    }
}
