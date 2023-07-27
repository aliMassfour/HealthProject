<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Survey\SurveyController;
use App\Http\Controllers\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
// this group is for users management
Route::group(['middleware' => ['auth:sanctum', 'admin']], function () {
    Route::post('/user/store', [UserController::class, 'store']);
    Route::get('/user/index', [UserController::class, 'index']);
    Route::put('/user/stopaccount/{user}', [UserController::class, 'stopAccount']);
});
// this group is for survey management
Route::group(['middleware' => ['auth:sanctum', 'admin']], function () {
    Route::post('/survey/store', [SurveyController::class, 'store']);
    // index is to view all valid and active survey
    Route::get('/survey/index', [SurveyController::class, 'index']);
    // this route is for archived survey
    Route::get('/survey/archive', [SurveyController::class, 'getArchive']);
    // this route is for valid survey
    Route::get('/survey/valid', [SurveyController::class, 'getValid']);
    Route::put('/survey/archive/{survey}', [SurveyController::class, 'archive']);
});
