<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * Store image with duplicate name protection
     * (Same name will not be overwritten)
     */
    public static function store(UploadedFile $file, string $folder): string
    {
        self::validate($file);

        $safeName = self::safeName($file);
        $path = $folder . '/' . $safeName;

        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->putFileAs($folder, $file, $safeName);
        }

        return $path;
    }

    /**
     * Store image without duplicate name protection
     */
    public static function storeUnsafe(UploadedFile $file, string $folder): string
    {
        self::validate($file);

        // Laravel will generate a random hashed filename
        return $file->store($folder, 'public');
    }

    /**
     * Delete image safely
     */
    public static function delete(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            return true;
        }

        return false;
    }

    /**
     * Validate uploaded file
     */
    private static function validate(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new \Exception('Invalid image upload');
        }
    }

    /**
     * Generate safe filename
     */
    private static function safeName(UploadedFile $file): string
    {
        $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $ext = $file->getClientOriginalExtension();

        return Str::slug($original) . '.' . $ext;
    }
}
