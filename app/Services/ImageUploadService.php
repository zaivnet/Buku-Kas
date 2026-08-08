<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class ImageUploadService
{
    /**
     * Store and compress proof image.
     * Scale max width to 1200px, compress to 80% JPEG quality.
     * Path structure: storage/app/public/proofs/{tahun}/{bulan}/{uuid}.jpg
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return string Relative path inside disk public
     */
    public function store(UploadedFile $file, string $folder = 'proofs'): string
    {
        $year = date('Y');
        $month = date('m');
        $uuid = (string) Str::uuid();
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $extension = 'jpg';
        }

        $filename = "{$uuid}.{$extension}";
        $relativePath = "{$folder}/{$year}/{$month}/{$filename}";

        // Instantiate ImageManager dengan GD Driver
        $manager = new ImageManager(new Driver());
        $img = $manager->decodePath($file->getRealPath());

        if ($img->width() > 1200) {
            $img->scale(width: 1200);
        }

        // Encode dengan kualitas 80%
        $encoded = match ($extension) {
            'png'  => $img->encode(new PngEncoder()),
            'webp' => $img->encode(new WebpEncoder(quality: 80)),
            default => $img->encode(new JpegEncoder(quality: 80)),
        };

        // Simpan ke storage disk 'public'
        Storage::disk('public')->put($relativePath, (string) $encoded);

        return $relativePath;
    }

    /**
     * Delete proof image file from storage if exists.
     *
     * @param string|null $relativePath
     * @return void
     */
    public function delete(?string $relativePath): void
    {
        if ($relativePath && Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }
}
