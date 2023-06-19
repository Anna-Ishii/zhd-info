<?php

use App\Http\Controllers\Admin\Account\AccountController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\Manual\ManualManageController;
use App\Http\Controllers\Admin\Manual\ManualPublishController;
use App\Http\Controllers\Admin\Message\MessagePublishController;
use App\Http\Controllers\Admin\Message\MessageManageController;

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

Route::get('/', function () {
    return view('welcome');
})->name('home');
Route::get('/auth', [AuthController::class, 'index'])->name('auth');
Route::post('/auth', [AuthController::class, 'login']);

// Route::get('/admin/message/publish', [MessagePublishController::class, 'index'])->name('massage.publish')->middleware('auth');
// Rotue::get('/admin/message/manage', )
// Rotue::get('/admin/manual/publish', )
// Route::match(['get', 'post'], '/admin/message/publish/new', [MessagePublishController::class, 'new'])->name('message.publish.new');

// Route::get('/admin/account', [AccountController::class, 'index'])->name('account.index');
// Route::match(['get', 'post'], '/admin/account/new', [AccountController::class, 'new'])->name('account.new');

// 管理画面のルート
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth'], function() {
    Route::group(['prefix' => 'message', 'as' => 'message.'], function(){
        Route::group(['prefix' => 'publish', 'as' => 'publish.'], function(){
            Route::get('/', [MessagePublishController::class, 'index'])->name('index');
            Route::match(['get', 'post'], 'new', [MessagePublishController::class, 'new'])->name('new');
            Route::match(['get', 'post'], 'edit/{message_id}', [MessagePublishController::class, 'edit'])->name('edit')->where('message_id', '^\d+$');
            Route::post('/stop', [MessagePublishController::class, 'stop'])->name('stop');
        });
        Route::group(['prefix' => 'manage', 'as' => 'manage.'], function () {
            Route::get('/', [MessageManageController::class, 'index'])->name('index');
            Route::get('detail/{message_id}', [MessageManageController::class, 'detail'])->name('detail')->where('message_id', '^\d+$');
        });
    });
    Route::group(['prefix' => 'manual', 'as' => 'manual.'], function () {
        Route::group(['prefix' => 'publish', 'as' => 'publish.'], function () {
            Route::get('/', [ManualPublishController::class, 'index'])->name('index');
            Route::match(['get', 'post'], 'new', [ManualPublishController::class, 'new'])->name('new');
            Route::match(['get', 'post'], 'edit/{manual_id}', [ManualPublishController::class, 'edit'])->name('edit')->where('manual_id', '^\d+$');
        });
        Route::group(['prefix' => 'manage', 'as' => 'manage.'], function () {
            Route::get('/', [ManualManageController::class, 'index'])->name('index');
            Route::match(['get', 'post'], 'detail/{manual_id}', [ManualManageController::class, 'detail'])->name('detail')->where('manual_id', '^\d+$');
        });
    });
    Route::group(['prefix' => 'account', 'as' => 'account.'], function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::match(['get', 'post'], 'new', [AccountController::class, 'new'])->name('new');
    });
});

