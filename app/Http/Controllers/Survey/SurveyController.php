<?php

namespace App\Http\Controllers\Survey;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Question\QuestionController;
use App\Http\Controllers\SectionController;
use App\Models\MainTitle;
use App\Models\Question;
use App\Models\Survey;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use PhpParser\Node\Expr\Cast\Object_;

class SurveyController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }
    /**
     * Retrieve surveys based on their status.
     *
     * @param string $status The status of the surveys ("archived" or "valid").
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/survey/index/{status}",
     *     operationId="getSurveysByStatus",
     *     summary="Get surveys by status",
     *     tags={"surveys"},
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="Status of the surveys",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"archived", "valid"},
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="surveys",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="ar_name", type="string", nullable=true),
     *                     @OA\Property(property="en_name", type="string", nullable=true),
     *                     @OA\Property(property="color", type="string", nullable=true),
     *                     @OA\Property(property="start_date", type="string", format="date", nullable=true),
     *                     @OA\Property(property="end_date", type="string", format="date-time", nullable=true),
     *                     @OA\Property(property="questions_count", type="integer"),
     *                     @OA\Property(property="notes", type="string", nullable=true),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(
     *                         property="users",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="username", type="string"),
     *                             @OA\Property(property="name", type="string"),
     *                             @OA\Property(property="phone", type="string"),
     *                             @OA\Property(property="directorate_id", type="integer"),
     *                             @OA\Property(property="city_id", type="integer"),
     *                             @OA\Property(property="flag", type="string"),
     *                             @OA\Property(property="certificate", type="string", nullable=true),
     *                             @OA\Property(property="gender", type="string", enum={"male", "female"}),
     *                             @OA\Property(property="courses", type="array", @OA\Items(type="string"), nullable=true),
     *                         ),
     *                     ),
     *                     @OA\Property(property="total_days", type="integer"),
     *                     @OA\Property(property="remaining_days", type="integer"),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * 
     * )
     */
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
                $user->makeHidden(['pivot', 'created_at', 'updated_at', 'role_id']);
                return $user;
            });
            $end = Carbon::parse($survey->ende_date);
            $start = CArbon::parse($survey->satr_date);
            $total_days = $end->diff($start);
            $remaining_days = $end->diff(now());

            $survey->setAttribute('total_days', $total_days->days);
            $survey->setAttribute('remaining_days', $remaining_days->days);
            $survey->makeHidden(['created_at', 'updated_at']);
            return $survey;
        });
        return response()->json([
            'surveys' => $surveys
        ]);
    }
    /**
     * @OA\Post(
     *     path="/survey/store",
     *     summary="Store a new survey",
     *     description="Please ensure to send the questions in the same order as they were created. Options, main_title, and sub_title must be null if the question doesn't have them.",
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
     *                       @OA\Property(property="en_content", type="string"),
     *                       @OA\Property(property="length", type="integer"),
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
     *                             @OA\Schema(type="integer"),
     *                             @OA\Schema(type="null")
     *                         }
     *                     ),
     *                     @OA\Property(
     *                         property="sub_title",
     *                         oneOf={
     *                             @OA\Schema(type="integer"),
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

            $main_titles = [];
            $data_questions = [];
            $question_controller = app(QuestionController::class);

            foreach ($request->questions as $question) {
                if (!in_array($question['main_title'], $main_titles) && $question['main_title'] !== null) {
                    $main_titles[] = $question['main_title'];
                }
                $data_question = [
                    'content' => $question['content'],
                    'type' => $question['type'],
                    'survey_id' => $survey->id,
                    'required' => $question['required'] == true ? "1" : "0",
                    'main_title' => $question['main_title'],
                    'sub_title' => $question['sub_title'],
                    'en_content' => $question['en_content'] ,
                    'length' => $question['length']
                ];
                if (array_key_exists('options', $question)) {
                    $data_question['options'] = $question['options'];
                }
                $data_questions[] = $data_question;
            }

            $question_controller->store($data_questions);
            $survey->MainTitles()->attach($main_titles);

            return response()->json([
                'message' => 'Survey created successfully'
            ]);
        } catch (Exception $e) {
            return $e;
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
     *     ),
     *  security={{"bearerAuth": {}}}
     * )
     */
    public function duplicate(Survey $survey)
    {
        $questions = $survey->questions()->with(['MainTitle', 'SubTitle'])->get();
        $questions_count = $questions->count();
        $new_survey = Survey::create([
            'questions_count' => $questions_count
        ]);

        foreach ($questions as $question) {
            $question->survey_id = $survey->id;
            Question::query()->create($question);
        }
        
        return response()->json([
            'duplicated the survey successfully'
        ]);
    }
    /**
     * @OA\Put(
     *     path="/survey/archive/{survey}",
     *     summary="Archive a survey",
     *     description="Archive a survey by setting its status to 'archived'.",
     *     tags={"surveys"},
     *     @OA\Parameter(
     *         name="survey",
     *         in="path",
     *         description="ID of the survey to be archived",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Survey archived successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="Success message"
     *             )
     *         )
     *     ),
     *    security={{"bearerAuth": {}}}
     * )
     */
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
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

    public function show(Survey $survey)
    {
        $questions = $survey->getAllQuestions();
        $filter_questions =  $questions->filter(function ($question) {
            $question->main_title !== null ? $question->setAttribute('main_title', $question->MainTitle) :  $question->setAttribute('main_title_name', null);
            $question->sub_title !== null ? $question->setAttribute('sub_title', $question->SubTitle) : $question->setAttribute('sub_title_name', null);
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
     *                     type="object",
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="role_id",
     *                         type="integer",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="username",
     *                         type="string",
     *                         example="admin"
     *                     ),
     *                     @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="admin"
     *                     ),
     *                     @OA\Property(
     *                         property="phone",
     *                         type="string",
     *                         example="12345678"
     *                     ),
     *                     @OA\Property(
     *                         property="directorate_id",
     *                         type="integer",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="city_id",
     *                         type="integer",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="flag",
     *                         type="string",
     *                         example="0"
     *                     ),
     *                     @OA\Property(
     *                         property="certificate",
     *                         type="array",
     *                         @OA\Items(
     *                             type="string"
     *                         ),
     *                         example={"certificate1", "certificate2"},
     *                         nullable=true
     *                     ),
     *                     @OA\Property(
     *                         property="gender",
     *                         type="string",
     *                         example="male"
     *                     ),
     *                     @OA\Property(
     *                         property="courses",
     *                         type="array",
     *                         @OA\Items(
     *                             type="string"
     *                         ),
     *                         example={"course1", "course2"},
     *                         nullable=true
     *                     ),
     *                     @OA\Property(
     *                         property="city_name",
     *                         type="string",
     *                         example="city one"
     *                     ),
     *                     @OA\Property(
     *                         property="directorate_name",
     *                         type="string",
     *                         example="directorate one"
     *                     ),
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
    /**
     * @OA\Put(
     *     path="/survey/update/{survey}",
     *     tags={"surveys"},
     *     summary="Update a survey",
     *     description="Updates a survey with the specified data",
     *     @OA\Parameter(
     *         name="survey",
     *         in="path",
     *         description="ID of the survey to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/formdata",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="ar_name",
     *                     type="string",
     *                     description="Arabic name of the survey"
     *                 ),
     *                 @OA\Property(
     *                     property="en_name",
     *                     type="string",
     *                     description="English name of the survey"
     *                 ),
     *                 @OA\Property(
     *                     property="color",
     *                     type="string",
     *                     description="Color of the survey"
     *                 ),
     *                 @OA\Property(
     *                     property="start_date",
     *                     type="string",
     *                     format="date",
     *                     description="Start date of the survey (YYYY-MM-DD)"
     *                 ),
     *                 @OA\Property(
     *                     property="end_date",
     *                     type="string",
     *                     format="date",
     *                     description="End date of the survey (YYYY-MM-DD)"
     *                 ),
     *                 @OA\Property(
     *                     property="notes",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string"
     *                     ),
     *                     description="Array of notes for the survey"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="Success message"
     *             )
     *         )
     *     ),
     * security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

    public function update(Request $request, Survey $survey)
    {
        $this->validate($request, [
            'start_date' => 'date',
            'end_date' => 'date',
            'notes' => 'array'
        ]);
        $survey->ar_name = $request->has('ar_name') ? $request->ar_name : $survey->ar_name;
        $survey->en_name = $request->has('en_name') ? $request->en_name : $survey->en_name;
        $survey->color = $request->has('color') ? $request->color : $survey->color;
        $survey->start_date = $request->has('start_date') ? $request->start_date : $survey->start_date;
        $survey->end_date = $request->has('end_date') ? $request->end_date : $survey->end_date;
        $survey->notes = $request->has('notes') ? json_encode($request->notes) : $survey->notes;
        if ($survey->save()) {
            return response()->json([
                'message' => 'survey updated successfully'
            ]);
        }
    }
    /**
     * @OA\Put(
     *     path="/survey/reuse/{survey}",
     *     tags={"surveys"},
     *     summary="Reuse a survey",
     *     description="Updates the start and end dates of a survey to reuse it",
     *     @OA\Parameter(
     *         name="survey",
     *         in="path",
     *         description="ID of the survey to reuse",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="Success message"
     *             )
     *         )
     *     ),
     * security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function reuse(Survey $survey)
    {
        $survey->status = 'valid';
        $start = Carbon::parse($survey->start_date);
        $end = Carbon::parse($survey->end_date);
        $duration = $end->diff($start);
        $newEndDate = Carbon::now()->add($duration);
        $survey->start_date = Carbon::now();
        $survey->end_date = $newEndDate;
        $survey->save();
        return response()->json([
            'message' => 'This survey is active.'
        ]);
    }
}
