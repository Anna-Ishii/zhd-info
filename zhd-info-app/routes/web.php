<?php

use App\Http\Controllers\Admin\Account\AccountController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\Manual\ManualManageController;
use App\Http\Controllers\Admin\Manual\ManualPublishController;
use App\Http\Controllers\Admin\Message\MessagePublishController;
use App\Http\Controllers\Admin\Message\MessageManageController;
use App\Http\Controllers\Admin\Setting\ChangePasswordController;
use App\Http\Controllers\AuthController as MemberAuthController;
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

// アプリ側画面へのログイン画面
Route::get('/member/auth', [MemberAuthController::class, 'index'])->name('auth');
Route::post('/member/auth', [MemberAuthController::class, 'login']);
Route::get('/member/logout', [MemberAuthController::class, 'logout'])->name('logout');

Route::get('/', [TopController::class, 'index'])->name('top')->middleware('auth');
Route::group(['prefix' => 'message', 'as' => 'message.', 'middleware' => 'auth'], function (){
    Route::get('/', [MessageController::class, 'index'])->name('index');
    Route::get('detail/{message_id}', [MessageController::class, 'detail'])->name('detail')->where('message_id', '^\d+$');
});
Route::group(['prefix' => 'manual', 'as' =>'manual.', 'middleware' => 'auth'], function () {
    Route::get('/', [ManualController::class, 'index'])->name('index');
    Route::get('detail/{manual_id}', [ManualController::class, 'detail'])->name('detail')->where('manual_id', '^\d+$');
    Route::put('/watched', [ManualController::class, 'watched'])->name('watched');
});

// 管理画面へのログイン画面
Route::get('/admin/auth', [AuthController::class, 'index'])->name('admin.auth');
Route::post('/admin/auth', [AuthController::class, 'login']);
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// 管理画面のルート
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'adminauth'], function() {
    // 管理画面-業務連絡
    Route::group(['prefix' => 'message', 'as' => 'message.'], function(){
        Route::group(['prefix' => 'publish', 'as' => 'publish.'], function(){
            Route::get('/', [MessagePublishController::class, 'index'])->name('index');
            Route::get('new', [MessagePublishController::class, 'new'])->name('new');
            Route::post('new', [MessagePublishController::class, 'store'])->name('new.store');
            Route::get('edit/{message_id}', [MessagePublishController::class, 'edit'])->name('edit')->where('message_id', '^\d+$');
            Route::post('edit/{message_id}', [MessagePublishController::class, 'update'])->name('edit.update')->where('message_id', '^\d+$');
            Route::post('stop', [MessagePublishController::class, 'stop'])->name('stop');
        });
        Route::group(['prefix' => 'manage', 'as' => 'manage.'], function () {
            Route::get('/', [MessageManageController::class, 'index'])->name('index');
            Route::get('detail/{message_id}', [MessageManageController::class, 'detail'])->name('detail')->where('message_id', '^\d+$');
        });
    });
    // 管理画面-動画マニュアル
    Route::group(['prefix' => 'manual', 'as' => 'manual.'], function () {
        Route::group(['prefix' => 'publish', 'as' => 'publish.'], function () {
            Route::get('/', [ManualPublishController::class, 'index'])->name('index');
            Route::get('new', [ManualPublishController::class, 'new'])->name('new');
            Route::post('new', [ManualPublishController::class, 'store'])->name('new.store');
            Route::get('edit/{manual_id}', [ManualPublishController::class, 'edit'])->name('edit')->where('manual_id', '^\d+$');
            Route::post('edit/{manual_id}', [ManualPublishController::class, 'update'])->name('edit.update')->where('manual_id', '^\d+$');
            Route::post('/stop', [ManualPublishController::class, 'stop'])->name('stop');
        });
        Route::group(['prefix' => 'manage', 'as' => 'manage.'], function () {
            Route::get('/', [ManualManageController::class, 'index'])->name('index');
            Route::match(['get', 'post'], 'detail/{manual_id}', [ManualManageController::class, 'detail'])->name('detail')->where('manual_id', '^\d+$');
        });
    });
    Route::group(['prefix' => 'account', 'as' => 'account.'], function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::get('new', [AccountController::class, 'new'])->name('new');
        Route::post('new', [AccountController::class, 'store'])->name('new.store');
        Route::post('/delete', [AccountController::class, 'delete'])->name('delete');
    });
    Route::group(['prefix' => 'setting', 'as' => 'setting.'], function () {
        Route::group(['prefix' => '/change_password', 'as' => 'change_password.'], function () {
        Route::get('/', [ChangePasswordController::class, 'index'])->name('index');
        Route::post('/', [ChangePasswordController::class, 'edit'])->name('edit');
        });
    });

    // パスが/admin/から始まる場合のフォールバックルート
    Route::fallback(function () {
        return redirect(route('admin.message.publish.index'));
    });

});



// パスが/user/から始まる場合のフォールバックルート
Route::fallback(function () {
    return redirect(route('top'));
});