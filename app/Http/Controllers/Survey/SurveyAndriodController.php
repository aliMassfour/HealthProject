<?php

namespace App\Http\Controllers\Survey;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SurveyAndriodController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
   /**
 * @OA\Get(
 *     path="/app/survey/index",
 *     summary="Show all available surveys for authenticated user",
 *     description="Send a request to fetch surveys",
 *     operationId="AppSurveyIndex",
 *     tags={"Android Application"},
 *     @OA\Response(
 *         response=200,
 *         description="Successfully retrieved surveys",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="surveys",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(
 *                         property="ar_name",
 *                         type="string"
 *                     ),
 *                     @OA\Property(
 *                         property="en_name",
 *                         type="string"
 *                     ),
 *                     @OA\Property(
 *                         property="color",
 *                         type="string"
 *                     ),
 *                     @OA\Property(
 *                         property="questions_count",
 *                         type="integer"
 *                     ),
 *                     @OA\Property(
 *                         property="start_date",
 *                         type="string",
 *                         format="date-time"
 *                     ),
 *                     @OA\Property(
 *                         property="end_date",
 *                         type="string",
 *                         format="date-time"
 *                     ),
 *                     @OA\Property(
 *                         property="remaining_days",
 *                         type="integer"
 *                     ),
 *                     @OA\Property(
 *                         property="status",
 *                         type="string"
 *                     ),
 *                     @OA\Property(
 *                         property="notes",
 *                         type="array",
 *                         @OA\Items(
 *                             type="string"
 *                         )
 *                     )
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="notifications",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(
 *                         property="message",
 *                         type="string"
 *                     ),
 *                     @OA\Property(
 *                         property="reading_status",
 *                         type="integer"
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */
    public function index()
    {
        $user = auth()->user();
        $notifications = $user->notifications;
        $surveys = Survey::where('status', 'valid')
            ->with('users', 'entries')
            ->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereDoesntHave('entries', function ($query) use ($user) {
                $query->where('participant_id', $user->id);
            })
            ->orderBy('created_at')
            ->get()
            ->groupBy(function ($survey) {
                return $survey->created_at->format('Y-M-D');
            });
        $surveys->makeHidden(['entries', 'pivot', 'users']);
        foreach ($surveys as $date_group) {
            foreach ($date_group as &$survey) {
                $end = Carbon::parse($survey->end_date);
                $remaining_days = $end->diff(now())->days;
                $survey->setAttribute('remaining_days', $remaining_days);
            }
        }
        $notifications->filter(function ($notification) {
            $notification->update(['reading_status', '0']);
        });
        return response()->json([
            'surveys' => $surveys,
            'notifications' => $notifications
        ]);
    }
    /**
     * @OA\Get(
     *     path="/app/survey/show/{survey}",
     *     operationId="showSurveyQuestions",
     *     tags={"Android Application"},
     *     summary="Retrieve questions for a survey",
     *     description="This endpoint retrieves all the questions associated with a given survey. It includes questions directly attached to the survey and questions belonging to the main titles and sub-titles of the survey.",
     *     @OA\Parameter(
     *         name="survey",
     *         in="path",
     *         description="ID of the survey",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="survey_id",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="content",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="type",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="options",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string"
     *                     ),
     *                     nullable=true
     *                 ),
     *                 @OA\Property(
     *                     property="required",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="created_at",
     *                     type="string",
     *                     nullable=true
     *                 ),
     *                 @OA\Property(
     *                     property="updated_at",
     *                     type="string",
     *                     nullable=true
     *                 ),
     *                 @OA\Property(
     *                     property="main_title",
     *                     type="integer",
     *                     nullable=true
     *                 ),
     *                 @OA\Property(
     *                     property="sub_title",
     *                     type="integer",
     *                     nullable=true
     *                 )
     *             )
     *         )
     *     ),
     *    security={{"bearerAuth": {}}}
     * )
     */
    public function show(Survey $survey)
    {
        $questions = $survey->getAllQuestions();
        $questions->filter(function ($question) {
            $main_title_name = $question->MainTile !== null ?  $question->MainTitle->name : null;
            $sub_title_name = $question->SubTitle !== null ? $question->SubTitle->name : null;
            $question->setAttribute('main_title', $main_title_name);
            $question->setAttribute('sub_title', $sub_title_name);
            $question->options = json_decode($question->options);
            return $question;
        });
        return response()->json([
            'questions' => $questions
        ]);
    }
}
