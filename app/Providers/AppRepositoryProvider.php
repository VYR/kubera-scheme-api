<?php

namespace App\Providers;

use App\Interfaces\UserRepositoryInterface;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;
use App\ServiceInterfaces\PaymentInterface;
use App\ServiceInterfaces\SingleContentInterface;
use App\RepositoryInterfaces\PaymentRepositoryInterface;
use App\RepositoryInterfaces\SingleContentRepositoryInterface;
use App\Repositories\PaymentRepository;

class AppRepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(UserRepositoryInterface::class, UserRepository::class);
        $this->app->singleton(SingleContentRepositoryInterface::class, SingleContentRepositoryService::class);
        $this->app->singleton(PaymentRepositoryInterface::class, PaymentRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
