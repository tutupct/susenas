<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Laravel\Facades\Image;

class ImageCompressionService
{
    /**
     * @param int      $quality   Kualitas JPEG (0-100). 90-95 = "visually lossless", nyaris tidak
     *                            terlihat bedanya dengan aslinya tapi ukuran tetap turun signifikan.
     *                            Jangan naik ke 100 — itu cuma bikin file lebih besar tanpa manfaat kasat mata.
     * @param int|null $maxWidth  Lebar maksimal. Kalau gambar lebih lebar, akan di-scale down. Null = tidak di-resize.
     */
    public function __construct(
        protected int $quality = 90,
        protected ?int $maxWidth = 1920,
    ) {}

    /**
     * Kompres UploadedFile (tetap format JPEG) lalu simpan ke disk, return path relatif-nya.
     */
    public function compressAndStore(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $image = Image::decode($file);

        // Resize kalau lebih lebar dari batas, tanpa distorsi (tinggi menyesuaikan otomatis)
        if ($this->maxWidth !== null && $image->width() > $this->maxWidth) {
            $image->scale(width: $this->maxWidth);
        }

        // strip: true -> buang metadata EXIF/ICC (lossless, tidak pengaruh ke tampilan gambar)
        $encoded = $image->encode(new JpegEncoder(quality: $this->quality, strip: true));

        $filename = Str::uuid() . '.jpg';
        $path = trim($directory, '/') . '/' . $filename;

        Storage::disk($disk)->put($path, (string) $encoded);

        return $path;
    }
}
