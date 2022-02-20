<?php

namespace App\Http\Middleware;

use App\Helpers\JwtAuth;
use Closure;
use Illuminate\Http\Request;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        //comprobar si el usuario está autenticado
        $token = $request->header('Authorization');
        $jwtAuth = new JwtAuth;
        $checkToken = $jwtAuth->checkToken($token);

        if ($checkToken){
            return $next($request);
        } else {
            //Mensaje de error
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Usuario no está identificado'
            );

            return response()->json($data, $data['code']);
        }
    }
}
