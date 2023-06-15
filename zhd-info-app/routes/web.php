<?php

use App\Http\Controllers\Admin\Account\AccountController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\Message\MessagePublishController;
use App\Http\Controllers\Admin\Message\MessageManageController;
use App\Http\Controllers\ManualController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\TopController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [TopController::class, 'index'])->name('top');
Route::group(['prefix' => 'message', 'as' => 'message.'], function (){
    Route::get('/', [MessageController::class, 'index'])->name('index');
    Route::get('detail', [MessageController::class, 'detail'])->name('detail');
});
Route::group(['prefix' => 'manual', 'as' => 'manual.'], function () {
    Route::get('/', [ManualController::class, 'index'])->name('index');
    Route::get('detail', [ManualController::class, 'detail'])->name('detail');
});

// 管理画面へのログイン画面
Route::get('/auth', [AuthController::class, 'index'])->name('auth');
Route::post('/auth', [AuthController::class, 'login']);

// 管理画面のルート
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth'], function() {
    Route::group(['prefix' => 'message', 'as' => 'message.'], function(){
        Route::group(['prefix' => 'publish', 'as' => 'publish.'], function(){
            Route::get('/', [MessagePublishController::class, 'index'])->name('index');
            Route::match(['get', 'post'], 'new', [MessagePublishController::class, 'new'])->name('new');
            Route::match(['get', 'post'], 'edit/{message_id}', [MessagePublishController::class, 'edit'])->name('edit')->where('message_id', '^\d+$');
        });
        Route::group(['prefix' => 'manage', 'as' => 'manage.'], function () {
            Route::get('/', [MessageManageController::class, 'index'])->name('index');
            Route::get('detail/{message_id}', [MessageManageController::class, 'detail'])->name('detail')->where('message_id', '^\d+$');
        });
    });
    Route::group(['prefix' => 'account', 'as' => 'account.'], function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::match(['get', 'post'], 'new', [AccountController::class, 'new'])->name('new');
    });
});

