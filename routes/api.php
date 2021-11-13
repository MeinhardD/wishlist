<?php

use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\WishlistController;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

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

Route::post('/unlock', function (Request $request) {
    try {
        $request->validate([
            'unique_link' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'Both the unique link and a password are required',
            'success' => false,
        ]);
    }

    try {
        $wishlist = Wishlist::find(Crypt::decrypt($request->unique_link));
    } catch (Exception $e) {
        return response()->json([
            'message' => 'Could not find the wishlist to unlock',
            'success' => false,
        ]);
    }

    if (Hash::check($request->password, $wishlist->password)) {
        return response()->json([
            'message' => 'Unlocked the wishlist',
            'success' => true,
        ]);
    }

    return response()->json([
        'message' => 'Wrong password',
        'success' => false,
    ]);
});
