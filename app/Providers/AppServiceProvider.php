<?php

namespace App\Providers;


use App\Exceptions\GlobalExceptionHandler;
use App\Interfaces\UserInterface;
use App\Services\UserService;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use App\ServiceInterfaces\PaymentInterface;
use App\ServiceInterfaces\SingleContentInterface;
use App\Services\PaymentService;
use App\Services\SingleContentService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(UserInterface::class, UserService::class);
        $this->app->singleton(SingleContentInterface::class, SingleContentService::class);
        $this->app->singleton(PaymentInterface::class, PaymentService::class);
       // $this->app->singleton(ExceptionHandler::class, GlobalExceptionHandler::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
