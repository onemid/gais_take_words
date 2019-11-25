<?php

namespace App\Http\Controllers;

use App\Services\BasicService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ClassificationController extends Controller
{
    /**
     * To receive the request that make the new classification
     * @param Request $request
     * @return JsonResponse
     */
    public function newClassification(Request $request)
    {
        $bs = new BasicService('gais_classification');
        $cnt = $bs->pattern(["class_name" => $request->input('class_name', 'default2')])
            ->count();
        if ($cnt > 0) {
            return response()->json("the data has already existed", 400);
        }

        $now = Carbon::now();
        $json_builder = [
            "class_name" => $request->input('class_name', 'default2'),
            "previous_name" => "",
            "description" => $request->input('description', ''),
            "keywords" => $request->input('keywords', 'default2'),
            "parent_id" => $request->input('parent_id', 0),
            "child_id" => $request->input('child_id', 0),
            "user_id" => $request->input('user_id', 1),
            "created_time" => $now->format('H:i:s'),
            "created_date" => $now->format('Y-m-d'),
            "updated_time" => $now->format('H:i:s'),
            "updated_date" => $now->format('Y-m-d')
        ];
        $insert = new BasicService('gais_classification');
        $result = $insert->save($json_builder);
        return response()->json($result, 200);
    }

    public function modifyClassification(Request $request)
    {
        $update = new BasicService('gais_classification');
        $now = Carbon::now();
        $result = $update->save(["class_name" => $request->input('class_name'),
            "description" => $request->input('description'),
            "keywords" => $request->input('keywords'),
            "parent_id" => ($request->input('parent_id') == '#') ? 0 : $request->input('parent_id'),
            "updated_time" => $now->format('H:i:s'),
            "updated_date" => $now->format('Y-m-d')], $request->input('rid'));

        return response()->json($result, 200);
    }

    public function deleteClassification(Request $request)
    {
        $delete = new BasicService('gais_classification');
        $result = $delete
            ->delete($request->input('rid'));
        return response()->json($result, 200);
    }
}
