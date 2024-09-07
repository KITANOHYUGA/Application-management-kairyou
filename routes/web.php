<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::prefix('items')->middleware('auth')->group(function () {
    // 一覧画面
    Route::get('/', [App\Http\Controllers\ItemController::class, 'index']);
    // 検索画面
    // Route::get('/search', [App\Http\Controllers\ItemController::class, 'searchIndex']);
    // 登録機能
    Route::get('/add', [App\Http\Controllers\ItemController::class, 'add']);
    Route::post('/add', [App\Http\Controllers\ItemController::class, 'add']);
    // 編集画面遷移
    Route::get('/update/{id}', [App\Http\Controllers\ItemController::class, 'update']);
    // 編集処理
    Route::post('/updateItem/{id}', [App\Http\Controllers\ItemController::class, 'updateItem']);
    // 削除処理
    Route::delete('/deleteItem/{id}', [App\Http\Controllers\ItemController::class, 'delete']);
    // 検索処理
    Route::get('/search', [App\Http\Controllers\ItemController::class, 'search']);
});
