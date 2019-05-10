<?php

namespace App\Http\Middleware;

class AuthMiddleware
{
    /**
     * @param \Mild\Http\Request $request
     * @param \Mild\Http\Response $response
     * @return \Mild\Http\Response
     */
    public function __invoke($request, $response, $next)
    {
        if (!session('user')) {
            return redirect()->route('login')->with('warning', 'You must login before access this page.');
        }
        return $next($request, $response);
    }
}