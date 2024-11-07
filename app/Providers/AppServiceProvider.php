<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\AccountServiceInterface;
use App\Services\AccountService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AccountServiceInterface::class, AccountService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
