<?php

namespace App\Http\Controllers\Question;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public function store($questions)
    {
        $validateData = [];
        foreach ($questions as $question) {
            $validator =  Validator::make($question, [
                'survey_id' => 'required',
                'content' => 'required',
                'type' => 'required',
                'options' => 'array',
                'required' => 'required'
            ]);
            if ($validator->fails()) {
                throw new ValidationException($validator->getMessageBag());
            } else {
                $validateData[] = $question;
            }
        }

        Question::insert($validateData);
    }
}
