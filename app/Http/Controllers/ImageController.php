<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function show($image_name)
    {
        try {
            $image = Storage::get('/public/images/' . $image_name);
        } catch (Exception $e) {
            return abort(404);
        }

        return $image;
    }
}
