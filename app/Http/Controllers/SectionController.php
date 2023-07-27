<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function store(Survey $survey, $section_name)
    {
        $section = $survey->sections()->create([
            'name' => $section_name
        ]);
        return $section->id;    
    }
}
