<?php

namespace Upyun;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(Upyun::class, function () {
            return new Upyun(new Config(config('services.upyun.bucket'), config('services.upyun.operator'), config('services.upyun.password')));
        });

        $this->app->alias(Upyun::class, 'upyun');
    }

    public function provides()
    {
        return [Upyun::class, 'upyun'];
    }
}
