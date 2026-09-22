<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;

class ImageCompressionService
{
    /**
     * @param int      $quality   Kualitas WebP (0-100). 75-85 biasanya "hampir mirip" tapi jauh lebih kecil.
     * @param int|null $maxWidth  Lebar maksimal. Kalau gambar lebih lebar, akan di-scale down. Null = tidak di-resize.
     */
    public function __construct(
        protected int $quality = 80,
        protected ?int $maxWidth = 1920,
    ) {}

    /**
     * Kompres UploadedFile jadi WebP lalu simpan ke disk, return path relatif-nya.
     */
    public function compressAndStore(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        // decode() menggantikan read() sejak Intervention Image v4
        $image = Image::decode($file);

        // Resize kalau lebih lebar dari batas, tanpa distorsi (tinggi menyesuaikan otomatis)
        if ($this->maxWidth !== null && $image->width() > $this->maxWidth) {
            $image->scale(width: $this->maxWidth);
        }

        // Di v4, shortcut seperti toWebp() sudah dihapus, gantinya pakai encoder object
        $encoded = $image->encode(new WebpEncoder(quality: $this->quality));

        $filename = Str::uuid() . '.webp';
        $path = trim($directory, '/') . '/' . $filename;

        Storage::disk($disk)->put($path, (string) $encoded);

        return $path;
    }
}
