<?php

namespace App\Http\Controllers;

use App\Http\Requests\ModifyClass;
use App\Http\Requests\NewClass;
use App\Services\BasicService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ClassificationController extends Controller
{
    /**
     * To receive the request that make the new classification
     * @param NewClass $request
     * @return JsonResponse
     */
    public function newClassification(NewClass $request)
    {
//        $bs = new BasicService('gais_classification');
//        $cnt = $bs->pattern(["class_name" => $request->input('class_name', 'default2')])
//            ->count();
//        if ($cnt > 0) {
//            return response()->json("the data has already existed", 400);
//        }

        $now = Carbon::now();
        $persistent_id = Str::random(8).md5($request->input('class_name').$now->format('H:i:s').$now->format('Y-m-d'));
        $json_builder = [
            "class_name" => $request->input('class_name'),
            "persistent_id" => $persistent_id,
            "previous_name" => "",
            "description" => $request->input('description', ''),
            "keywords" => $request->input('keywords', ''),
            "parent_id" => $request->input('parent_id', '#'),
            "child_id" => $request->input('child_id', 0),
            "user_id" => $request->input('user_id', 1),
            "created_time" => $now->format('H:i:s'),
            "created_date" => $now->format('Y-m-d'),
            "updated_time" => $now->format('H:i:s'),
            "updated_date" => $now->format('Y-m-d')
        ];
        $insert = new BasicService('gais_classification');
        $result = $insert->save($json_builder);
//        dd($result);
        $new_rid = json_decode($result['data'], true);
        $result['data'] = array_merge($new_rid[0], $json_builder);
        return response()->json($result, 200);
    }

    public function modifyClassification(ModifyClass $request)
    {
        $update = new BasicService('gais_classification');
        $now = Carbon::now();
        $result = $update->save(["class_name" => $request->input('class_name'),
            "description" => $request->input('description', ''),
            "keywords" => $request->input('keywords', ''),
            "parent_id" => $request->input('parent_id'),
            "updated_time" => $now->format('H:i:s'),
            "updated_date" => $now->format('Y-m-d')], $request->input('rid'));

        return response()->json($result, 200);
    }

    public function deleteClassification(Request $request)
    {
        $delete_classification = new BasicService('gais_classification');
        $delete_classification
            ->pattern(['persistent_id' => $request->input('persistent_id')])
            ->delete();

        $delete_c_classification = new BasicService('gais_classification');
        $delete_c_classification
            ->pattern(['parent_id' => $request->input('persistent_id')])
            ->delete();

        $delete_word = new BasicService('gais_words');
        $result = $delete_word
            ->pattern(['class_id' => $request->input('persistent_id')])
            ->delete();

        return response()->json($result, 200);
    }

    public function getClassification($field_name, $rid = 0)
    {
        $get = new BasicService('gais_classification');
        if ($rid == 0) {
            $result = $get
                ->pageCount(500)
                ->all();
            $arr = json_decode($result['data'], true);
            $fmt_result = [];
            if ($arr != null && array_key_exists('recs', $arr)){
                foreach($arr['recs'] as $key => $value) {
                    $parent_id = $value['rec']['parent_id'];
                    array_push($fmt_result,
                        [
                            'id' => isset($value['rec']['persistent_id']) ? $value['rec']['persistent_id'] : '',
                            'parent' => $parent_id,
                            'text' => $value['rec']['class_name'],
                            'rid' => $value['rec']['_rid'],
                        ]);
                }
            }
            return response()->json($fmt_result, 200);
        } else {
            $result = $get->rid($rid)->get();
        }
        return response()->json($result, 200);
    }

    public function searchClassification($field_name, $query = '')
    {
        $get = new BasicService('gais_classification');
        $result = $get->pattern([$field_name => $query])->get();
        $arr = json_decode($result['data'], true);
        return response()->json($arr, 200);
    }


}
