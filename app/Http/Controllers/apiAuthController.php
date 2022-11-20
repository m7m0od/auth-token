<?php

namespace App\Http\Controllers;


use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class apiAuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'required|email|unique:users,email|max:255',
            'password'=>'required|string|min:5|max:30|confirmed'
        ]);
        $data['password'] = encrypt($data['password']);
        $data['role_id'] = Role::where('name','user')->first()->id;
        $data['access-token'] = Str::random(64);
        $user = User::create($data);

        return response()->json([
            'access-token' => $data['access-token']
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'=>'required|email|max:255',
            'password'=>'required|string|min:5|max:30'
        ]);

        $isLogin = auth()->attempt(['email'=> $data['email'],'password' => $data['password']]);

        if(! $isLogin)
        {
            return back()->withErrors([
                'error_msg' => "not correct login details"
            ],422);
        }

        $accessToken = Str::random(64);
    
        auth()->user->update([
            'access-token' => $accessToken,
        ]);

        return response()->json([
            'access-token' => $accessToken,
            'success_msg' => "logged in"
        ]);
    }

    public function logout(Request $request)
    {
        $accessToken = $request->header('Access-Token');
        User::where('access-token',$accessToken)->first()->update([
            'access-token' => null,
        ]);
        return response()->json([
            'success_msg' => "logged out"
        ]);
    }
}
