<?php

namespace App\Http\Controllers;

use App\Models\Follower;
use Mild\Http\Request;
use Mild\Http\Response;
use Mild\Supports\Facades\View;

class FollowController
{
    /**
     * @param Request $request
     * @return mixed
     * @throws \ReflectionException
     */
    public function follow(Request $request)
    {
        if (is_null($user = session('user'))) {
            return response()->json([
                'success' => false
            ]);
        }
        if (is_null(Follower::where('follower_user_id', '=', $request->getParsedBodyParam('follower_id'))->where('following_user_id', '=', $user->id)->first())) {
            Follower::insert([
                'follower_user_id' => $request->getParsedBodyParam('follower_id'),
                'following_user_id' => $user->id
            ]);
        }
        return response()->json([
            'success' => true
        ]);
    }

    /**
     * @param Request $request
     */
    public function unfoll(Request $request)
    {
        Follower::where('follower_user_id', '=', $request->getParsedBodyParam('follower_id'))
            ->where('following_user_id', '=', $request->getParsedBodyParam('following_id'))
            ->delete();
    }
}