<?php

namespace App\Http\Controllers\AUTH;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PhpParser\Node\Stmt\TryCatch;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request){
        $this -> validate($request,[
            "name" => 'required|max:255',
            "email" => "required|email|max:255|unique:users",
            "password" => "required|min:6|confirmed"
        ]);

        $user = new User();
        $user -> name = $request->name;
        $user -> email = $request -> email;
        $user -> password = Hash::make($request->password);
        $user -> save();

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'user' => $user,
            "token" => $token
        ],201);
    }
    public function login(Request $request){
        $credentcials = $request -> only("email","password");
        
        try{
            if(!$token = JWTAuth::attempt($credentcials)){
                return response()->json([
                    "error" => "Invalid Credentials"
                ],401);
            }

        }catch(JWTException $e){
            return response()->json([
                "error" => "not create token"
            ],500);
        }

        return response()->json(compact("token"));
    }
    public function getUsers(){
        $usuarios = User::all();
        return response()->json([
            "users" => $usuarios
        ]);
    }
    public function logout(){
        Auth::guard("api")->logout();
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Logout successful']);
    }
}
