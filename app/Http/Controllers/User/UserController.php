<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        //add the nedded mobile app view needed
        $users->filter(function ($user) {
            $user->setAttribute('city_name', $user->city->name);
            $user->setAttribute('directorate_name', $user->directorate->name);
            $user->makeHidden(['directorate', 'city', 'created_at', 'updated_at']);
            return $user;
        });
        return response()->json([
            'users' => $users
        ]);
    }
    public function store(Request $request)
    {
        //validate
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
            'gender' => 'required'

        ]);
        try {
            //create new user
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'city_id' => $request->city,
                'directorate_id' => $request->directorate,
                'role_id' => 2,
                'phone' => $request->phone,
                'gender' => $request->gender,
                'certificate' => $request->certificate,
                'courses' => json_encode($request->courses)
            ]);
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
}
