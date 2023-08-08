<?php

namespace App\Http\Controllers\Survey;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Question\QuestionController;
use App\Http\Controllers\SectionController;
use App\Models\MainTitle;
use App\Models\Survey;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use PhpParser\Node\Expr\Cast\Object_;

class SurveyController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }
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

    /**
     * Store a new survey.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     *
     * @OA\Post(
     *     path="/survey/store",
     *     summary="Store a new survey",
     *     description="Please ensure to send the questions in the same order as they were created.options and main_title and sub_title must be null if the question donst have it ",
     *     tags={"surveys"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="ar_name", type="string"),
     *                 @OA\Property(property="en_name", type="string"),
     *                 @OA\Property(property="color", type="string"),
     *                 @OA\Property(property="volunteer", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="start_date", type="string", format="date"),
     *                 @OA\Property(property="end_date", type="string", format="date"),
     *                 @OA\Property(property="questions", type="array", @OA\Items(
     *                     @OA\Property(property="content", type="string"),
     *                     @OA\Property(property="type", type="string"),
     *                     @OA\Property(
     *                         property="options",
     *                         oneOf={
     *                             @OA\Schema(type="array", @OA\Items(type="string")),
     *                             @OA\Schema(type="null")
     *                         }
     *                     ),
     *                     @OA\Property(property="required", type="boolean"),
     *                     @OA\Property(
     *                         property="main_title",
     *                         oneOf={
     *                             @OA\Schema(type="string"),
     *                             @OA\Schema(type="null")
     *                         }
     *                     ),
     *                     @OA\Property(
     *                         property="sub_title",
     *                         oneOf={
     *                             @OA\Schema(type="string"),
     *                             @OA\Schema(type="null")
     *                         }
     *                     )
     *                 )),
     *                 @OA\Property(property="questions_count", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Survey created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Survey is created successfully")
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function store(Request $request)
    {
        // return $request->questions[1]['options'];

        // return $request->sections[0]['questions'];
        try {
            $this->validate($request, [
                'ar_name' => 'required',
                'en_name' => 'required',
                'color'   => 'required',
                'volunteer' => 'required|array',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'questions' => 'required|array',
                'questions_count' => 'required'

            ]);
            // return $request->all();
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
            $questions = $request->questions;
            $main_titles = [];
            $sub_titles = [];
            foreach ($questions as &$question) {
                if ($question['main_title'] != null && !in_array($question['main_title'], $main_titles)) {
                    $main_title = $survey->MainTitles()->create([
                        'name' => $question['main_title']
                    ]);
                    $main_titles[] = $question['main_title'];
                    $question['main_title'] = $main_title->id;
                }
                if ($question['sub_title'] != null && !in_array($question['sub_title'], $sub_titles)) {
                    $sub_title = $main_title->SubTitles()->create([
                        'name' => $question['sub_title']
                    ]);
                    $sub_titles[] = $question['sub_title'];
                    $question['sub_title'] = $sub_title->id;
                    $question['main_title'] = $main_title->id;
                }
                $question['survey_id'] = $survey->id;
            }
            $questionsController->store($questions);
            return response()->json([
                'message' => 'survey is created successfully '
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    /**
 * @OA\Post(
 *     path="/survey/duplicate/{survey}",
 *     tags={"surveys"},
 *     summary="Duplicate a survey",
 *     description="Creates a duplicate of the specified survey, including its questions, main titles, and subtitles.",
 *     @OA\Parameter(
 *         name="survey",
 *         in="path",
 *         description="The ID of the survey to duplicate",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success message",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 description="The success message indicating that the survey was duplicated successfully."
 *             )
 *         )
 *     )
 * )
 */
    public function duplicate(Survey $survey)
    {
        $questions = $survey->questions()->with(['MainTitle', 'SubTitle'])->get();
        $questions_count = $questions->count();
        $new_survey = Survey::create([
            'questions_count' => $questions_count
        ]);
        // we need to create the survey information from questions and main titles and subtitles 
        // we know that every questions maybe belongs to sub title and main title 
        // and also many questions maybe have the same main_title or the same subtitle 
        // by the main title or the sub title must created once so for that i use this arrays to 
        // store the main an sub titles that i created  
        $main_titles = [];
        $sub_titles = [];
        $main_title = null;
        $sub_title = null;
        $new_questions = [];
        foreach ($questions as $question) {
            $new_question = [
                'content' => $question->content,
                'survey_id' => $new_survey->id,
                'type' => $question->type,
                'required' => $question->required
            ];
            if ($question->options !== null)
                $new_question['options'] = json_decode($question->options);
            if ($question->main_title !== null) {
                if (!in_array($question->MainTitle->name, $main_titles)) {
                    $main_title = $new_survey->MainTitles()->create([
                        'name' => $question->MainTitle->name
                    ]);
                    $main_titles[] = $question->MainTitle->name;
                }
                $new_question['main_title'] = $main_title->id;
            } else {
                $new_question['main_title'] = null;
            }

            if ($question->sub_title !== null) {
                if (!in_array($question->SubTitle->name, $sub_titles)) {
                    $sub_title = $main_title->SubTitles()->create([
                        'name' => $question->SubTitle->name
                    ]);
                    $sub_titles[] = $question->SubTitle->name;
                }
                $new_question['sub_title'] = $sub_title->id;
            } else {
                $new_question['sub_title'] = null;
            }
            $new_questions[] = $new_question;
        }
        $QuestionController = app(QuestionController::class);
        $QuestionController->store($new_questions);
        return response()->json([
            'message' => 'survey is duplicated successfully'
        ]);
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
    /**
     * Retrieve a survey along with its questions.
     *
     * @param  \App\Models\Survey  $survey
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/survey/show/{survey}",
     *     summary="Retrieve a survey",
     *     description="Retrieves a survey along with its questions.",
     *     tags={"surveys"},
     *     @OA\Parameter(
     *         name="survey",
     *         in="path",
     *         description="ID of the survey",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Survey retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="questions", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="content", type="string"),
     *                 @OA\Property(property="type", type="string"),
     *                 @OA\Property(property="options", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="required", type="boolean"),
     *                 @OA\Property(property="main_title_name", type="string"),
     *                 @OA\Property(property="sub_title_name", type="string")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Survey not found"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

    public function show(Survey $survey)
    {
        $questions = $survey->getAllQuestions();
        $filter_questions =  $questions->filter(function ($question) {
            $question->main_title !== null ? $question->setAttribute('main_title_name', $question->MainTitle->name) :  $question->setAttribute('main_title_name', null);
            $question->sub_title !== null ? $question->setAttribute('sub_title_name', $question->SubTitle->name) : $question->setAttribute('sub_title_name', null);
            $question->makeHidden('main_title');
            return $question;
        });
        return response()->json([
            'questions' => $filter_questions
        ]);
    }
    /**
     * @OA\Get(
     *     path="/users/isanswer/{survey}",
     *     summary="Get users who have answered a survey",
     *     tags={"surveys"},
     *     @OA\Parameter(
     *         name="survey",
     *         in="path",
     *         description="ID of the survey",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="users",
     *                 type="array",
     *                 @OA\Items(
     *                     
     *                 )
     *             )
     *         )
     *     ),
     * security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
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
