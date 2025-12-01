<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\Api\Auth\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    // created by kariem ibrahiem
    // get all dependencies i might need
    public function __construct(protected AuthService $authService)
    {
    }

    // user registration
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        return $this->authService->register($data);
    }
    
    // user login
    public function login(Request $request)
    {
        $data = $request->only('email', 'password');
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        return $this->authService->login($data);
    }

    public function logout()
    {
        return $this->authService->logout();
    }
}
