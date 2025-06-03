<?php

// app/Http/Middleware/SetLocaleTimezone.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;

class SetLocaleTimezone
{
    public function handle($request, Closure $next)
    {
        $locale = App::getLocale();
        $timezones = config('timezones');
        $timezone = $timezones[$locale] ?? config('app.timezone');

        Config::set('app.timezone', $timezone);
        date_default_timezone_set($timezone);
        // Date::setTimezone($timezone);

        return $next($request);
    }
}
