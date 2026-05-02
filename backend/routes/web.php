<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/test', function () {
    return "laravel home page)))))";
});
Route::get('/', function () {
    return "laravel работает через Do11cker";
});

Route::get('api/message', function () {
    return response()->json([
        'text' => 'Привет! Это ответ с Laravel-бэкенда.'
    ]);
});
Route::post('/api/reverse-text', function (\Illuminate\Http\Request $request) {
    $text = $request->input('text', '');

    $reversedText = mb_strrev($text);

    return response()->json([
        'original' => $text,
        'reversed' => $reversedText,
    ]);
});

function mb_strrev($string)
{
    return implode('', array_reverse(preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY)));
}
