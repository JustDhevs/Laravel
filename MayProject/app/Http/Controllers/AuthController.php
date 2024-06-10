<?php

namespace App\Http\Controllers;

use App\Models\Administrator;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function signup (Request $request){
        $validator = Validator::make($request->all(),[
            'username' => 'required|unique:users,username|min:4|max:60',
            'password' => 'required|min:5|max:10'
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 'invalid',
                'message' => 'Request body is not valid',
                'violation' => $validator->errors()
            ]);
        }

        $newUser = new User();
        $newUser->username = $request->username;
        $newUser->password = bcrypt($request->password);
        $newUser->save();

        if(Auth::attempt($request->only(['username', 'password']))){
            $token = $newUser->createToken("SANCTUMTOKEN")->plainTextToken;

            return response()->json([
                'status' => 'success',
                'token' => $token
            ],201);
        }
    }

    public function signin(Request $request){
        $validator = Validator::make($request->all(),[
            'username' => 'required|min:4|max:60',
            'password' => 'required|min:5'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'invalid',
                'message' => 'Request body is not valid',
                'violations' => $validator->errors()
            ]);
        }
        $user = Administrator::where('username', $request->username)->first();
        $guard = 'admin';
        if(!$user){
            $user = User::where('username', $request->username)->first();
            $guard = 'web';
        }
        if(!$user){
            return response()->json([
                'status' => 'invalid',
                'message' => 'Username does not exist'
            ], 401);
        }
        if(!Auth::guard($guard)->attempt($request->only(['username', 'password']))){
            return response()->json([
                'status' => 'invalid',
                'message' => 'Wrong username or password'
            ]. 401);
        }
        $user->last_login_at = Carbon::now();
        $user->save();

        $token = $user->createToken("SANCTUMTOKEN")->plainTextToken;
        return response()->json([
            'status' => 'success',
            'token' => $token
        ], 200);

        $token = $user->createToken("SANCTUMTOKEN")->plainTextToken;
        return response()->json([
            'status' => 'success',
            'token' => $token
        ], 200);
    }

    public function signout(Request $request){
        if($request->user()->Tokens()->delete())
        return response([
            'status' => 'success'
        ], 200);
    }
}
