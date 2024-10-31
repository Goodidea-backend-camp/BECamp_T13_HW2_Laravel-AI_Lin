<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AuthController;


//註冊
Route::post('/register', [RegisterController::class, 'register']);

//登入
Route::post('/login', [AuthController::class, 'login'])->name('login');
//登出
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

