<?php

namespace Haridarshan\Laravel\NwidartModules\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class ApiVersion
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param $guard
     *
     * @return Response|JsonResponse|RedirectResponse
     * @throws Throwable
     */
    public function handle(Request $request, Closure $next, $guard = null): Response|JsonResponse|RedirectResponse
    {
        if (!is_null($guard)) {
            config(['api.version' => $guard]);

            $path = module_path($guard);
            if (file_exists("$path/bootstrap.php")) {
                require_once "$path/bootstrap.php";
            }
        }

        return $next($request);
    }
}
