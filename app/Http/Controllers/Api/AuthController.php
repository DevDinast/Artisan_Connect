<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
 use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Artisan;
use App\Models\Acheteur;
use App\Models\Administrateur;

class AuthController extends Controller{


public function register(Request $request)
{
$data=$request->validate([
    'name' => 'required|string|max:20',
    'email' => 'required|email|unique:users,email', // obligatoire, format email, unique dans la table users
    'password' => 'required|string|min:8|confirmed', // obligatoire, minimum 8 caractères, et doit correspondre au champ password_confirmation
    'role' => 'required|in:administrateur,artisan,acheteur', // obligatoire et limité aux valeurs possibles
    'telephone' => 'nullable|string|max:15', // optionnel, chaîne max 15 caractères
    'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // optionnel, doit être une image
    'actif' => 'required|boolean', // obligatoire, doit être vrai/faux
]);


try{
 DB::beginTransaction();
 $data['password']=Hash::make($data['password']);
 $user=User::create($data);

 if($user->role==="administrateur")
    {
        $administrateur=Administrateur::create([
            'user_id'=>$user->id,
        ]);
    }
 elseif($user->role==="artisan")
    {
        $artisan=Artisan::create([
            'user_id'=>$user->id,
            'compte_valide'=>false,

        ]);
    }
 elseif($user->role==="acheteur")
    {
        $acheteur=Acheteur::create([
            'user_id'=>$user->id,
        ]);
    }

    $user->sendEmailVerificationNotification();
    $token=$user->createToken('authToken')->plainTextToken;

    DB::commit();
    return response()->json([
        'user'=>$user,
        'token'=>$token,
    ],201);
}
 catch(\Exception $e){

    DB::rollBack();
    return response()->json([
        'erreur'=>'error',
    ],500);
 }
}

public function login(Request $request)
{
    $request->filled('email');
    $request->filled('password');
    Auth::Attempt(['email'=>'','password'=>''],401);
    $user=Auth::user();
    $user->tokens->delete();
}


}
