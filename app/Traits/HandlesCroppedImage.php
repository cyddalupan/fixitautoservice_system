<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HandlesCroppedImage
{
    /**
     * Save a cropped image from base64 data or file upload.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $croppedInputField The hidden input name (e.g., 'cropped_image')
     * @param string $fileInputField The file input name (e.g., 'profile_photo')
     * @param string $storageDir Subdirectory under storage/app/public/
     * @param int $size Square output size in px
     * @param int $quality JPEG quality 0-100
     * @return string|null Relative storage path, or null if no image
     */
    protected function saveCroppedImage($request, $croppedInputField = 'cropped_image', $fileInputField = 'profile_photo', $storageDir = 'profile-photos', $size = 300, $quality = 85)
    {
        // Priority 1: cropped base64 data from the crop modal
        if ($request->filled($croppedInputField)) {
            $base64 = $request->input($croppedInputField);
            return $this->saveBase64Image($base64, $storageDir, $size, $quality);
        }

        // Priority 2: raw file upload (no cropping performed)
        if ($request->hasFile($fileInputField)) {
            $filename = $this->generateFilename();
            $img = $this->loadImage($request->file($fileInputField)->getRealPath());
            if (!$img) return null;

            $stored = $this->resizeAndSaveImage($img, $storageDir, $filename, $size, $quality);
            imagedestroy($img);
            return $stored;
        }

        return null;
    }

    /**
     * Decode a base64 data URL, crop/resize to a 1:1 square, and save.
     */
    protected function saveBase64Image($base64Data, $storageDir, $size, $quality)
    {
        // Strip data URL prefix if present (e.g., "data:image/jpeg;base64,...")
        if (strpos($base64Data, 'base64,') !== false) {
            $base64Data = substr($base64Data, strpos($base64Data, 'base64,') + 7);
        }

        $decoded = base64_decode($base64Data);
        if ($decoded === false) return null;

        $img = @imagecreatefromstring($decoded);
        if (!$img) return null;

        $filename = $this->generateFilename();
        $stored = $this->resizeAndSaveImage($img, $storageDir, $filename, $size, $quality);
        imagedestroy($img);
        return $stored;
    }

    /**
     * Load an image file path into a GD resource.
     */
    protected function loadImage($path)
    {
        $info = @getimagesize($path);
        if (!$info) return null;

        switch ($info[2]) {
            case IMAGETYPE_JPEG: return @imagecreatefromjpeg($path);
            case IMAGETYPE_PNG:  return @imagecreatefrompng($path);
            case IMAGETYPE_WEBP: return @imagecreatefromwebp($path);
            case IMAGETYPE_GIF:  return @imagecreatefromgif($path);
            default: return null;
        }
    }

    /**
     * Resize (center-crop to 1:1 square) and save as JPEG.
     */
    protected function resizeAndSaveImage($src, $storageDir, $filename, $size, $quality)
    {
        $srcW = imagesx($src);
        $srcH = imagesy($src);

        // Center crop to 1:1
        $minDim = min($srcW, $srcH);
        $srcX = (int)(($srcW - $minDim) / 2);
        $srcY = (int)(($srcH - $minDim) / 2);

        $dst = imagecreatetruecolor($size, $size);
        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $size, $size, $minDim, $minDim);

        $storagePath = "$storageDir/$filename.jpg";
        Storage::disk('public')->makeDirectory($storageDir);

        $fullPath = Storage::disk('public')->path($storagePath);
        imagejpeg($dst, $fullPath, $quality);
        imagedestroy($dst);

        return $storagePath;
    }

    /**
     * Generate a unique filename.
     */
    protected function generateFilename()
    {
        return time() . '_' . strtolower(\Illuminate\Support\Str::random(16));
    }

    /**
     * Delete a stored image file.
     */
    protected function deleteStoredImage($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
