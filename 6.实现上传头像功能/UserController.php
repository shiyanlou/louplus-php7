<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\User;
use Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show($id=null)
    {
        
        $id ?? $id = Auth::id();
        $user = User::find($id);

        return view('user.show',compact('user'));
    }

    //修改用户头像
    public function avatar(Request $request)
    {
        if ($filename = $request->get('filename')) {
            
            $user = Auth::user();
            $user->avatar = $request->get('filename');
            $user->save();
    
            return back();
        } 
        $file = $request->file('avatar');
        $filename = md5(time().Auth::id()).'.'.$file->getClientOriginalExtension();
        Storage::disk('image')->put('avatar/'.$filename,file_get_contents($file));

        return json_encode($filename);
    }

}
