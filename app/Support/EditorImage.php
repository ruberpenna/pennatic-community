<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Images inserted into post bodies through the editor.
 *
 * Stored on the public disk under post-images/ and referenced by a relative /storage/... URL,
 * which is the only image source the "post" purifier profile keeps.
 */
class EditorImage
{
    public const MAX_KB = 5120;

    public const TYPES = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /** Wider images are scaled down to this width. */
    public const MAX_WIDTH = 1600;

    public const DIRECTORY = 'post-images';

    /**
     * @return list<mixed>
     */
    public static function rules(): array
    {
        return ['required', 'image', 'mimes:'.implode(',', self::TYPES), 'max:'.self::MAX_KB, 'dimensions:max_width=12000,max_height=12000'];
    }

    /**
     * Store the upload and return the URL to put in the post body.
     *
     * Everything except GIF is decoded and re-encoded, which drops metadata (EXIF location etc.)
     * and anything that is not image data. GIFs are kept as-is so animations survive; the
     * "image" validation rule has already checked they decode as images.
     */
    public static function store(UploadedFile $file): string
    {
        $type = strtolower($file->guessExtension() ?? '');
        $type = $type === 'jpeg' ? 'jpg' : $type;
        $path = self::DIRECTORY.'/'.now()->format('Y/m').'/'.Str::uuid().'.'.$type;

        $bytes = $type === 'gif'
            ? file_get_contents($file->getRealPath())
            : self::reencode($file->getRealPath(), $type);

        Storage::disk('public')->put($path, $bytes);

        return '/storage/'.$path;
    }

    private static function reencode(string $source, string $type): string
    {
        $image = imagecreatefromstring(file_get_contents($source));

        if ($image === false) {
            throw new \RuntimeException('Unreadable image.');
        }

        if ($type === 'jpg') {
            $image = self::applyExifOrientation($image, $source);
        }

        if (imagesx($image) > self::MAX_WIDTH) {
            $scaled = imagescale($image, self::MAX_WIDTH, -1, IMG_BICUBIC);
            imagedestroy($image);
            $image = $scaled;
        }

        if ($type !== 'jpg') {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        ob_start();
        match ($type) {
            'png' => imagepng($image, null, 6),
            'webp' => imagewebp($image, null, 85),
            default => imagejpeg($image, null, 85),
        };
        imagedestroy($image);

        return ob_get_clean();
    }

    /**
     * Phone photos store rotation in EXIF, which re-encoding drops; rotate the pixels instead.
     */
    private static function applyExifOrientation(\GdImage $image, string $source): \GdImage
    {
        $orientation = (int) (@exif_read_data($source)['Orientation'] ?? 1);

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        if ($rotated) {
            imagedestroy($image);

            return $rotated;
        }

        return $image;
    }
}
