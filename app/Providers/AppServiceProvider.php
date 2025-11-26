<?php

namespace App\Providers;

use App\Events\CreateCompteEven;
use App\Listeners\SendSmsListener;
use App\Models\Compte;
use App\Models\Marchant;
use App\Observers\CompteObserver;
use App\Observers\MarchantObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

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
        Passport::loadKeysFrom(storage_path());

        Compte::observe(CompteObserver::class);
        Marchant::observe(MarchantObserver::class);

        Event::listen(
            CreateCompteEven::class,
            [SendSmsListener::class, 'handle']
        );

        // // Enregistrer le provider personnalisé pour les comptes
        // $this->app->bind('auth.comptes', function ($app) {
        //     return new CompteUserProvider();
        // });
    }
}
