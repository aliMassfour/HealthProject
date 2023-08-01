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
        //add the  mobile app view needed
        $users->filter(function ($user) {
            $user->setAttribute('city_name', $user->city->name);
            $user->setAttribute('directorate_name', $user->directorate->name);
            $user->makeHidden(['directorate', 'city', 'created_at', 'updated_at']);
            $user->courses = json_decode($user->courses);
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
    public function update(Request $request, User $user)
    {
        $this->validate($request, [
            'name' => 'required',
            'username' => 'required',
            'directorate' => 'required',
            'city' => 'required',
            'password' => 'required|min:4|max:8',
            'phone' => 'required',
        ]);
        try {
            $user->update([
                'name' => $request->name,
                'username' => $request->username,
                'directorate' => $request->directorate,
                'city' => $request->city,
                'password' => Hash::make($request->password),
                'phone' => $request->phone
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
}
