<?php

namespace App\Http\Controllers\Api\Auth;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function store(Request $request):JsonResponse

    {  /** @var array{email: string, password: string} $validated */
        $validated = $request->validate([
            'email' => ['required', 'string', 'max:255','email'],
            'password' => ['required', 'string',],
        ]);
        $user = User::where('email', $validated['email'])->first();
        if (!$user || !Hash::check($validated['password'], $user->password)) {

            return response()->json([
                'message' => 'Неверные данные при входе',
            ], 401);
        }
            $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Вход выполнен успешно',
            'user' => $user,
            'token' => $token,
        ]);
    }
}

