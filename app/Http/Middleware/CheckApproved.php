<?php

namespace App\Http\Middleware;

use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Provider;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckApproved
{
    use ApiResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && get_class(Auth::user()) == Provider::class && !Auth::user()->is_approved) {
            return $this->respondError('Your account is not approved yet', 401);
        }
        return $next($request);

    }
}
