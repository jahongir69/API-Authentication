<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Jobs\SendVerificationEmailJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        
        if ($request->hasFile('avatar')) {
        
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar()->create(['path' => $avatarPath]);
        }
        
        
        SendVerificationEmailJob::dispatch($user);
        
        return new UserResource($user);
    }
    
    
    
    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        
        if (!Auth::attempt($data)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        
        $user = Auth::user();
        if (!$user->email_verified_at) {
            return response()->json(['message' => 'Please verify your email address'], 403);
        }
        
        $token = $user->createToken('authToken')->plainTextToken;
        return response()->json(['token' => $token]);
    }
    
    
    
    public function verifyEmail($id, $hash)
    {
        $user = User::findOrFail($id);
        if (sha1($user->email) !== $hash) {
            return response()->json(['message' => 'Invalid verification link'], 400);
        }
        $user->update(['email_verified_at' => now()]);
        return response()->json(['message' => 'Email verified successfully']);
    }
    
    
     
    public function logout()
    {
        Auth::user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
