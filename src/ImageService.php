<?php
namespace App;

use Imagick;

class ImageService
{
    public static function optimise(string $inputPath, string $outputPath): array
    {
        Config::loadEnv();

        $image = new Imagick($inputPath);

        // Fix rotation from EXIF
        $image->autoOrient();

        // Remove metadata (privacy + size)
        $image->stripImage();

        // Resize if too large
        $maxWidth = (int) Config::get('MAX_WIDTH', 2000);
        if ($image->getImageWidth() > $maxWidth) {
            $image->resizeImage(
                $maxWidth,
                0,
                Imagick::FILTER_LANCZOS,
                1
            );
        }

        // Convert to WebP
        $image->setImageFormat('webp');
        $image->setImageCompressionQuality(
            (int) Config::get('IMAGE_QUALITY', 75)
        );

        // Save optimised image
        $image->writeImage($outputPath);

        return [
            'width'  => $image->getImageWidth(),
            'height' => $image->getImageHeight(),
            'size'   => filesize($outputPath),
        ];
    }
}
