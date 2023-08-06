<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
   /**
 * @OA\Post(
 *     path="/user/changePassword",
 *     summary="Edit user password",
 *     description="This endpoint allows the user to change their password.",
 *     operationId="editPassword",
 *     tags={"Auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="_method",
 *                     type="string",
 *                     enum={"PUT"}
 *                 ),
 *                 @OA\Property(
 *                     property="current_password",
 *                     type="string",
 *                     format="password"
 *                 ),
 *                 @OA\Property(
 *                     property="new_password",
 *                     type="string",
 *                     format="password"
 *                 ),
 *                 @OA\Property(
 *                     property="new_password_confirmation",
 *                     type="string",
 *                     format="password"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Password change successful",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Password changed successfully"
 *             )
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */
    public function editPassword(Request $request)
    {
        $this->validate($request, [
            'current_password' => 'required',
            'new_password' => 'required|confirmed'
        ]);
        $user = auth()->user();
        if (Hash::check($request->current_user, $user->password)) {
            $user->password = Hash::make($request->new_password);
            $user->save();
            return response()->json([
                'message' => 'password changen successfully'
            ]);
        }
        return response()->json(
            [
                'message' => 'incorrect current password'

            ]
        );
    }
}
