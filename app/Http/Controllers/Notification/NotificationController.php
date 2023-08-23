<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Illuminate\Http\Request;

/**
 * @OA\Post(
 *     path="/lazyusers/notificate/{survey}",
 *     tags={"surveys"},
 *     summary="Send notifications for unanswered surveys",
 *     description="Sends notifications to users who have not answered the specified survey.",
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
 *             @OA\Property(property="message", type="string", example="Users have been notified successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Survey not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Survey not found")
 *         )
 *     ),
 *   security={
*         {"bearerAuth": {}}
*     }
 * )
 */
class NotificationController extends Controller
{
    public function notificate(Survey $survey)
    {
        $users = $survey->users;
        foreach ($users as $user) {
            if (!$user->isAnswer($survey)) {
                $user->notifications()->create([
                    'message' => 'please answer the survey ' . $survey->en_name
                ]);
            }
        }
        return response()->json([
            'message' => 'users is notificated successfully'
        ]);
    }
}
