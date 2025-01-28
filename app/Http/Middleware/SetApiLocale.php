<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the 'Accept-Language' header from the request
        $lang = $request->header('Accept-Language', config('app.locale'));

        // Check if the requested language is supported, otherwise default to the application's default locale
        if (array_key_exists($lang, config('app.locales'))) { // Add your supported languages
            App::setLocale($lang);
        }

        return $next($request);
    }
}
