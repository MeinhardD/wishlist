<?php

use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

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

Route::get('/wishlists/{unique_link}', [WishlistController::class, 'show']);
Route::post('/wishlists', [WishlistController::class, 'store']);
Route::delete('/wishlists/{unique_link}', [WishlistController::class, 'destroy']);

Route::post('/items/store', [ItemController::class, 'store']);
Route::post('/items/update', [ItemController::class, 'update']);
Route::delete('/items/{id}', [ItemController::class, 'destroy']);