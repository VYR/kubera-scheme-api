<?php

namespace App\Providers;


use App\Interfaces\UserInterface;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;
use App\ServiceInterfaces\PaymentInterface;
use App\ServiceInterfaces\SingleContentInterface;
use App\Services\PaymentService;
use App\Services\SingleContentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        // Add in boot function
        DB::listen(function($query){
        Storage::append('logs/query.log', '[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . $query->sql . ' [' . implode(', ', $query->bindings) . ']' . PHP_EOL . PHP_EOL);
        }
        );
    }
}
