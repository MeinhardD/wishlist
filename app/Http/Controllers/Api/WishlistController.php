<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class WishlistController extends Controller
{
    public function show(String $unique_link)
    {
        $wishlist = Wishlist::where('random_string', $unique_link)->first();

        if (!$wishlist) {
            return response()->json([
                'message' => 'Could not find the wishlist',
                'success' => false,
            ]);
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
                'exception' => $e->getMessage(),
            ]);
        }

        $random_string = Str::random();
        try {
            Wishlist::create([...$attributes, 'random_string' => $random_string]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Could not create a wishlist',
                'success' => false,
                'exception' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'Created a new wishlist',
            'success' => true,
            'unique_link' => $random_string,
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
                'exception' => $e->getMessage(),
            ]);
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
                'exception' => $e->getMessage(),
            ]);
        }

        try {
            $wishlist = Wishlist::where('random_string', $unique_link)->firstOrFail();
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Could not find the wishlist',
                'success' => false,
                'exception' => $e->getMessage(),
            ]);
        }

        foreach ($wishlist->items as $item) {
            if ($item->icon_name) Storage::delete('/public/images/' . $item->icon_name);
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
        ]);
    }
}
