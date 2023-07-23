<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'name' => 'required',
            'email' => 'required',
            'password' => 'required|confirmed'
        ]);
        // return $request->all();
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'name' => $request->name

        ]);
        $token = $user->createToken('Application token' . $user->name)->plainTextToken;
        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }
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
    public function logout(Request $request)
    {

        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'logout successfully'
        ]);
    }
}
