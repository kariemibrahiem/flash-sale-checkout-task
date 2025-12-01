<?php

namespace App\Services\Api\Auth;

use App\Models\User;
use App\Traits\ApiTrait;
use Illuminate\Support\Facades\Log;

class AuthService
{
    // created by kariem ibrahiem
    use ApiTrait;

    // get all dependencies i might need
    public function __construct(protected User $model) {
        Log::info('AuthService initialized' . $this->model->id);
    }

    // user registration
    public function register($data)
    {
        $user = $this->model->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => bcrypt($data['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        Log::info('User registered: ' . $user->id . "with token: " . $token);
        return $this->respondWithSuccess([
            'access_token' => "Bearer " . $token,
            'user' => $user,
        ]);
    }

    // user login
    public function login(array $credentials)
    {
        $user = $this->model->where('email', $credentials['email'])->first();

        if (!$user || !\Hash::check($credentials['password'], $user->password)) {
            return $this->respondWithError('Invalid credentials', 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        Log::info('User logged in: ' . $user->id . "with token: " . $token);
        return $this->respondWithSuccess([
            'access_token' => "Bearer " . $token,
            'user' => $user,
        ]);
    }

    public function logout(){
        auth("client-api")->user()->tokens()->delete();
        Log::info('User logged out: ' . auth("client-api")->id());
        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
