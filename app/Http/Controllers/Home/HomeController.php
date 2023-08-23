<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\UserController;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/home",
     *     tags={"Dashboard"},
     *     summary="User dashboard",
     *     description="Retrieves the dashboard information for the authenticated user.",
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="auth_user",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     format="int64",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="John Doe"
     *                 ),
     *                 @OA\Property(
     *                     property="username",
     *                     type="string",
     *                     example="johndoe"
     *                 ),
     *                 @OA\Property(
     *                     property="certificate",
     *                     type="array",
     *                     @OA\Items(type="string", example="XYZ Certificate")
     *                 ),
     *                 @OA\Property(
     *                     property="courses",
     *                     type="array",
     *                     @OA\Items(type="string", example="Course A")
     *                 ),
     *                 @OA\Property(
     *                     property="directorate_name",
     *                     type="string",
     *                     example="Marketing"
     *                 ),
     *                 @OA\Property(
     *                     property="directorate_id",
     *                     type="integer",
     *                     format="int64",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="city_name",
     *                     type="string",
     *                     example="New York"
     *                 ),
     *                 @OA\Property(
     *                     property="city_id",
     *                     type="integer",
     *                     format="int64",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     type="string",
     *                     example="1234567890"
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="users",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         format="int64",
     *                         example=2
     *                     ),
     *                     @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="Jane Smith"
     *                     ),
     *                     @OA\Property(
     *                         property="username",
     *                         type="string",
     *                         example="janesmith"
     *                     ),
     *                     @OA\Property(
     *                         property="certificate",
     *                         type="array",
     *                         @OA\Items(type="string", example="ABC Certificate")
     *                     ),
     *                     @OA\Property(
     *                         property="courses",
     *                         type="array",
     *                         @OA\Items(type="string", example="Course B")
     *                     ),
     *                     @OA\Property(
     *                         property="directorate_name",
     *                         type="string",
     *                         example="Sales"
     *                     ),
     *                     @OA\Property(
     *                         property="directorate_id",
     *                         type="integer",
     *                         format="int64",
     *                         example=2
     *                     ),
     *                     @OA\Property(
     *                         property="city_name",
     *                         type="string",
     *                         example="London"
     *                     ),
     *                     @OA\Property(
     *                         property="city_id",
     *                         type="integer",
     *                         format="int64",
     *                         example=2
     *                     ),
     *                     @OA\Property(
     *                         property="phone",
     *                         type="string",
     *                         example="9876543210"
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="total",
     *                 type="integer"
     *             )
     *         )
     *     ),
     *    security={{"bearerAuth": {}}}
     * )
     */
    public function dashboard()
    {
        $user = auth()->user();
        $UserController = app(UserController::class);
        $users = $UserController->index('user');
        return response()->json([
            'auth_user' => $user,
            'users' => $users->original['users'],
            'total' => $users->original['total']
        ]);
    }
}