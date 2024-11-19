<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ThreadController;
use App\Http\Controllers\UserController;
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

//取得使用者資訊
Route::middleware('auth:sanctum')->get('/users/{user}', [UserController::class, 'show']);
//編輯使用者資訊
Route::middleware('auth:sanctum')->patch('/users/{user}', [UserController::class, 'update']);

//使用者建立新的對話串
Route::middleware('auth:sanctum')->post('/threads', [ThreadController::class, 'store']);

//使用者瀏覽自己創建的所有對話串（只有對話串名稱及種類）
Route::middleware('auth:sanctum')->get('/threads', [ThreadController::class, 'index']);

//使用者編輯對話串名稱
Route::middleware('auth:sanctum')->patch('/threads/{thread}', [ThreadController::class, 'update']);

//使用者刪除對話串
Route::middleware('auth:sanctum')->delete('/threads/{thread}', [ThreadController::class, 'destroy']);

// 使用者發送文字訊息，AI回覆文字訊息或是圖片
Route::middleware('auth:sanctum')->post('/threads/{thread}/messages', [MessageController::class, 'store']);
