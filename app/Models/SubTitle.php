<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubTitle extends Model
{
    use HasFactory;
    protected $fillable=[
        'name' ,
        'main_title' 
    ];
    public function MainTitle()
    {
        return $this->belongsTo(MainTitle::class,'main_title','id');
    }
    public function questions()
    {
        return $this->hasMany(Question::class,'sub_title','id');
    }

}
