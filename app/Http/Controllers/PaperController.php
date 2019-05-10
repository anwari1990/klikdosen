<?php

namespace App\Http\Controllers;

use Flash;
use Mail;
use DB;
use App\Models\User;
use App\Models\Paper;
use Mild\Http\Request;
use Mild\Http\Response;
use Mild\Supports\Facades\View;

class PaperController
{
    /**
     * @return string
     */
    public function create()
    {
        return View::render('upload');
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \ReflectionException
     */
    public function store(Request $request)
    {
        if (!validator($request->getParsedBody() + $request->getUploadedFiles(), [
            'title' => 'required|min:5',
            'description' => 'required|min:20',
            'file' => 'mimes: pdf,odt,ppt,docx'
        ])->getMessageBag()->isEmpty()) {
            return redirect()->back()->withInputs()->withError('Cannot upload your paper.');
        }
        $file = $request->getUploadedFile('paper');
        Paper::insert([
            'title' => $request->getParsedBodyParam('title'),
            'user_id' => session('user')->id,
            'description' => $request->getParsedBodyParam('description'),
            'file' => ($name = uniqid().'.'.$file->getExtension()),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $file->moveTo(path('public/papers/'.$name));
        Mail::send(function ($message) use ($request) {
            foreach (User::get() as $user) {
                $message->to($user->email, $user->name);
            }
            $message->subject('New Paper To Read')
            ->body(View::render('mail/suscribe', [
                'title' => $request->getParsedBodyParam('title'),
                'link' => route('paper.show', [DB::lastInsertId()]),
            ]));
        });
        return redirect()->back()->withSuccess('Paper created successfully.');
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        if (is_null($paper = Paper::where('id', '=', $id)->first())) {
            abort(404);
        }
        $paper->views += 1;
        $paper->save();
        $papers = Paper::where('id', '!=', $paper->id)->order('rand()')->limit(5)->get();
        return View::render('paper', compact('paper', 'papers'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     * @throws \Mild\Validation\ValidationException
     * @throws \ReflectionException
     */
    public function update($id, Request $request)
    {
        if (is_null($paper = Paper::where('id', '=', $id)->first())) {
            abort(404);
        }
        validate($request->getParsedBody(), [
            'title' => 'required|min:5',
            'description' => 'required|min:20'
        ]);
        if (session('user')->id !== $paper->user->id) {
            return redirect()->back()->withError('Couldnt update the paper.');
        }
        $paper->title = $request->getParsedBodyParam('title');
        $paper->description = $request->getParsedBodyParam('description');
        $paper->save();
        return redirect()->back()->withSuccess('Paper updated successfully.');
    }

    /**
     * @param string $id
     * @return string
     */
    public function delete($id)
    {
        $paper = Paper::where('id', '=', $id)->first();
        if ($paper && session('user')) {
            if (session('user')->id === $paper->user_id || session('user')->isAdmin()) {
                Paper::where('id', '=', $id)->delete();
                Flash::set('warning', 'Paper deleted successfully.');
                return response()->json([
                    'success' => true
                ]);
            }
        }
        return response()->json([
            'success' => false
        ]);
    }
}