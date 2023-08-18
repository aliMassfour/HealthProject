<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/user/index/{role}",
     *     tags={"Users"},
     *     summary="Get users by role",
     *     description="Retrieves a list of users based on the specified role.",
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         description="The role of the users to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"admin", "user"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
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
     *  security={{"bearerAuth": {}}}
     * )
     */
    public function index($role)
    {
        if ($role == 'admin') {
            $users = User::where('role_id', '1')->get();
        } else {
            $users = User::where('role_id', '<>', '1')->get();
        }


        //add the  mobile app view needed
        $users->filter(function ($user) {
            $user->setAttribute('city_name', $user->city->name);
            $user->setAttribute('directorate_name', $user->directorate->name);
            $user->makeHidden(['directorate', 'city', 'created_at', 'updated_at']);
            $user->courses = json_decode($user->courses);
            $user->certificate = json_decode($user->certificate);
            return $user;
        });
        return response()->json([
            'users' => $users
        ]);
    }
    /**
     * @OA\Get(
     *     path="/user/show/{user}",
     *     tags={"Users"},
     *     summary="Get user details",
     *     description="Retrieves details of a specific user.",
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="The ID of the user",
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
     *             type="object",
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="role_id",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="username",
     *                     type="string",
     *                     example="admin"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="admin"
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     type="string",
     *                     example="12345678"
     *                 ),
     *                 @OA\Property(
     *                     property="flag",
     *                     type="string",
     *                     example="0"
     *                 ),
     *                 @OA\Property(
     *                     property="certificate",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string"
     *                     ),
     *                     nullable=true
     *                 ),
     *                 @OA\Property(
     *                     property="gender",
     *                     type="string",
     *                     example="male"
     *                 ),
     *                 @OA\Property(
     *                     property="courses",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string"
     *                     ),
     *                     nullable=true
     *                 ),
     *                 @OA\Property(
     *                     property="city_name",
     *                     type="string",
     *                     example="city one"
     *                 ),
     *                 @OA\Property(
     *                     property="directorate_name",
     *                     type="string",
     *                     example="directorate one"
     *                 ),
     *             )
     *         )
     *     ),
     * security={{"bearerAuth": {}}}
     * )
     */
    public function show(User $user)
    {
        $directorate = $user->directorate->name;
        $city = $user->city->name;
        $user->makeHidden(['city', 'directorate']);
        $user->setAttribute('city_name', $city);
        $user->setAttribute('directorate_name', $directorate);
        $user->setAttribute('courses', json_decode($user->courses));
        $user->setAttribute('certificate', json_decode($user->certificate));
        return response()->json([
            'user' => $user
        ]);
    }
    /**
     * @OA\Post(
     *     path="/user/store",
     *     tags={"Users"},
     *     summary="Create a new user",
     *     description="Creates a new user with the provided information.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="username",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="directorate",
     *                     type="integer",
     *                     format="int64",
     *                 ),
     *                 @OA\Property(
     *                     property="city",
     *                     type="integer",
     *                     format="int64",
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="certificate",
     *                     type="array",
     *                     description="write the certificate",
     *                     @OA\Items(
     *                         type="string",
     *                     ),
     *                 ),
     *                 @OA\Property(
     *                     property="courses",
     *                     type="array",
     *                     description="write the courses",
     *                     @OA\Items(
     *                         type="string",
     *                     ),
     *                 ),
     *                 @OA\Property(
     *                     property="gender",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="evaluation",
     *                     type="string",
     *                     enum={"excellent", "very good", "good"},
     *                 ),
     *                 @OA\Property(
     *                     property="type",
     *                     type="string",
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="the user is created successfully"
     *             ),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
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
     *                     property="phone",
     *                     type="string",
     *                     example="1234567890"
     *                 ),
     *                 @OA\Property(
     *                     property="directorate_id",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="city_id",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="role_id",
     *                     type="integer",
     *                     example=2
     *                 ),
     *                 @OA\Property(
     *                     property="gender",
     *                     type="string",
     *                     example="male",
     *                     enum={"male", "female"}
     *                 ),
     *                 @OA\Property(
     *                     property="certificate",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="courses",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="evaluation",
     *                     type="string",
     *                     example="excellent",
     *                     enum={"excellent", "very good", "good"},
     *                 ),
     *                 @OA\Property(
     *                     property="type",
     *                     type="string",
     *                 ),
     *             )
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function store(Request $request)
    {
        //validate
        return $request->all();
        $this->validate($request, [
            'name' => 'required',
            'phone' => 'required',
            'username' => 'required',
            'directorate' => 'required',
            'city' => 'required',
            'password' => 'required|min:4|max:8',
            'phone' => 'required',
            'certificate' => 'required',
            'courses' => 'required|array',
            'gender' => 'required',
            'type' => 'string|required',
            'evaluation' => 'required|string'
        ]);
        try {
            //create new user
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'city_id' => $request->city,
                'directorate_id' => $request->directorate,
                'role_id' => $request->type=='admin' ? 1 : 2,
                'phone' => $request->phone,
                'gender' => $request->gender,
                'certificate' => $request->type == 'volunteer' ? json_encode($request->certificate) : null,
                'courses' => $request->type == 'volunteer' ? json_encode($request->courses) : null,
                'evaluation' => $request->evaluation
            ]);
            $user->certificate = json_decode($user->certificate);
            $user->courses = json_decode($user->courses);
            return response()->json([
                'message' => 'the user is created successfully',
                'user' => $user
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'user' => null,
            ], 500);
        }
    }
    /**
     * @OA\Put(
     *     path="/user/stopaccount/{user}",
     *     tags={"Users"},
     *     summary="Stop user account",
     *     description="Stops the account of a specific user.",
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="The ID of the user",
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
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="user account stopped successfully"
     *             )
     *         )
     *     ),
     * security={{"bearerAuth": {}}}
     * )
     */
    public function stopAccount(User $user)
    {
        $user->flag = '1';
        if ($user->save()) {
            return response()->json([
                'message' => 'user account stopped successfully'
            ]);
        } else {
            return response()->json([
                'message' => 'error occured please try again'
            ]);
        }
    }
    /**
     * @OA\Put(
     *     path="/user/update/{user}",
     *     tags={"Users"},
     *     summary="Update user information",
     *     description="Updates the information of a specific user.",
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="The ID of the user",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="username",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="directorate",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="city",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="evaluation",
     *                     type="string",
     *                     enum={"excellent", "very good", "good"}
     *                 ),
     *                 @OA\Property(
     *                     property="certificate",
     *                     type="array",
     *                      @OA\Items(
     *                         type="string"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="courses",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string"
     *                     )
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="users information updated successfully"
     *             )
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */

    public function update(Request $request, User $user)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
                'username' => 'required',
                'directorate' => 'required',
                'city' => 'required',
                'password' => 'required|min:4|max:8',
                'phone' => 'required',
                'evaluation' => 'required|in:excellent,very good,good',
                'certificate' => 'required|array',
                'courses' => 'required|array'
            ]);
            $user->update([
                'name' => $request->name,
                'username' => $request->username,
                'directorate' => $request->directorate,
                'city' => $request->city,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'evaluation' => $request->evaluation
            ]);
            return response()->json([
                'message' => 'users information updated successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }
    /**
     * @OA\Put(
     *     path="/user/activAccount/{user}",
     *     tags={"Users"},
     *     summary="Activate user account",
     *     description="Activates the account of a specific user.",
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="The ID of the user",
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
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="user account activated successfully"
     *             )
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function activeAccount(User $user)
    {
        $user->flag = '0';
        $user->save();
        return response()->json([
            'message' => 'user account activated successfully'
        ]);
    }
}
