<?php

namespace App\Http\Controllers;

use Flash;
use App\Models\User;
use App\Models\Paper;
use Mild\Http\Request;
use Mild\Http\Response;
use App\Models\Follower;
use Mild\Supports\Facades\View;

class UserController
{
    /**
     * @param $id
     * @return mixed
     * @throws \ReflectionException
     */
    public function show($id)
    {
        if (is_null($user = User::where('id', '=', $id)->first())) {
            abort(404);
        }
        $user->isCurrentUser = false;
        $followingCurrentUser = false;
        if (!is_null($currentUser = session('user'))) {
            if ($currentUser->id === $user->id) {
                $user->isCurrentUser = true;
            } 
            if (!is_null(Follower::where('following_user_id', '=', $currentUser->id)->where('follower_user_id', '=', $id)->first())) {
                $followingCurrentUser = true;
            }
        }
        $follower = Follower::where('follower_user_id', '=', $user->id)->count();
        $following = Follower::where('following_user_id', '=',$user->id)->count();
        $followers = [];
        $followings = [];
        foreach (Follower::order('id', 'desc')->where('follower_user_id', '=', $user->id)->get() as $u) {
            $followers[] = User::where('id', '=', $u->following_user_id)->first();
        }
        foreach (Follower::order('id', 'desc')->where('following_user_id', '=', $user->id)->get() as $u) {
            $followings[] = User::where('id', '=', $u->follower_user_id)->first();
        }
        $followers = array_filter($followers);
        $followings = array_filter($followings);
        $papers = Paper::order('id', 'desc')->where('user_id', '=', $user->id)->paginate();
        return View::render('user', compact('user', 'follower', 'following', 'followingCurrentUser', 'followers', 'followings', 'papers'));
    }

    /**
     * @param $id
     * @param Request $request
     * @throws \Mild\Validation\ValidationException
     * @throws \ReflectionException
     */
    public function edit($id, Request $request)
    {
        $rulePass = '';
        if ($request->getParsedBodyParam('password')) {
            $rulePass = 'min:6|';
            $updates = 
            [
                'updated_at' => now(),
                'name' => $request->getParsedBodyParam('name'),
                'phone' => $request->getParsedBodyParam('phone'),
                'departement' => $request->getParsedBodyParam('departement'),
                'university' => $request->getParsedBodyParam('university'),
                'password' => encrypt($request->getParsedBodyParam('password'))
            ];
        } else {
            $updates = [
                'updated_at' => now(),
                'name' => $request->getParsedBodyParam('name'),
                'phone' => $request->getParsedBodyParam('phone'),
                'departement' => $request->getParsedBodyParam('departement'),
                'university' => $request->getParsedBodyParam('university')
            ];
        }
        validate($request->getParsedBody(), [
            'name' => 'required|max:15',
            'password' => $rulePass.'confirmed',
            'departement' => 'required',
            'university' => 'required',
            'phone' => 'required|numeric'
        ]);
        User::where('id', '=', $id)->update($updates);
    }

    /**
     * @param $id
     * @param Request $request
     * @throws \ReflectionException
     */
    public function editPicture($id, Request $request) 
    {
        $file = $request->getUploadedFile('picture');
        $name = str_rand().'.'.$file->getExtension();
        \App\Models\User::where('id', '=', $id)->update([
            'picture' => $name
        ]);
        $file->moveTo(path('public/images/'.$name));
        Flash::set('success', 'Picture has been updated.');
    }
}