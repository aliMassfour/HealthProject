<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function notificate(Survey $survey)
    {
        $users = $survey->users;
        foreach ($users as $user) {
            if (!$user->isAnswer($survey)) {
                $user->notifications()->create([
                    'message' => 'please answer the survey ' . $survey->en_name
                ]);
            }
        }
        return response()->json([
            'message' => 'users is notificated successfully'
        ]);
    }
}
