<?php

namespace App\Providers;

use App\Services\ComponentCard;
use App\Services\ComponentModal;
use App\Services\ComponentScript;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ComponentCard::class, function () {
            return new ComponentCard();
        });
        $this->app->alias(ComponentCard::class, 'component.card');

        $this->app->singleton(ComponentModal::class, function () {
            return new ComponentModal();
        });
        $this->app->alias(ComponentModal::class, 'component.modal');

        $this->app->singleton(ComponentScript::class, function(){
            return new ComponentScript();
        });
        $this->app->alias(ComponentScript::class, 'component.script');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
