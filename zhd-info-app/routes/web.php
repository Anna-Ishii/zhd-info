<?php

use App\Http\Controllers\Admin\Account\AccountController;
use App\Http\Controllers\Admin\Account\AdminAccountController;
use App\Http\Controllers\Admin\Account\MailAccountController;
use App\Http\Controllers\Admin\Analyse\PersonalContoller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\Manage\ImsController;
use App\Http\Controllers\Admin\Manual\ManualPublishController;
use App\Http\Controllers\Admin\Message\MessagePublishController;
use App\Http\Controllers\Admin\Setting\ChangePasswordController;
use App\Http\Controllers\AuthController as MemberAuthController;
use App\Http\Controllers\ManualController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\TopController;
use Symfony\Component\Mime\MessageConverter;

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
Route::post('/member/logout', [MemberAuthController::class, 'logout'])->name('logout');

Route::get('/', [TopController::class, 'index'])->name('top')->middleware('auth');
Route::get('/search', [TopController::class, 'search'])->name('search')->middleware('auth');
Route::group(['prefix' => 'message', 'as' => 'message.', 'middleware' => 'auth'], function (){
    Route::get('/', [MessageController::class, 'index'])->name('index');
    Route::get('detail/{message_id}', [MessageController::class, 'detail'])->name('detail')->where('message_id', '^\d+$');
    Route::get('/search', [MessageController::class, 'search'])->name('search');
    Route::get('/crews', [MessageController::class, 'getCrews'])->name('get-crews');
    Route::post('/crews', [MessageController::class, 'putCrews'])->name('crews');
    Route::post('/reading', [MessageController::class, 'putReading'])->name('reading');
    Route::get('/crews-message', [MessageController::class, 'getCrewsMessage'])->name('crew-message');
    Route::post('/crews-logout', [MessageController::class, 'crewsLogout'])->name("crew-logout");
});
Route::group(['prefix' => 'manual', 'as' =>'manual.', 'middleware' => 'auth'], function () {
    Route::get('/', [ManualController::class, 'index'])->name('index');
    Route::get('detail/{manual_id}', [ManualController::class, 'detail'])->name('detail')->where('manual_id', '^\d+$');
    Route::put('/watched', [ManualController::class, 'watched'])->name('watched');
    Route::get('/search', [ManualController::class, 'search'])->name('search');
});

// 管理画面へのログイン画面
Route::get('/admin/auth', [AuthController::class, 'index'])->name('admin.auth');
Route::post('/admin/auth', [AuthController::class, 'login']);
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// 管理画面のルート
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'adminauth'], function() {
    // 管理画面-業務連絡
    Route::group(['prefix' => 'message', 'as' => 'message.', 'middleware' => 'check.allowpage:message'], function(){
        Route::group(['prefix' => 'publish', 'as' => 'publish.'], function(){
            Route::get('/', [MessagePublishController::class, 'index'])->name('index');
            Route::post('save-session-conditions', [MessagePublishController::class, 'saveSessionConditions'])->name('save-session-conditions');
            Route::post('save-search-conditions', [MessagePublishController::class, 'saveSearchConditions'])->name('save-search-conditions');
            Route::get('update-view-rates', [MessagePublishController::class, 'updateViewRates'])->name('update-view-rates');
            Route::get('/{message_id}', [MessagePublishController::class, 'show'])->name('show')->where('message_id', '^\d+$');
            Route::get('{organization1}/new', [MessagePublishController::class, 'new'])->name('new');
            Route::post('messageNewData/{organization1_id}', [MessagePublishController::class, 'messageNewData'])->name('messageNewData');
            Route::post('messageStoreData', [MessagePublishController::class, 'messageStoreData'])->name('messageStoreData');
            Route::post('{organization1}/new', [MessagePublishController::class, 'store'])->name('new.store');
            Route::get('edit/{message_id}', [MessagePublishController::class, 'edit'])->name('edit')->where('message_id', '^\d+$');
            Route::post('messageEditData/{message_id}/{organization1_id}', [MessagePublishController::class, 'messageEditData'])->name('messageEditData')->where('message_id', '^\d+$');
            Route::post('messageUpdateData', [MessagePublishController::class, 'messageUpdateData'])->name('messageUpdateData');
            Route::post('messageAllSaveData', [MessagePublishController::class, 'messageAllSaveData'])->name('messageAllSaveData');
            Route::post('edit/{message_id}', [MessagePublishController::class, 'update'])->name('edit.update')->where('message_id', '^\d+$');
            Route::post('stop', [MessagePublishController::class, 'stop'])->name('stop');
            Route::get('export/{message_id}', [MessagePublishController::class, 'export'])->name('export')->where('message_id', '^\d+$');
            Route::post('/upload', [MessagePublishController::class, 'fileUpload'])->name('fileUpload');
            Route::get('export-list', [MessagePublishController::class, 'exportList'])->name('export-list');
            Route::post('import', [MessagePublishController::class, 'Import'])->name('import');
            Route::post('/csv/upload', [MessagePublishController::class, 'csvUpload'])->name('csvUpload');
            Route::get('/csv/progress', [MessagePublishController::class, 'progress'])->name('progress');
            Route::post('/csv/store/export', [MessagePublishController::class, 'csvStoreExport'])->name('csvStoreExport');
            Route::post('/csv/store/upload', [MessagePublishController::class, 'csvStoreUpload'])->name('csvStoreUpload');
            Route::get('/csv/store/progress', [MessagePublishController::class, 'storeProgress'])->name('storeProgress');
            Route::post('/csv/store/import', [MessagePublishController::class, 'csvStoreImport'])->name('csvStoreImport');
            Route::post('/csv/store/all/import', [MessagePublishController::class, 'csvStoreAllImport'])->name('csvStoreAllImport');
        });
    });
    // 管理画面-動画マニュアル
    Route::group(['prefix' => 'manual', 'as' =>'manual.', 'middleware' => 'check.allowpage:manual'], function () {
        Route::group(['prefix' => 'publish', 'as' => 'publish.'], function () {
            Route::get('/', [ManualPublishController::class, 'index'])->name('index');
            Route::post('save-session-conditions', [ManualPublishController::class, 'saveSessionConditions'])->name('save-session-conditions');
            Route::post('save-search-conditions', [ManualPublishController::class, 'saveSearchConditions'])->name('save-search-conditions');
            Route::get('update-view-rates', [ManualPublishController::class, 'updateViewRates'])->name('update-view-rates');
            Route::get('/{manual_id}', [ManualPublishController::class, 'show'])->name('show')->where('manual_id', '^\d+$');
            Route::get('{organization1}/new', [ManualPublishController::class, 'new'])->name('new');
            Route::post('{organization1}/new', [ManualPublishController::class, 'store'])->name('new.store');
            Route::get('edit/{manual_id}', [ManualPublishController::class, 'edit'])->name('edit')->where('manual_id', '^\d+$');
            Route::post('edit/{manual_id}', [ManualPublishController::class, 'update'])->name('edit.update')->where('manual_id', '^\d+$');
            Route::post('/stop', [ManualPublishController::class, 'stop'])->name('stop');
            Route::get('export/{manual_id}', [ManualPublishController::class, 'export'])->name('export')->where('manual_id', '^\d+$');
            Route::post('/upload', [ManualPublishController::class, 'fileUpload'])->name('fileUpload');
            Route::get('export-list', [ManualPublishController::class, 'exportList'])->name('export-list');
            Route::post('import', [ManualPublishController::class, 'Import'])->name('import');
            Route::post('/csv/upload', [ManualPublishController::class, 'csvUpload'])->name('csvUpload');
            Route::get('/csv/progress', [ManualPublishController::class, 'progress'])->name('progress');
            Route::post('/csv/store/export', [ManualPublishController::class, 'csvStoreExport'])->name('csvStoreExport');
            Route::post('/csv/store/upload', [ManualPublishController::class, 'csvStoreUpload'])->name('csvStoreUpload');
            Route::get('/csv/store/progress', [ManualPublishController::class, 'storeProgress'])->name('storeProgress');
            Route::post('/csv/store/import', [ManualPublishController::class, 'csvStoreImport'])->name('csvStoreImport');
        });
    });
    Route::group(['prefix' => 'account', 'as' => 'account.'], function () {
        Route::group(['middleware' => 'check.allowpage:account-shop'], function(){
            Route::get('/', [AccountController::class, 'index'])->name('index');
            Route::post('save-session-conditions', [AccountController::class, 'saveSessionConditions'])->name('save-session-conditions');
            Route::get('new', [AccountController::class, 'new'])->name('new');
            Route::post('new', [AccountController::class, 'store'])->name('new.store');
            Route::post('/delete', [AccountController::class, 'delete'])->name('delete');
            Route::post('/wowtalkAlertUpdate', [AccountController::class, 'wowtalkAlertUpdate'])->name('wowtalkAlertUpdate');
            Route::get('/organization', [AccountController::class,  'getOrganization']);
            Route::get('/account-export', [AccountController::class, 'export'])->name('export');
        });
        Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'check.allowpage:account-admin'], function () {
            Route::get('/', [AdminAccountController::class, 'index'])->name('index');
            Route::get('new', [AdminAccountController::class, 'new'])->name('new');
            Route::post('new', [AdminAccountController::class, 'store'])->name('new.store');
            Route::get('edit/{admin}', [AdminAccountController::class, 'edit'])->name('edit');
            Route::post('edit/{admin}', [AdminAccountController::class, 'update'])->name('update');
        });
        Route::group(['prefix' => 'mail', 'as' => 'mail.', 'middleware' => 'check.allowpage:account-mail'], function () {
            Route::get('/', [MailAccountController::class, 'index'])->name('index');
            Route::post('save-session-conditions', [MailAccountController::class, 'saveSessionConditions'])->name('save-session-conditions');
            Route::post('/userRoleUpdate', [MailAccountController::class, 'userRoleUpdate'])->name('userRoleUpdate');
            Route::get('/organization', [MailAccountController::class,  'getOrganization']);
            Route::get('export', [MailAccountController::class, 'export'])->name('export');
        });
    });

    Route::group(['prefix' => 'setting', 'as' => 'setting.'], function () {
        Route::group(['prefix' => '/change_password', 'as' => 'change_password.'], function () {
        Route::get('/', [ChangePasswordController::class, 'index'])->name('index');
        Route::post('/', [ChangePasswordController::class, 'edit'])->name('edit');
        });
    });
    Route::group(['prefix' => 'manage', 'as' => 'manage', 'middleware' => 'check.allowpage:ims'], function () {
        Route::get('ims', [ImsController::class, 'index'])->name('index');
    });
    Route::group(['prefix' => 'analyse', 'as' =>'analyse.', 'middleware' => 'check.allowpage:message-analyse'], function () {
        Route::get('/personal', [PersonalContoller::class, 'index'])->name('index');
        Route::post('/personal/save-session-conditions', [PersonalContoller::class, 'saveSessionConditions'])->name('save-session-conditions');
        Route::post('/personal/save-search-conditions', [PersonalContoller::class, 'saveSearchConditions'])->name('save-search-conditions');
        Route::get('/personal-export', [PersonalContoller::class, 'export'])->name('export');
        Route::get('/personal/shop-message', [PersonalContoller::class,  'getShopMessageViewRate']);
        Route::get('/personal/org-message', [PersonalContoller::class,  'getOrgMessageViewRate']);
        Route::get('/personal/organization', [PersonalContoller::class,  'getOrganization']);
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
