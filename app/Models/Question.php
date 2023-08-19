<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $fillable=[
        'survey_id' ,
        'section_id' ,
        'content' ,
        'type' ,
        'options' ,
        'required',
        'main_title' ,
        'sub_title'
    ];
    public function survey()
    {
        return $this->belongsTo(Survey::class,'survey_id','id');
    }
    public function section()
    {
        return $this->belongsTo(Section::class,'section_id','id');
    }
    public function MainTitle()
    {
        return $this->belongsTo(MainTitle::class,'main_title','id');
    }
    public function SubTitle()
    {
        return $this->belongsTo(SubTitle::class,'sub_title','id');
    }
}
