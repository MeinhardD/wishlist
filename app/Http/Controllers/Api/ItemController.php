<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Wishlist;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function store(Request $request)
    {
        try {
            $attributes = $request->validate([
                'unique_link' => ['required', 'string'],
                'password' => ['required', 'string'],
                'label' => ['required', 'string'],
                'icon' => ['image'],
                'link' => ['url'],
                'category' => ['string']
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Validation error',
                'success' => false,
                'exception' => $e->getMessage(),
            ]);
        }

        $random_string = $attributes['unique_link'];
        unset($attributes['unique_link']);
        $password = $attributes['password'];
        unset($attributes['password']);

        $wishlist = Wishlist::where('random_string', $random_string)->first();
        if (!$wishlist) {
            return response()->json([
                'message' => 'Could not find the wishlist',
                'success' => false,
            ]);
        }

        if (Hash::check($password, $wishlist->password)) {
            if ($icon = $request->file('icon')) {
                unset($attributes['icon']);
                $icon_name = explode('/', $icon->store('/public/images'))[2];
                $attributes['icon_name'] = $icon_name;
            }

            try {
                $item = $wishlist->items()->create($attributes);
            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Could not create item',
                    'success' => false,
                    'exception' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'message' => 'Created the item',
                'success' => true,
                'item' => $item,
            ]);
        }

        return response()->json([
            'message' => 'Wrong password',
            'success' => false,
        ]);
    }

    public function update(Request $request)
    {
        try {
            $attributes = $request->validate([
                'id' => ['required', 'numeric'],
                'password' => ['required', 'string'],
                'label' => ['string'],
                'icon' => ['image'],
                'link' => ['url'],
                'category' => ['string']
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Validation error',
                'success' => false,
            ]);
        }

        $id = $attributes['id'];
        unset($attributes['id']);
        $password = $attributes['password'];
        unset($attributes['password']);

        try {
            $item = Item::find($id);
        } catch (Exception) {
            return response()->json([
                'message' => 'Could not find the item',
                'success' => false,
            ]);
        }

        if (Hash::check($password, $item->wishlist->password)) {
            if ($icon = $request->file('icon')) {
                if ($item->icon_name) Storage::delete('/public/images/' . $item->icon_name);
                unset($attributes['icon']);
                $icon_name = explode('/', $icon->store('/public/images'))[2];
                $attributes['icon_name'] = $icon_name;
            }

            $item->update($attributes);
            unset($item->wishlist);
            return response()->json([
                'message' => 'Updated the item',
                'success' => true,
                'item' => $item,
            ]);
        }

        return response()->json([
            'message' => 'Wrong password',
            'success' => false,
        ]);
    }

    public function destroy($id, Request $request)
    {
        try {
            $password = $request->validate([
                'password' => ['required', 'string'],
            ])['password'];
        } catch (Exception $e) {
            return response()->json([
                'message' => 'A password is required',
                'success' => false,
            ]);
        }

        try {
            $item = Item::find($id);
        } catch (Exception) {
            return response()->json([
                'message' => 'Could not find the item',
                'success' => false,
            ]);
        }

        if ($item->icon_name) Storage::delete('/public/images/' . $item->icon_name);

        if (Hash::check($password, $item->wishlist->password)) {
            $item->delete();
            return response()->json([
                'message' => 'Deleted the item',
                'success' => true,
            ]);
        }

        return response()->json([
            'message' => 'Wrong password',
            'success' => false,
        ]);
    }
}
