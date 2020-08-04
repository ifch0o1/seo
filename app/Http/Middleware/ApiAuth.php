<?php

namespace App\Http\Middleware;

use App\AuthToken;
use Closure;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->input('api_token');
        if (!$token) {
            /** Return 1 */
            return abort('401', 'No token provided.');
        } else {
            $found = AuthToken::where('token', $token)->where('type', 'api_auth')->first();
            if ($found && $found->active == 1) {
                $request->route()->setParameter('client_id', $found->client_id);
                return $next($request);
            } else {
                /** Return 2 */
                return abort('403', 'This token is banned.');
            }
        }
    }
}
