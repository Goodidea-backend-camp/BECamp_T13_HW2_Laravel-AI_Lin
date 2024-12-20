<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
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
        //Google第三方登入的設定
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $socialiteWasCalled) {
            $socialiteWasCalled->extendSocialite('google', \SocialiteProviders\Google\Provider::class);
        });
    }
}
