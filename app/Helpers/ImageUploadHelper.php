<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class ImageUploadHelper
{
    public static function uploadBase64Image(
        $base64Image,
        $folder = 'uploads',
        $oldFile = null
    ) {

        if (!$base64Image) {
            return null;
        }

        // already URL/path
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            return $base64Image;
        }

        $imageType = strtolower($type[1]);

        if (!in_array($imageType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            throw new \Exception('Invalid image type');
        }

        $image = substr($base64Image, strpos($base64Image, ',') + 1);

        $image = base64_decode($image);

        if (!$image) {
            throw new \Exception('Invalid base64 image');
        }

        // create folder
        $directory = public_path($folder);

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // delete old file
        if ($oldFile) {

            $oldPath = public_path($oldFile);

            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        // unique file
        $filename =
            Str::uuid() . '.' . $imageType;

        $relativePath =
            $folder . '/' . $filename;

        $fullPath =
            public_path($relativePath);

        file_put_contents($fullPath, $image);

        return $relativePath;
    }
}