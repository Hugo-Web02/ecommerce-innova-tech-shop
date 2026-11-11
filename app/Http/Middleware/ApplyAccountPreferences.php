<?php

namespace App\Http\Middleware;

use App\Models\AccountPreference;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ApplyAccountPreferences
{
    public function handle(Request $request, Closure $next): Response
    {
        $language = $request->user()
            ? AccountPreference::preferences($request->user()->id)['language']
            : 'es';

        App::setLocale($language);

        return $next($request);
    }
}
