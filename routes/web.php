<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsAdmin;

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

// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();

// ホーム画面のリダイレクト設定
Route::get('/home', function() {
    return redirect('/items');
});

// ログイン画面
// この部分が不要であれば削除
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);

Route::prefix('items')->middleware('auth')->group(function () {
    // 一覧画面
    Route::get('/', [App\Http\Controllers\ItemController::class, 'index'])->name('items.index');
    Route::get('/reset', [App\Http\Controllers\ItemController::class, 'reset'])->name('items.reset');
    // 検索画面でのクリアボタン処理
    Route::get('/searchReset', [App\Http\Controllers\ItemController::class, 'searchReset'])->name('items.searchReset');
    // 検索処理
    Route::get('/search', [App\Http\Controllers\ItemController::class, 'search'])->name('items.search');

    Route::post('/save-selected', [App\Http\Controllers\ItemController::class, 'saveSelected']);
    Route::get('/get-selected', [App\Http\Controllers\ItemController::class, 'getSelected']);
    Route::post('/delete-selected', [App\Http\Controllers\ItemController::class, 'deleteSelected'])->name('items.deleteSelected');
    Route::post('/items/clear-selection', [App\Http\Controllers\ItemController::class, 'clearSelection'])->name('items.clearSelection');
    Route::post('/save-selected-items', [App\Http\Controllers\ItemController::class, 'saveSelectedItems'])->name('items.saveSelectedItems');


    // 管理者専用機能（登録、編集、削除）に IsAdmin ミドルウェアを適用
    Route::middleware(IsAdmin::class)->group(function () {
    
    // 登録機能
    Route::get('/add', [App\Http\Controllers\ItemController::class, 'add']);
    Route::post('/add', [App\Http\Controllers\ItemController::class, 'add']);

    // 編集画面遷移
    Route::get('/update/{id}', [App\Http\Controllers\ItemController::class, 'update']);

    // 編集処理
    Route::post('/updateItem/{id}', [App\Http\Controllers\ItemController::class, 'updateItem']);

    // 削除処理
    Route::delete('/deleteItem/{id}', [App\Http\Controllers\ItemController::class, 'delete'])->name('items.delete');
    Route::match(['get', 'post'], '/show-selected', [App\Http\Controllers\ItemController::class, 'showSelected'])->name('items.showSelected');

});
});
Route::middleware(IsAdmin::class)->group(function () {

Route::get('/users/list', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
Route::get('/users/{id}/edit', [App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
Route::put('/users/{id}', [App\Http\Controllers\UserController::class, 'update'])->name('users.update');
Route::delete('/users/{id}', [App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
Route::post('/users/bulk-delete', [App\Http\Controllers\UserController::class, 'bulkDelete'])->name('users.bulkDelete');
// 検索処理
Route::get('/users/search', [App\Http\Controllers\UserController::class, 'userSearch'])->name('users.search');
// クリアボタン
Route::post('/users/clear-selection', [App\Http\Controllers\UserController::class, 'clearSelection'])->name('users.clearSelection');
});