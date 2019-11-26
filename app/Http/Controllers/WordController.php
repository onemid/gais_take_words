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

    public function deleteWord(Request $request)
    {
        $delete = new BasicService('gais_words');
        $result = $delete
            ->delete($request->input('rid'));
        return response()->json($result, 200);
    }

    public function getWord($field_name, $rid = 0)
    {
        $get = new BasicService('gais_words');
        if ($rid == 0) {
            $result = $get
                ->pageCount(50)
                ->all();
        } else {
            $result = $get->rid($rid)->get();
        }
        $arr = json_decode($result['data'], true);
        return response()->json($arr, 200);
    }

    public function getWordByClass($class_id, $sub_class_id_mode = false)
    {
        // strategy: (1) fetch the class_id (persistent_id) and its child_id,
        $get = new BasicService('gais_words');
        if ($rid == 0) {
            $result = $get
                ->pageCount(50)
                ->all();
        } else {
            $result = $get->rid($rid)->get();
        }
        $arr = json_decode($result['data'], true);
        return response()->json($arr, 200);
    }

    public function searchWord($field_name, $query = '')
    {
        $get = new BasicService('gais_words');
        $result = $get->pattern([$field_name => $query])->get();
        $arr = json_decode($result['data'], true);
        return response()->json($arr, 200);
    }
}
