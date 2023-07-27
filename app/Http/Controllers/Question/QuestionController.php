<?php

namespace App\Http\Controllers\Question;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function GuzzleHttp\json_encode;

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
               if(isset($question['options'])){
                $question['options'] = json_encode($question['options']);
               }else {
                $question = array_merge($question,['options'=>null]);
               }
                $validateData[] = $question;
            }
        }
        // return $validateData;
        Question::insert($validateData);
    }
}
