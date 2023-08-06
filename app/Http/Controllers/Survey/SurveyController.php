<?php

namespace App\Http\Controllers\Survey;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Question\QuestionController;
use App\Http\Controllers\SectionController;
use App\Models\Survey;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SurveyController extends Controller
{
    
    public function index($status)
    {
        if ($status == "archived") {
            $surveys = Survey::where('status', 'archived')->with('users')->get();
        }
        if ($status == "valid") {
            $surveys = Survey::where('status', 'valid')->with('users')->get();
        }
        $surveys->filter(function ($survey) {
            $survey->users->filter(function ($user) {
                $user->courses = json_decode($user->courses);
                $user->makeHidden('pivot');
                return $user;
            });
            return $survey;
        });
        return response()->json([
            'surveys' => $surveys
        ]);
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
            'sections' => 'required|array'
        ]);
        // return $request->sections[0]['questions'];
        try {
            $survey = Survey::create([
                'ar_name' => $request->ar_name,
                'en_name' => $request->en_name,
                'color' => $request->color,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'questions_count' => $request->questions_count,
                'notes' => $request->has('notes') ? $request->notes : null
            ]);
            $survey->users()->attach($request->volunteer);
            // the create question is in QuestionControler so i call it from instance with app
            $questionsController = app(QuestionController::class);
            $sectionController = app(SectionController::class);
            foreach ($request->sections as $section) {
                $section_id = $sectionController->store($survey, $section['section_name']);
                $questions = collect($section['questions']);
                $questions = $questions->map(function ($question) use ($survey, $section_id) {
                    $question = array_merge($question, ['survey_id' => $survey->id]);
                    $question = array_merge($question, ['section_id' => $section_id]);
                    return $question;
                });
                // return $questions;
                $questionsController->store($questions);
            }
            return response()->json([
                'message' => 'survey created successfully'
            ]);
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

    public function show(Survey $survey)
    {
        $sections = $survey->sections;
        $sections = $sections->map(function ($section) {
            $questions = $section->questions;
            foreach ($questions as $question) {
                $question->options = json_decode($question->options);
            }
            $section->setAttribute('questions', $questions);
            return $section;
        });
        $survey->setAttribute('sections', $sections);
        return response()->json([
            'survey' => $survey
        ]);
    }
    public function getAnswersUser(Survey $survey)
    {
        $users = $survey->users;
        $answers_users = [];
        foreach ($users as $user) {
            // return $user->isAnswer($survey);
            if ($user->isAnswer($survey)) {
                $answers_users[] = $user;
            }
        }
        return response()->json([
            'users' => $answers_users
        ]);
    }
}
