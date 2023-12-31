<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainTitle extends Model
{
    use HasFactory;
    protected $fillable = [
        'name','en_name'
    ];
    public function survey()
    {
        return $this->belongsToMany(Survey::class, 'SurveyMain', 'survey', 'main_title', 'id', 'id');
    }
    public function SubTitles()
    {
        return $this->hasMany(SubTitle::class, 'main_title', 'id');
    }
    public function questions()
    {
        return $this->hasMany(Question::class, 'main_title', 'id');
    }
}
