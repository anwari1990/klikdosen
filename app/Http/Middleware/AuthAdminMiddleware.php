<?php

namespace App\Http\Middleware;

use App\Models\User;
use Mild\Supports\Facades\View;

class AuthAdminMiddleware
{
    /**
     * @param \Mild\Http\Request $request
     * @param \Mild\Http\Response $response
     * @return \Mild\Http\Response
     */
    public function __invoke($request, $response, $next)
    {
        if (!session('user')->isAdmin()) {
            return redirect()->back()->withError('You not allowed to access this page');
        }
        View::share('user', User::where('id', '=', session('user')->id)->first());
        return $next($request, $response);
    }
}