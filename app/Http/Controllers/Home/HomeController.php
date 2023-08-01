<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\UserController;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $UserController = app(UserController::class);
        $users = $UserController->index();
        return response()->json([
            'auth_user' => $user,
            'users'=>$users->original['users']
        ]);
    }
}
