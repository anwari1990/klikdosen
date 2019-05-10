<?php

namespace App\Http\Controllers;

use App\Models\Paper;
use App\Models\User;
use Mild\Http\Request;
use Mild\Http\Response;
use Mild\Supports\Facades\View;

class WelcomeController
{
    /**
     * @return mixed
     */
    public function index()
    {
        return View::render('welcome', [
            'papers' => Paper::order('id', 'desc')->paginate(),
            'users' => User::order('rand()')->limit(5)->get()
        ]);
    }

    /**
     * @param $file
     * @return mixed
     * @throws \ReflectionException
     */
    public function download($file)
    {
        if (!file_exists($file = path('public/papers/'.$file))) {
            abort(404);
        }
        return response()->withHeader('Content-Type', mime_content_type($file))
            ->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Content-Disposition', 'attachment; filename="' . basename($file) . '"')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->withHeader('Pragma', 'public')
            ->withBody(new \Mild\Http\Stream(fopen($file, 'rb'))); // all stream contents will be sent to the response
    }
}