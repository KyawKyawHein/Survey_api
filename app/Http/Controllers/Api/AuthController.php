<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request){
        $user = User::create([
            "name" =>$request->name,
            "email"=>$request->email,
            "password"=>bcrypt($request->password)
        ]);
        $token = $user->createToken('main')->plainTextToken;
        return response([
            'user'=>$user,
            'token'=>$token
        ]);
    }

    public function login(LoginRequest $request){
        $credentials = $request->validated();
        $remember = $credentials['remember'] ?? false;
        unset($credentials['remember']);

        if(!Auth::attempt($credentials,$remember)){
            return response([
                'error'=> "Provided email or password is not correct."
            ],422);
        };
        $user = Auth::user();
        $token = $user->createToken('main')->plainTextToken;
        return response(compact('user','token'));
    }

    public function logout(Request $request){
        $user = Auth::user();
        $user->currentAccessToken()->delete();
        return response([
            "success"=>true
        ]);
    }

    public function me(Request $request){
        return $request->user();
    }
}
