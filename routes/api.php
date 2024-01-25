<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\SurveyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/user', function (Request $request) {
      return $request->user();
    });
    Route::post('/logout',[AuthController::class,'logout']);
    Route::apiResource('survey',SurveyController::class);
    Route::get('/me',[AuthController::class,'me']);
    // Route::get('/dashboard',[])
});


Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::get('/survey/public-view/{survey:slug}',[SurveyController::class,'getSurveyBySlug']);
Route::post('/survey/{survey:slug}/answer',[SurveyController::class,'storeAnswer']);
