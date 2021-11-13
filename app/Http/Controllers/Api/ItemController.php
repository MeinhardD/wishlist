<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Wishlist;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
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
            ]);
        }

        $wishlist_id = Crypt::decrypt($attributes['unique_link']);
        unset($attributes['unique_link']);
        $attributes['wishlist_id'] = $wishlist_id;
        $password = $attributes['password'];
        unset($attributes['password']);

        if (Hash::check($password, Wishlist::find($wishlist_id)->password)) {
            if ($icon = $request->file('icon')) {
                unset($attributes['icon']);
                $icon_name = explode('/', $icon->store('/public/images'))[2];
                $attributes['icon_name'] = $icon_name;
            }

            try {
                $item = Item::create($attributes);
            } catch (Exception) {
                return response()->json([
                    'message' => 'Could not create item',
                    'success' => false,
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
            ], 500);
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
            ], 500);
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
        ], 500);
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
