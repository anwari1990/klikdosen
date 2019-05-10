<?php

namespace App\Http\Controllers;

use App\Models\User;
use Mild\Http\Request;
use Mild\Http\Response;
use Mild\Supports\Facades\Mail;
use Mild\Supports\Facades\View;

class AuthController
{
    /**
     * @return string
     */
    public function login()
    {
        return View::render('auth/login');
    }

    /**
     * @return Response
     */
    public function loginAction(Request $request)
    {
        validate($request->getParsedBody(), [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);
        if (is_null($user = User::where('email', '=', $request->getParsedBodyParam('email'))->first())) {
            return redirect()->back()->withInputs()->with('error', 'The credential is not exists.');
        }
        if ($request->getParsedBodyParam('password') !== decrypt($user->password)) {
            return redirect()->back()->withInputs()->with('error', 'The password does not match.');
        }
        return tap(redirect()->to(url('/')), function ($response) use ($user) {
            $response->getFlash()->getSession()->set('user', $user);
        });
    }

    /**
     * @return string
     */
    public function register()
    {
        return View::render('auth/register');
    }

    /**
     * @return Response
     */
    public function registerAction(Request $request)
    {
        validate($request->getParsedBody(), [
            'name' => 'required|max:15',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);
        User::insert([
            'name' => $request->getParsedBodyParam('name'),
            'email' => $request->getParsedBodyParam('email'),
            'password' => encrypt($request->getParsedBodyParam('password')),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        return redirect()->route('login')->withInputs()->with('success', 'Register success, you can login now.');
    }

    /**
     * @return Response
     */
    public function logout()
    {
        return tap(redirect()->back(), function ($response) {
            $response->getFlash()->getSession()->put('user');
        });
    }

    /**
     * @return string
     */
    public function forgot()
    {
        return View::render('auth/forgot');
    }

    /**
     * @return Response
     */
    public function forgotAction(Request $request)
    {
        validate(['email' => ($email = $request->getParsedBodyParam('email'))], [
            'email' => 'required|email'
        ]);
        if (is_null($user = User::where('email', '=', $email)->first())) {
            return redirect()->back()->withInputs()->with('error', 'The credential does not exists.');
        }
        $user->forgot_token = str_rand(32);
        $user->save();
        Mail::send(function ($message) use ($user) {
            $message->from('security@klikdosen.com', 'Klikdosen')
            ->to($user->email, $user->name)
            ->subject('Password Reset')
            ->body(View::render('mail/forgot', ['user' => $user]));
        });
        return redirect()->back()->withInputs()->with('success', 'Check your mail inbox.');
    }

    /**
     * @return string
     */
    public function recovery(Request $request)
    {
        if (is_null($token = $request->getQueryParam('token'))) {
            abort(404);
        }
        if (is_null($user = User::where('forgot_token', '=', $token)->first())) {
            abort(404);
        }
        return View::render('auth/recovery');
    }

    /**
     * @return Response
     */
    public function recoveryAction(Request $request)
    {
        if (is_null($token = $request->getQueryParam('token'))) {
            abort(404);
        }
        if (is_null($user = User::where('forgot_token', '=', $token)->first())) {
            abort(404);
        }
        validate($request->getParsedBody(), [
            'password' => 'required|confirmed'
        ]);
        $user->forgot_token = null;
        $user->password = encrypt($request->getParsedBodyParam('password'));
        $user->save();
        return redirect()->route('login')->withInputs()->with('success', 'The password has been reset.');
    }
}