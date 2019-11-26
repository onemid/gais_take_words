<?php

namespace App\Http\Controllers;

use App\Http\Requests\ModifyWord;
use App\Http\Requests\NewClass;
use App\Http\Requests\NewWord;
use App\Services\BasicService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WordController extends Controller
{
    public function newWord(NewWord $request)
    {
        // check the word existence
        $bs = new BasicService('gais_words');
        $cnt = $bs->pattern(["word" => $request->input('word')])
            ->count();
        if ($cnt > 0) {
            return response()->json("the data has already existed", 400);
        }

        // check the class_id existence
        $bs = new BasicService('gais_classification');
        $cnt = $bs->pattern(["persistent_id" => $request->input('class_id')])
            ->count();
        if ($cnt <= 0) {
            return response()->json("the class does not existed", 400);
        }

        $now = Carbon::now();
        $persistent_id = Str::random(8).md5($request->input('word').$now->format('H:i:s').$now->format('Y-m-d'));
        $json_builder = [
            "word" => $request->input('word'),
            "persistent_id" => $persistent_id,
            "class_id" => $request->input('class_id'),
            "user_id" => $request->input('user_id', 1),
            "created_time" => $now->format('H:i:s'),
            "created_date" => $now->format('Y-m-d'),
            "updated_time" => $now->format('H:i:s'),
            "updated_date" => $now->format('Y-m-d')
        ];
        $insert = new BasicService('gais_words');
        $result = $insert->save($json_builder);
        $new_rid = json_decode($result['data'], true);
        $result['data'] = array_merge($new_rid[0], $json_builder);
        return response()->json($result, 200);
    }

    public function modifyWord(ModifyWord $request)
    {
        $update = new BasicService('gais_words');
        $now = Carbon::now();
        $result = $update->save(["word" => $request->input('word'),
            "class_id" => $request->input('class_id'),
            "updated_time" => $now->format('H:i:s'),
            "updated_date" => $now->format('Y-m-d')], $request->input('rid'));

        return response()->json($result, 200);
    }
}
