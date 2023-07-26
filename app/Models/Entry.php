<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    use HasFactory;
    public function answers()
    {
        return $this->hasMany(Answer::class, 'entry_id', 'id');
    }
    public function survey()
    {
        return $this->belongsTo(Survey::class, 'survey_id', 'id');
    }
}
