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
    public function index()
    {
        $surveys = Survey::with('users')->get();
        $surveys->filter(function ($survey) {
            $survey->users->filter(function ($user) {
                $user->courses = json_decode($user->courses);
                $user->makeHidden('pivot');
                return $user;
            });
            return $survey;
        });
        return $surveys;
    }
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
            $survey->users()->attach($request->volunteer);
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
    public function archive(Survey $survey)
    {
        $survey->status = 'archived';
        if ($survey->save()) {
            return response()->json([
                'message' => 'survey archived successfully'
            ]);
        } else {
            return response()->json([
                'message' => 'error occoured please try agian'
            ], 500);
        }
    }
    public function getArchive()
    {
        $surveys = Survey::where('status', 'archived')->with('users')->get();
        $surveys->filter(function ($survey) {
            $survey->users->filter(function ($user) {
                $user->courses = json_decode($user->courses);
                $user->makeHidden('pivot');
                return $user;
            });
            return $survey;
        });
        return response()->json([
            'archived_survey' => $surveys
        ]);
    }
    public function getValid()
    {
        $surveys = Survey::where('status', 'valid')->with('users')->get();
        $surveys->filter(function ($survey) {
            $survey->users->filter(function ($user) {
                $user->courses = json_decode($user->courses);
                $user->makeHidden('pivot');
                return $user;
            });
            return $survey;
        });
        return response()->json([
            'valid_survey' => $surveys
        ]);
    }
}
