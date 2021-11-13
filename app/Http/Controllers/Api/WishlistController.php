<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class WishlistController extends Controller
{
    public function show(String $unique_link)
    {
        try {
            $wishlist = Wishlist::find(Crypt::decrypt($unique_link));
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Could not find the wishlist',
                'success' => false,
            ], 500);
        }

        return response()->json([
            'message' => 'Got the wishlist',
            'success' => true,
            'items' => $wishlist->items()
                ->orderBy('category', 'desc')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $attributes = $request->validate([
                'password' => ['required', 'string'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'A password is required',
                'success' => false,
            ], 500);
        }

        try {
            $id = Wishlist::create($attributes)->id;
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Could not create a wishlist',
                'success' => false,
            ], 500);
        }

        return response()->json([
            'message' => 'Created a new wishlist',
            'success' => true,
            'unique_link' => Crypt::encrypt($id),
        ]);
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'unique_link' => ['required', 'string'],
                'password' => ['required', 'string'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Validation error',
                'success' => false,
            ], 500);
        }

        // TODO: figure out how we want to update the wishlist
    }

    public function destroy($unique_link, Request $request)
    {
        try {
            $password = $request->validate([
                'password' => ['required', 'string'],
            ])['password'];
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Password required',
                'success' => false,
            ], 500);
        }

        try {
            $wishlist = Wishlist::find(Crypt::decrypt($unique_link));
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Could not find the wishlist',
                'success' => false,
            ], 500);
        }

        if (Hash::check($password, $wishlist->password)) {
            $wishlist->delete();
            return response()->json([
                'message' => 'Deleted the wishlist',
                'success' => true,
            ]);
        }

        return response()->json([
            'message' => 'Wrong password',
            'success' => false,
        ], 500);
    }
}
