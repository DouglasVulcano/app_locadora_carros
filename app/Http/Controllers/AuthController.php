<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request) {
        $creadentials = $request->all(['email', 'password']);

        // Autenticação (usuário e senha)
        $token = auth('api')->attempt($creadentials);

        // Retornar token
        if($token){
            return response()->json([
                'token' => $token
            ], 200);
        } else {
            return response()->json([
                'error' => 'Usuário ou senha inválidos'
            ], 403);
        }
    }

    public function logout() {
        return 'logout';
    }

    public function refresh() {
        $token = auth('api')->refresh(); // Cliente emcaminhe um JWT válido
        return response()->json([
            'token' => $token
        ]);
    }

    public function me() {
        return response()->json(auth()->user());
    }
}
