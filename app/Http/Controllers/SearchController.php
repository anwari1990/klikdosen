<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Paper;
use Mild\Http\Request;
use Mild\Http\Response;
use Mild\Supports\Facades\View;

class SearchController
{
    /**
     * @param Request $request
     * @return string
     */
    public function index(Request $request)
    {
        $q = $request->getQueryParam('q');
        $users = User::order('id', 'desc');
        if (!is_null($user = session('user'))) {
            $users = $users->where('id', '!=', $user->id);
        }
        $users = $users->where('name', 'like', '%'.$q.'%')->Where('email', 'like', '%'.$q.'%')->paginate(15, '*', 'user');
        $papers = Paper::order('id', 'desc')->where('title', 'like', '%'.$q.'%')->paginate(15, '*', 'paper');
        $userSidebar = \App\Models\User::order('rand()')->limit(5)->get();
        return View::render('search', compact('q', 'users', 'papers', 'userSidebar'));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function api(Request $request)
    {
        $q = $request->getQueryParam('q');
        return response()->json(Paper::order('id', 'desc')->where('title', 'like', '%'.$q.'%')->limit(10)->get(['id', 'title']));
    }
}