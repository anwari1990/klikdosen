<?php

namespace App\Http\Middleware;

class AuthenticateMiddleware
{
    /**
     * @param \Mild\Http\Request $request
     * @param \Mild\Http\Response $response
     * @return \Mild\Http\Response
     */
    public function __invoke($request, $response, $next)
    {
        if (session('user')) {
            return redirect()->to(url('/'));
        }
        return $next($request, $response);
    }
}