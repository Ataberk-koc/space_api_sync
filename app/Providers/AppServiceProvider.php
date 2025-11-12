<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\DataSyncCompleted;
use App\Listeners\SendSyncNotification;
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
        // Passport ayarları - Laravel 11 için grant types'ı manuel kaydet
        if (config('passport.grants.password')) {
            Passport::enablePasswordGrant();
        }
        
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addDays(180));
        
        // Event-Listener mapping
        Event::listen(
            DataSyncCompleted::class,
            [SendSyncNotification::class, 'handle']
        );
    }
}
