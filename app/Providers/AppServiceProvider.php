<?php

namespace App\Providers;

use App\Models\Advertisement;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view): void {
            $user = auth()->user();
            $view->with('activeAdvertisement', $user && ! $user->isAdmin()
                ? Advertisement::active()->latest('id')->first()
                : null);
        });
    }
}
