<?php

use App\Http\Controllers\web\auth\LoginController;
use App\Http\Controllers\web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'index']);
Route::post('/user/signIn', [LoginController::class, 'signIn']);

Route::get('/dashboard', [DashboardController::class, 'index']);
