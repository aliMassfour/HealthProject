<?php

namespace App\Http\Controllers\Survey;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Question\QuestionController;
use App\Models\Survey;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SurveyController extends Controller
{
    public function store(Request $request)
    {
        // return $request->questions[1]['options'];
        $this->validate($request, [
            'ar_name' => 'required',
            'en_name' => 'required',
            'color'   => 'required',
            'volunteer' => 'required|array',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);
        try {
            $questions = collect($request->questions);
            $survey = Survey::create([
                'ar_name' => $request->ar_name,
                'en_name' => $request->en_name,
                'color' => $request->color,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'questions_count' => sizeOf($questions),
                'notes' => $request->has('notes') ? $request->notes : null
            ]);
            // the create question is in QuestionControler so i call it from instance with app
            $questionsController = app(QuestionController::class);
            $questions = $questions->map(function ($question) use ($survey) {
                return array_merge($question, ['survey_id' => $survey->id]);
            });
            $questionsController->store($questions);
        } catch (Exception $e) {
            return $e;
            return response()->json([
                'message' => json_decode($e->getMessage())
            ]);
        }
    }
}
