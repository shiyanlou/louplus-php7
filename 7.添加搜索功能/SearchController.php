<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SearchRequest;
use App\Question;
use App\Answer;
use App\User;
use App\Label;


class SearchController extends Controller
{
    public function content(SearchRequest $request)
    {
        $k = $request->get('k');
        $answer = Answer::where('content','like',"%$k%")->with('question')->get();
        dd($answer->toArray());
    }

    public function user(SearchRequest $request)
    {
        $k = $request->get('k');
        $answer = User::where('name','like',"%$k%")->get();
        dd($answer->toArray());
    }

    public function label(SearchRequest $request)
    {
        $k = $request->get('k');
        $answer = Label::where('name','like',"%$k%")->get();
        dd($answer->toArray());
    }
}
