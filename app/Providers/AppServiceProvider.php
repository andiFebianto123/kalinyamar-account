<?php

namespace App\Providers;

use App\Services\ComponentCard;
use App\Services\ComponentModal;
use App\Services\ComponentScript;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function load_helper(){
        $helper_path = app_path().'/Http/Helpers/'.'Helpers.php';


        if (File::isFile($helper_path)) {
            require_once $helper_path;
        }
    }

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

        // $this->load_helper();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
