<?php

use App\Http\Controllers\SSO\SSOController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/sso/login', [SSOController::class,'login'])->name('sso.login');
Route::get('/callback', [SSOController::class,'callback'])->name('sso.callback');
Route::get('/sso/authuser', [SSOController::class,'authuser'])->name('sso.authuser');

Auth::routes(['register' => false, 'reset' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('index');
