<?php

namespace App\Http\Controllers;

use App\Models\User;
use Mild\Http\Request;
use Mild\Http\Response;
use App\Models\Message;
use Mild\Supports\Facades\View;

class MessageController
{
    /**
     * @return string
     */
    public function index()
    {
        return View::render('message');
    }

    /**
     * @param Request $request
     */
    public function searchUser(Request $request)
    {
        return response()->json(User::order('id', 'desc')
                                    ->where('id', '!=', session('user')->id)
                                    ->where('name', 'like', '%'.$request->getParsedBodyParam('q').'%')
                                    ->limit(10)
                                    ->get()
                                );
    }

    public function show($recipient)
    {
        return response()->json([
            'recipient' => $recipient,
            'messages' => Message::where('sender', '=', session('user')->id)->where('recipient', '=', $recipient)
            ->orWhere('sender', '=', $recipient)
            ->where('recipient', '=', session('user')->id)->get()
        ]);
    }

    public function send($recipient, Request $request)
    {
        if (!empty($message = trim($request->getParsedBodyParam('message')))) {
            Message::insert([
                'sender' => session('user')->id,
                'recipient' => $recipient,
                'context' => $message,
                'created_at' => now()
            ]);
        }   
        return $this->show($recipient);
    }
}