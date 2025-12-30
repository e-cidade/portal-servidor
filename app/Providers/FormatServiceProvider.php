<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\FormatHelper;

class FormatServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('format', function () {
            return new FormatHelper();
        });
    }

    public function boot()
    {
        //
    }
}
