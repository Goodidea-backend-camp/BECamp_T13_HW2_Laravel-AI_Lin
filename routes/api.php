<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

//註冊
Route::post('/register', [RegisterController::class, 'register']);

//登入
Route::post('/login', [AuthController::class, 'login'])->name('login');
//登出
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

//寄送驗證信
Route::get('email/verify/{id}/{hash}', [RegisterController::class, 'verifyEmail'])->name('verification.verify');

//第三方登入
Route::get('/google/redirect', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
Route::middleware('auth:sanctum')->post('/google/setup', [GoogleAuthController::class, 'handleGoogleSetup']);
