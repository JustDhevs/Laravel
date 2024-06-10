<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Administrator;
use Illuminate\Http\Request;
use App\Models\User; 
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function getAdmin(){
        $admins = Administrator::all();
        $adminId = $admins->pluck('id')->toArray();
        if(!in_array(Auth::id(), $adminId)){
            return response()->json([
                'status' => 'forbidden',
                'message' => 'You are not administrator'
            ], 403);
        }

        return response()->json([
            'totalElement' => count($admins),
            'content' => $admins
        ], 200);
    }

    public function addUser(Request $request){
        $users = User::all();
        $usrnm = $users->pluck('username')->toArray();

        if (in_array($request->username, $usrnm)){
            return response()->json([
                'status' => 'invalid',
                'message' => 'Username already exist'
            ], 400);
        }
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users,username|min:4|max:60',
            'password' => 'required|min:5|max:10'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'invalid',
                'message' => 'Request body is not valid',
                'violations' => $validator->errors()
            ], 400);
        }

        $admins = Administrator::all();
        $adminId = $admins->pluck('id')->toArray();
        if (!in_array(Auth::id(), $adminId)){
            return response()->json([
                'status' => 'forbidden',
                'message' => 'You are not administrator'
            ], 403);
        }

        $user = new User();
        $user->username = $request->username;
        $user->password = bcrypt($request->password);
        $user->save();

        if ($user){
            return response()->json([
                'status' => 'success',
                'username' => $request->username
            ], 201);
        }
    }

    public function userList(){

        $users = User::all();
        $admins = Administrator::all();
        $adminId = $admins->pluck('id')->toArray();
        
        if (!in_Array(Auth::id(), $adminId)){
            return response()->json([
                'status' => 'fotbidden',
                'message' => 'You are not administrator'
            ], 403);
        }

        $users->makeHidden(['delete_reason', 'delete_reason', 'id']);
        return response()->json([
            'totalElement' => count($users),
            'content' => $users 
        ], 200);
    }
    public function updateUser(Request $request, $id){
        $users = User::all();
        $usrnm = $users->pluck('username')->toArray();

        if (in_array($request->username, $usrnm)){
            return response()->json([
                'status' => 'invalid',
                'message' => 'Username already exist'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users,username|min:4|max:60',
            'password' => 'required|min:5|max:10'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'invalid',
                'message' => 'Request body is not valid',
                'violation' => $validator->errors()
            ], 400);
        }

        $admins = Administrator::all();
        $adminId = $admins->pluck('id')->toArray();
        if (!in_array(Auth::id(), $adminId)){
            return response()->json([
                'status' => 'forbidden',
                'message' => 'You are not administrator'
            ], 403);
        }

        $user = User::where('id', $id)->first();
        if (!$user){
            return response()->json([
                'status' => 'not-found',
                'message' => 'User not found'
            ], 403);
        }

        $user->username = $request->username;
        $user->password = brcypt($request->password);
        $user->save();

        if ($user){
            return response()->json([
                'status' => 'success',
                'username' => $request->username
            ]);
        }
    }
}