<?php

namespace App\Providers;

use App\Events\ParkingActionRecorded;
use App\Listeners\CreateRecordListener;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

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
        Inertia::share([
            'csrf_token' => fn () => csrf_token(),
        ]);

        Model::preventLazyLoading();
    }
}
