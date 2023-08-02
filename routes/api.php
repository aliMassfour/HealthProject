<?php

use App\Http\Controllers\Answer\AnswerController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Profile\ProfileController;
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
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
// this group is for users management
Route::group(['middleware' => ['auth:sanctum', 'admin']], function () {
    Route::post('/user/store', [UserController::class, 'store']);
    Route::get('/user/index/{role}', [UserController::class, 'index']);
    Route::put('/user/stopaccount/{user}', [UserController::class, 'stopAccount']);
    Route::put('/user/changepassword', [ProfileController::class, 'editPassword']);
    Route::put('/user/update/{user}', [UserController::class, 'update']);
    Route::get('/user/show/{user}', [UserController::class, 'show']);
});
// this group is for survey management
Route::group(['middleware' => ['auth:sanctum', 'admin']], function () {
    Route::post('/survey/store', [SurveyController::class, 'store']);
    // index is to view all valid and active survey
    Route::get('/survey/index/{status}', [SurveyController::class, 'index']);
    Route::put('/survey/archive/{survey}', [SurveyController::class, 'archive']);
    Route::get('/survey/show/{survey}', [SurveyController::class, 'show']);
    Route::get('/users/isanswer/{survey}', [SurveyController::class, 'getAnswersUser']);
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/answer/store/{survey}', [AnswerController::class, 'store']);
});
// notificate group
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/lazyusers/notificate/{survey}', [NotificationController::class, 'notificate']);
});
// this group for home page
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/home', [HomeController::class, 'dashboard']);
});
