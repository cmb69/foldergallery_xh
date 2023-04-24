<?php

/**
 * Copyright 2017 Christoph M. Becker
 *
 * This file is part of Foldergallery_XH.
 *
 * Foldergallery_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Foldergallery_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foldergallery_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foldergallery\Infra;

use Foldergallery\Value\Image;
use GdImage;

class ThumbnailService
{
    /** @var int */
    private $folderBackground;

    public function __construct(int $folderBackground)
    {
        $this->folderBackground = $folderBackground;
    }

    /** @param list<Image> $images */
    public function makeFolderThumbnail(array $images, int $dstHeight): string
    {
        $dst = imagecreatetruecolor($dstHeight, $dstHeight);
        assert($dst !== false); // TODO invalid assertion?
        imagefilledrectangle($dst, 0, 0, $dstHeight - 1, $dstHeight - 1, $this->folderBackground);
        foreach ($images as $i => $image) {
            $src = imagecreatefromstring($image->data());
            assert($src !== false);  // TODO invalid assertion
            if (($src = $this->normalize($src, $image->orientation()))) {
                $this->copyResizedAndCropped($dst, $src, $i);
            }
        }
        return $this->jpegData($dst);
    }

    /**
     * @param GdImage $im
     * @param GdImage $src
     * @return void
     */
    private function copyResizedAndCropped($im, $src, int $index)
    {
        $w = (int) imagesx($src);
        $h = (int) imagesy($src);
        $d = $w - $h;
        if ($d >= 0) {
            $sx = (int) round($d / 2);
            $sy = 0;
            $sw = $w - $d;
            $sh = $h;
        } else {
            $d = abs($d);
            $sx = 0;
            $sy = (int) round($d / 2);
            $sw = $w;
            $sh = $h - $d;
        }
        $size = imagesx($im);
        $dx = $dy = ($index * 5 + 1) / 16 * $size;
        $dw = $dh = (int) round(9 / 16 * $size);
        imagecopyresampled($im, $src, $dx, $dy, $sx, $sy, $dw, $dh, $sw, $sh);
    }

    public function makeThumbnail(Image $image, int $dstHeight): string
    {
        $size = getimagesizefromstring($image->data());
        assert($size !== false); // TODO invalid assertion
        list($srcWidth, $srcHeight, $type) = $size;
        if ($image->orientation() >= 5) {
            $temp = $srcWidth;
            $srcWidth = $srcHeight;
            $srcHeight = $temp;
        }
        $dstWidth = (int) round($srcWidth / $srcHeight * $dstHeight);
        if (
            $dstWidth > $srcWidth || $dstHeight > $srcHeight
            || $dstWidth == $srcWidth && $dstHeight == $srcHeight
            || $type != IMAGETYPE_JPEG
        ) {
            return $image->data();
        }
        if (!($srcImage = imagecreatefromstring($image->data()))) {
            return $image->data();
        }
        if (!($srcImage = $this->normalize($srcImage, $image->orientation()))) {
            return $image->data();
        }
        if (!($dstImage = $this->resize($srcImage, $srcWidth, $srcHeight, $dstWidth, $dstHeight))) {
            return $image->data();
        }
        return $this->jpegData($dstImage);
    }

    /**
     * @param GdImage $image
     * @return GdImage|null
     */
    private function normalize($image, int $orientation)
    {
        switch ($orientation) {
            default:
                return $image;
            case 2:
                if (!imageflip($image, IMG_FLIP_HORIZONTAL)) {
                    return null;
                }
                return $image;
            case 3:
                return imagerotate($image, 180, 0) ?: null;
            case 4:
                if (!imageflip($image, IMG_FLIP_VERTICAL)) {
                    return null;
                }
                return $image;
            case 5:
                if (!imageflip($image, IMG_FLIP_VERTICAL)) {
                    return null;
                }
                return imagerotate($image, 270, 0) ?: null;
            case 6:
                return imagerotate($image, 270, 0) ?: null;
            case 7:
                if (!imageflip($image, IMG_FLIP_VERTICAL)) {
                    return null;
                }
                return imagerotate($image, 90, 0) ?: null;
            case 8:
                return imagerotate($image, 90, 0) ?: null;
        }
    }

    /**
     * @param GdImage $srcImage
     * @return GdImage|null
     */
    private function resize($srcImage, int $w1, int $h1, int $w2, int $h2)
    {
        if (!($dstImage = imagecreatetruecolor($w2, $h2))) {
            return null;
        }
        $success = imagecopyresampled(
            $dstImage,
            $srcImage,
            0,
            0,
            0,
            0,
            $w2,
            $h2,
            $w1,
            $h1
        );
        return $success ? $dstImage : null;
    }

    /** @param GdImage $image */
    private function jpegData($image): string
    {
        ob_start();
        imagejpeg($image);
        return (string) ob_get_clean();
    }
}
