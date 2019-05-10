<?php

namespace App\Http\Controllers;

use Flash;
use App\Models\User;
use App\Models\Paper;
use App\Models\Follower;
use Mild\Http\Request;
use Mild\Http\Response;
use Mail;
use Mild\Supports\Facades\View;

class DashboardController
{
    /**
     * @return string
     */
    public function index()
    {
        return View::render('dashboard/index', [
            'userTotal' => User::count(),
            'paperTotal' => Paper::count()
        ]);
    }

    public function users() 
    {
        return View::render('dashboard/user', [
            'users' => User::where('admin', '=', '0')->order('id', 'desc')->paginate()
        ]);
    }

    public function papers()
    {
        return View::render('dashboard/paper', [
            'papers' => Paper::order('id', 'desc')->paginate()
        ]);
    }

    public function usersEdit(Request $request, $id)
    {
        $v = validator($request->getParsedBody(), [
            'name' => 'required|max:15',
            'email' => 'required|email'
        ]);
        if (!$v->passes()) {
            return redirect()->back()->withError('Updated user failed.');
        }
        User::where('id', '=', $id)->update([
            'email' => $request->getParsedBodyParam('email'),
            'name' => $request->getParsedBodyParam('name'),
            'updated_at' => now()
        ]);
        return redirect()->back()->withSuccess('Updated user successfully.');
    }

    public function usersDelete($id)
    {
        User::where('id', '=', $id)->delete();
        Follower::where('follower_user_id', '=', $id)->orWhere('following_user_id', '=', $id)->delete();
        Flash::set('success', 'Delete user successfully.');
        return response()->json([
            'success' => true
        ]);
    }

    public function usersCreate(Request $request)
    {
        $v = validator($request->getParsedBody(), [
            'email' => 'required|email|unique:users',
            'name' => 'required'
        ]);
        if (!$v->passes()) {
            return redirect()->back()->withError('Created user failed.');
        }
        User::insert([
            'name' => ($name = $request->getParsedBodyParam('name')),
            'email' => ($email = $request->getParsedBodyParam('email')),
            'password' => encrypt($password = str_rand(8)),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        Mail::send(function ($message) use ($email, $name, $password) {
            $message->subject('Registration User')
            ->to($email, $name)
            ->body(View::render('mail/create', compact('email', 'name', 'password')));
        }); 
        return redirect()->back()->withSuccess('Created user successfully.');
    }

    public function usersAdmin($id)
    {
        if (is_null($user = User::where('id', '=', $id)->first())) {
            Flash::set('danger', 'User updated failed.');
            return response()->json([
                'success' => false
            ]);
        }
        $user->admin = '1';
        $user->save();
        Flash::set('success', 'User updated successfully.');
        return response()->json([
            'success' => true
        ]);
    }
}