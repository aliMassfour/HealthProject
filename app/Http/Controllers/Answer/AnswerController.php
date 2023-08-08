<?php

namespace App\Http\Controllers\Answer;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnswerController extends Controller
{
   /**
 * @OA\Post(
 *     path="/answer/store/{survey}",
 *     summary="Send answers for a survey",
 *     description="Submit answers for a survey",
 *     operationId="AnswerSurveyStore",
 *     tags={"Android Application"},
 *     @OA\Parameter(
 *         name="survey",
 *         in="path",
 *         description="The ID of the survey",
 *         required=true,
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             @OA\Property(property="answers", type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="question_id", type="integer"),
 *                     @OA\Property(property="value", type="string")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Answers successfully stored",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Answers successfully stored")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="object", example={"question_id": {"The question_id field is required."}})
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Sorry, an error occurred. Please try again.")
 *         )
 *     ),
 *  security={{"bearerAuth": {}}}
 * )
 */

    public function store(Request $request, Survey $survey)
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
