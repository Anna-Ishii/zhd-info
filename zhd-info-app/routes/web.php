<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\Message\MessagePublishController;

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
Route::get('/auth', [AuthController::class, 'index']);
Route::post('/auth', [AuthController::class, 'login']);

Route::get('/admin/message/publish', [MessagePublishController::class, 'index'])->name('massage.publish');
// Rotue::get('/admin/message/manage', )
// Rotue::get('/admin/manual/publish', )
Route::match(['get', 'post'], '/admin/message/publish/new', [MessagePublishController::class, 'new'])->name('message.publish.new');