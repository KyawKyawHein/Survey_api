<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSurveyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(){
        $this->merge([
            "user_id"=> $this->user()->id
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "title"=>['required','string'],
            "image"=>['nullable'],
            "user_id"=>['exists:users,id'],
            "status"=>['required','boolean'],
            "description"=>['string'],
            'expire_date'=>['string','date','after:today'],
            "questions" => 'array'
        ];
    }
}
