<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
    public function survey()
    {
        return $this->belongsTo(Survey::class, 'survey_id', 'id');
    }
    public function questions()
    {
        return $this->hasMany(Question::class, 'section_id', 'id');
    }
}
