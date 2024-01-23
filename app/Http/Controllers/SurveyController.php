<?php

namespace App\Http\Controllers;

use App\Enum\QuestionTypeEnum;
use App\Models\Survey;
use App\Http\Requests\StoreSurveyRequest;
use App\Http\Requests\UpdateSurveyRequest;
use App\Http\Resources\SurveyResource;
use App\Models\SurveyQuestion;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;

class SurveyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user= $request->user();

        $surveys = Survey::where('user_id',$user->id)->orderBy('id','desc')->paginate(10);
        return SurveyResource::collection($surveys);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSurveyRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['title']);
        if(isset($data['image'])){
            $relativePath = $this->saveImage($data['image']);
            $data['image'] = $relativePath;
        }
        $survey = Survey::create($data);

        // Create new questions
        foreach($data['questions'] as $question){
            $question['survey_id']= $survey->id;
            $this->createQuestion($question);
        }

        return new SurveyResource($survey);
    }

    /**
     * Display the specified resource.
     */
    public function show(Survey $survey,Request $request)
    {
        $user = $request->user();
        if($user->id !== $survey->user_id){
            return abort(403,"Unauthorized Access");
        }
        return new SurveyResource($survey);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Survey $survey)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSurveyRequest $request, Survey $survey)
    {
        $data = $request->validated();
        //if image was given ,save on local file
        if(isset($data['image'])){
            $relativePath = $this->saveImage($data['image']);
            $data['image'] = $relativePath;

            // if there is an old image, delete it
            if($survey->image){
                $absolutePath  = public_path($survey->image);
                File::delete($absolutePath);
            }
        }

        //update survey in database
        $survey->update($data);

        // For survey question
        //Get ids as plain array of existing questions
        $existingIds  = $survey->questions()->pluck('id')->toArray();
        //Get ids as plain array of new questions
        $newIds = Arr::pluck($data['questions'],'id');
        // Find questions to delete
        $toDelete = array_diff($existingIds,$newIds);
        // Find questions to add
        $toAdd = array_diff($newIds,$existingIds);

        // Create new questions
        foreach($data['questions'] as $question){
            if(in_array($question['id'],$toAdd)){
                $question['survey_id'] = $survey->id;
                $this->createQuestion($question);
            }
        }

        // update existing question
        $questionMap = collect($data['questions'])->keyBy('id');
        foreach($survey->questions as $question){
            if(isset($questionMap[$question->id])){
                $this->updateQuestion($question,$questionMap[$question->id]);
            }
        }
        return new SurveyResource($survey);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Survey $survey,Request $request)
    {
        $user=  $request->user();
        if($user->id != $survey->user_id){
            return abort(403,'Unauthorized action.');
        }
        $survey->delete();

        // if there is an old image,delete it
        if($survey->image){
            $absolutePath = public_path($survey->image);
            File::delete($absolutePath);
        }
        return response('',204);
    }

    private function saveImage($image){
        //check if the image is valid base64 string
        if(preg_match('/^data:image\/(\w+);base64,/', $image, $type)){
            // base64 encode
            $image  = substr($image,strpos($image,',')+1);
            //get file extension
            $type = strtolower($type[1]);
            // check if the file is image
            if(!in_array($type,['jpg','jpeg','gif','png'])){
                throw new Exception('invalid image type');
            }
            $image = str_replace(' ','+',$image);
            $image = base64_decode($image);

            if($image==false){
                throw new \Exception('base64_decode failed.');
            }
        }else{
            throw new \Exception('did not match data URI with image data.');
        }

        $dir = 'images/';
        $file= Str::random().'.'.$type;
        $absolutePath = public_path($dir);
        if(!File::exists($absolutePath)){
            File::makeDirectory($absolutePath,0755,true);
        }
        $relativePath = $dir . $file;

        file_put_contents($relativePath,$image);
        return $relativePath;
    }

    private function createQuestion($data){
        if(is_array($data['data'])){
            $data['data'] = json_encode($data['data']);
        }
        $validator = Validator::make($data,[
            'question' =>['required','string'],
            'type'=>['required',new Enum(QuestionTypeEnum::class)],
            'description'=>['nullable','string'],
            'data'=>['present'],
            'survey_id'=>['exists:surveys,id']
        ]);
        return SurveyQuestion::create($validator->validated());
    }

    private function updateQuestion(SurveyQuestion $question,$data){
        if(is_array($data['data'])){
            $data['data'] = json_encode($data['data']);;
        }
        $validator = Validator::make($data, [
            'id'=>['exists:survey_questions,id'],
            'question' => ['required', 'string'],
            'type' => ['required', new Enum(QuestionTypeEnum::class)],
            'description' => ['nullable', 'string'],
            'data' => ['present'],
        ]);
        return $question->update($validator->validated());
    }
}
