<?php

namespace App\Http\Controllers\Answer;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnswerController extends Controller
{
    public function store(Request $request , Survey $survey)
    {
        // return $request->all();
        $answers = $request->answers;
        //this loop is validate each answer in the array
        foreach ($answers as $answer) {
            $validator = Validator::make($answer, [
                'question_id' => 'required|integer',
                'value' => 'required|string',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->getMessageBag()
                ], 422);
            }
        }
        try {
            $entry = $survey->entries()->create([
                'participant_id' => auth()->user()->id
            ]);
        } catch (Exception $e) {
            return response()->json([
                'sorry error occured please try again'
            ], 500);
        }
        try {
            $entry->answers()->createMany($answers);
        } catch (Exception $e) {
            $entry->delete();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
        return response()->json([
            'message' => 'create the answers success'
        ], 200);
        
    }
        
}
