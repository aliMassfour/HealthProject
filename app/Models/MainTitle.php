<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainTitle extends Model
{
    use HasFactory;
    protected $fillable =[
        'survey_id' ,
        'name'
    ];
    public function survey()
    {
        return $this->belongsTo(Survey::class,'survey_id','id');
    }
    public function SubTitles()
    {
        return $this->hasMany(SubTitle::class,'main_title','id');
    }
    public function questions()
    {
        return $this->hasMany(Question::class,'main_title','id');
    }
}
