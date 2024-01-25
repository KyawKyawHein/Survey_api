<?php

namespace App\Http\Controllers;

use App\Http\Resources\SurveyAnswerResource;
use App\Http\Resources\SurveyDashboardResource;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request){
        $user= $request->user();

        // survey count created by user
        $surveyCount = Survey::where('user_id',$user->id)->count();

        // latest survey created by user
        $latestSurvey = Survey::where('user_id',$user->id)->latest('created_at')->first();

        // Total number of answers
        $countOfAnswer = DB::table('survey_answers')
                         ->join('surveys','survey_answers.survey_id','=','surveys.id')
                         ->where('surveys.user_id',$user->id)->count();

        // latest 5 answer
        $latestAnswers= DB::table('survey_answers')
                        ->join('surveys', 'survey_answers.survey_id', '=', 'surveys.id')
                        ->where('surveys.user_id', $user->id)
                        ->orderBy('end_date','DESC')
                        ->limit(5)
                        ->get();
        return [
            "totalSurveys"=>$surveyCount,
            "latestSurvey"=>$latestSurvey ? new SurveyDashboardResource($latestSurvey) : null,
            'totalAnswers'=>$countOfAnswer,
            'latestAnswers'=>SurveyAnswerResource::collection($latestAnswers)
        ];
    }
}
