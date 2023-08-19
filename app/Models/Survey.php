<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;
    protected $fillable = [
        'ar_name',
        'en_name',
        'color',
        'start_date',
        'end_date',
        'questions_count',
        'notes',
        'status'
    ];
    public function questions()
    {
        return $this->hasMany(Question::class, 'survey_id', 'id');
    }
    public function sections()
    {
        return $this->hasMany(Section::class, 'survey_id', 'id');
    }
    public function entries()
    {
        return $this->hasMany(Entry::class, 'survey_id', 'id');
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'users_surveys', 'survey_id', 'user_id', 'id', 'id');
    }
    public function MainTitles()
    {
        return $this->belongsToMany(MainTitle::class, 'SurveyMain', 'survey', 'main_title', 'id', 'id');
    }
    public function getAllQuestions()
    {
        return $this->load(['questions', 'MainTitles.subTitles.questions', 'MainTitles.questions'])
            ->questions
            ->merge(
                $this->MainTitles->flatMap(function ($mainTitle) {
                    return $mainTitle->subTitles->flatMap(function ($subTitle) {
                        return $subTitle->questions;
                    })->merge($mainTitle->questions);
                })
            );
    }
}
