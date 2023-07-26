<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $filable=[
        'survey_id' ,
        'section_id' ,
        'content' ,
        'type' ,
        'options' ,
        'required'
    ];
    public function survey()
    {
        return $this->belongsTo(Survey::class,'survey_id','id');
    }
    public function section()
    {
        return $this->belongsTo(Section::class,'section_id','id');
    }
}
