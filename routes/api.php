<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TweetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::controller(TweetController::class)->group(function () {
    Route::get('tweet', 'index');
    Route::get('tweet/{tweet}', 'show');
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::controller(UserController::class)->group(function () {
        Route::get('users', 'index');
    });

    Route::controller(TweetController::class)->group(function () {
        Route::post('tweet/create', 'create');
        Route::post('tweet/{tweet}/like',  'likeTweet');
        Route::post('tweet/{tweet}/retweet',  'retweet');
        Route::post('tweet/{tweet}/comment',  'retweet');
    });
});
