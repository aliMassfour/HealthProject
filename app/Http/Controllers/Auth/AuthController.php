<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     * path="/login",
     * operationId="authLogin",
     * tags={"Auth"},
     * summary="User Login",
     * description="Login User Here",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"username", "password"},
     *               @OA\Property(property="username", type="text"),
     *               @OA\Property(property="password", type="password")
     *            ),
     *        ),
     *    ),
     *    
     *      @OA\Response(
     *          response=200,
     *          description="Login Successfully",
     *          @OA\JsonContent()
     *       ),
     *               @OA\Response(
     *          response=401,
     *          description="Invalid ",
     *          @OA\JsonContent()
     *       ),
     *     
     *
     *     
     * )
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required'
        ]);
        $credentials = $request->only(['username', 'password']);
        if (Auth::attempt($credentials)) {
            $user = User::where('username', $request->username)->first();
            $token = $user->createToken('Application Token' . $user->usernmae)->plainTextToken;
            return response()->json([
                'user' => $user,
                'token' => $token
            ]);
        } else {
            return response()->json([
                'message' => 'Invalid credentials'
            ], '401');
        }
    }
    /**
     * @OA\Post(
     * path="/logout",
     * operationId="authLogout",
     * tags={"Auth"},
     * summary="User Logout",
     * description="Logout User Here",
     * security={{"bearerAuth":{}}},
     * 
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *            
     *            ),
     *        ),
     *    ),
     *    
     *      @OA\Response(
     *          response=200,
     *          description="Logout successfully",
     *          @OA\JsonContent()
     *       ),
     *     
     * )
     */
    public function logout(Request $request)
    {

        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'logout successfully'
        ]);
    }
}
