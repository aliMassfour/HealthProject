<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
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
