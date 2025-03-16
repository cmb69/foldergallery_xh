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
    public function makeFolderThumbnail1(array $images, int $dstHeight): string
    {
        $dst = imagecreatetruecolor($dstHeight, $dstHeight);
        assert($dst !== false);
        imagefilledrectangle($dst, 0, 0, $dstHeight - 1, $dstHeight - 1, $this->folderBackground);
        foreach ($images as $i => $image) {
            if (!($src = imagecreatefromstring($image->data()))) {
                continue;
            }
            if (!($src = $this->normalize($src, $image->orientation()))) {
                continue;
            }
            $this->copyResizedAndCropped($dst, $src, $i);
        }
        return $this->jpegData($dst);
    }

    /** @param list<Image> $images */
    public function makeFolderThumbnail(array $images, int $dstHeight): string
    {
        $folder = $this->folderBackground;
        $red = $folder >> 16 & 0xff;
        $green = $folder >> 8 & 0xff;
        $blue = $folder & 0xff;
        $folderDark = (int) (0.9 * $red) << 16 | (int) (0.9 * $green) << 8 | (int) (0.9 * $blue);

        $size = $dstHeight;

        $matrixes = [
            [0.7, 0.15, 0, 1, 0, 0],
            [0.55, 0.30, 0, 0.9, 0, 0],
        ];
        $offsets = [(int) (0.3125 * $size), (int) (0.234375 * $size)];

        $im1 = imagecreatetruecolor($size, $size);
        assert($im1 !== false);
        imagealphablending($im1, false);
        imagefilledrectangle($im1, 0, 0, $size - 1, $size - 1, 0x7f000000);
        imagealphablending($im1, true);
        imagefilledrectangle(
            $im1,
            (int) (0.1953125 * $size),
            (int) (0.0234375 * $size),
            (int) (0.703125 * $size),
            (int) (0.8046875 * $size),
            $folderDark
        );

        foreach ($images as $i => $image) {
            if (!($im2 = imagecreatefromstring($image->data()))) {
                continue;
            }
            if (!($im2 = $this->normalize($im2, $image->orientation()))) {
                continue;
            }
            $w = imagesx($im2);
            $h = imagesy($im2);
            if ($w > $h) {
                $im2 = imagecrop($im2, ["x" => intdiv($w - $h, 2), "y" => 0, "width" => $h, "height" => $h]);
            } else {
                $im2 = imagecrop($im2, ["x" => 0, "y" => intdiv($h - $w, 2), "width" => $w, "height" => $w]);
            }
            assert($im2 !== false); // TODO invalid assertion?
            $im2 = imagescale($im2, (int) (0.78125 * $size));
            assert($im2 !== false); // TODO invalid assertion?
            $im2 = imageaffine($im2, $matrixes[$i]);
            assert($im2 !== false); // TODO invalid assertion?
            $w = imagesx($im2);
            assert($w !== false);
            $h = imagesy($im2);
            assert($h !== false);
            imagecopy($im1, $im2, $offsets[$i], (int) (0.0546875 * $size), 0, 0, $w, $h);
        }

        $points = [
            (int) (0.1953125 * $size), (int) (0.0234375 * $size),
            (int) (0.390625 * $size), (int) (0.1796875 * $size),
            (int) (0.390625 * $size), (int) (0.9609375 * $size),
            (int) (0.1953125 * $size), (int) (0.8046875 * $size),
        ];
        if (PHP_MAJOR_VERSION >= 8) {
            imagefilledpolygon($im1, $points, $folder); // @phpstan-ignore-line
        } else {
            imagefilledpolygon($im1, $points, 4, $folder);
        }
        imagesavealpha($im1, true);
        return $this->pngData($im1);
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

    public function makeThumbnail(Image $image, int $dstHeight, float $ratio): string
    {
        if (!($srcImage = imagecreatefromstring($image->data()))) {
            return $image->data();
        }
        $srcWidth = imagesx($srcImage);
        assert($srcWidth !== false);
        $srcHeight = imagesy($srcImage);
        assert($srcHeight !== false);
        if ($image->orientation() >= 5) {
            $temp = $srcWidth;
            $srcWidth = $srcHeight;
            $srcHeight = $temp;
        }
        $dstWidth = (int) round($srcWidth / $srcHeight * $dstHeight);
        if ($dstWidth > $srcWidth || $dstHeight > $srcHeight || $dstWidth === $srcWidth && $dstHeight === $srcHeight) {
            return $image->data();
        }
        if (!($srcImage = $this->normalize($srcImage, $image->orientation()))) {
            return $image->data();
        }
        if (!($dstImage = $this->resize($srcImage, $srcWidth, $srcHeight, $dstWidth, $dstHeight, $ratio))) {
            return $image->data();
        }
        return $this->embedIcc($this->jpegData($dstImage), $image->icc());
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
                imageflip($image, IMG_FLIP_HORIZONTAL);
                return $image;
            case 3:
                return imagerotate($image, 180, 0) ?: null;
            case 4:
                imageflip($image, IMG_FLIP_VERTICAL);
                return $image;
            case 5:
                imageflip($image, IMG_FLIP_VERTICAL);
                return imagerotate($image, 270, 0) ?: null;
            case 6:
                return imagerotate($image, 270, 0) ?: null;
            case 7:
                imageflip($image, IMG_FLIP_VERTICAL);
                return imagerotate($image, 90, 0) ?: null;
            case 8:
                return imagerotate($image, 90, 0) ?: null;
        }
    }

    /**
     * @param GdImage $srcImage
     * @return GdImage|null
     */
    private function resize($srcImage, int $w1, int $h1, int $w2, int $h2, float $ratio)
    {
        $w2 = (int) round($h2 * $ratio);
        $dstImage = imagecreatetruecolor($w2, $h2);
        assert($dstImage !== false);
        if ($ratio < ($w1 / $h1)) {
            $w = (int) round($h1 * $ratio);
            $sx = (int) round(($w1 - $w) / 2);
            $sy = 0;
            $sw = $w1 - (int) round(($w1 - $w));
            $sh = $h1;
        } else {
            $h = (int) round($w1 / $ratio);
            $sx = 0;
            $sy = (int) round(($h1 - $h) / 2);
            $sw = $w1;
            $sh = $h1 - (int) round(($h1 - $h));
        }
        $success = imagecopyresampled(
            $dstImage,
            $srcImage,
            0,
            0,
            $sx,
            $sy,
            $w2,
            $h2,
            $sw,
            $sh
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

    /** @param GdImage $image */
    private function pngData($image): string
    {
        ob_start();
        imagepng($image);
        return (string) ob_get_clean();
    }

    private function embedIcc(string $data, string $icc): string
    {
        if (!$icc) {
            return $data;
        }
        $pos = 0;
        do {
            $un = unpack("a2marker/nlength", $data, $pos);
            if (!$un) {
                return $data;
            }
            if ($un["marker"] === "\xff\xd8") {
                $pos += 2;
            } elseif ($un["marker"] === "\xff\xe0") {
                $pos += $un["length"] + 2;
            }
        } while (in_array($un["marker"], ["\xff\xd8", "\xff\xe0"], true));
        return substr($data, 0, $pos) . "\xff\xe2" . pack("n", strlen($icc) + 2) . $icc . substr($data, $pos);
    }
}
