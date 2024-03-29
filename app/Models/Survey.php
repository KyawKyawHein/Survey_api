<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function questions(){
        return $this->hasMany(SurveyQuestion::class);
    }
    public function answers(){
        return $this->hasMany(SurveyAnswer::class);
    }
}
