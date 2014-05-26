<?php namespace Larapress\Providers;

use Illuminate\Support\ServiceProvider;
use Larapress\Services\Captcha;

class CaptchaServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('captcha', function()
        {
            return new Captcha(
                $this->app->make('view'),
                $this->app->make('config'),
                $this->app->make('session.store'),
                $this->app->make('helpers')
            );
        });
    }

}
