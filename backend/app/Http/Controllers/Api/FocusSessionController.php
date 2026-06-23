<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\FocusSession;
use Illuminate\Http\Request;

class FocusSessionController extends Controller
{
    public function store(Request $request): JsonResponse

    {
            $request->validate([
            'mode' => ['required', 'string', 'in:focus'],
            'duration_seconds' => ['required', 'integer', 'min:1'],
        ]);

        $user = $request->user();

        if (!$user instanceof User) {
            return response()->json([
                'message' => 'Пользователь не авторизован',
            ], 401);
        }

        $session = FocusSession::create([
            'user_id' => $user->id,
            'mode' => $request->string('mode')->toString(),
            'duration_seconds' => $request->integer('duration_seconds'),
            'completed_at' => now(),
        ]);
        return response()->json([
            'message' => 'Focus session created successfully',
            'session' => $session
        ]);
    }

    public function index(Request $request): JsonResponse

    {
        $user = $request->user();

        if (!$user instanceof User) {
            return response()->json([
                'message' => 'Пользователь не авторизован',
            ], 401);
        }
        $statistics = $user->focusSessions()
            ->latest('completed_at')
            ->get();

        return response()->json([
            'message' => 'Focus session list',
            'statistics' => $statistics]);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user instanceof User) {
            return response()->json([
                'message' => 'Пользователь не авторизован',
            ], 401);
        }
        $user->focusSessions()->delete();
        return response()->json([
            'message' => 'Focus sessions deleted successfully',
        ]);
    }


}

