<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function show($image_name)
    {
        $path = '/public/images/' . $image_name;

        if (Storage::exists($path)) {
            return $this->getImage($path);
        }

        return abort(404);
    }

    private function getImage(String $path)
    {
        $content = Storage::get($path);
        $mime = Storage::mimeType($path);

        $response = Response::make($content, 200);
        $response->header('Content-Type', $mime);

        return $response;
    }
}
