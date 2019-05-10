<?php

namespace Mild\Supports\Facades;

use App\Models\User;

class Auth
{
    protected static $user;

    /**
     * @return mixed
     * @throws \ReflectionException
     */
    public static function user()
    {
        if (is_null(static::$user)) {
            static::$user = User::where('id', '=', session('user'))->first();
        }
        return static::$user;
    }
}