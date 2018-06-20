<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CommentRequest;
use App\Notifications\MessageNotification;
use App\Events\NotificationEvent;
use App\Message;
use Auth;
use App\User;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(CommentRequest $request)
    {
        $message = Message::create([
            'from_user_id' => Auth::id(),
            'to_user_id' => $request->get('to_user_id'),
            'content' => $request->get('content')
        ]);

        //通知
        $message->toUser->notify(new MessageNotification());
        //推送
        broadcast(new NotificationEvent($message->toUser))->toOthers();

        return redirect(url()->previous()."#current");
    }

    //私信
    public function show($id)
    {
        $messages = Message::where(function($q) use ($id) {
            $q->where(['from_user_id'=>$id,'to_user_id'=>Auth::id()]);
        })->orWhere(function($q) use ($id) {
            $q->where(['from_user_id'=>Auth::id(),'to_user_id'=>$id]);
        })->get();
            //->latest('created_at')->simplePaginate(10);

        //读取通知
        $a = Auth::user()->unreadNotifications;
        return view('message.show',compact('messages'),['to_user_id'=>$id]);
    }


}
