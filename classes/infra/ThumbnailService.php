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

use GdImage;

class ThumbnailService
{
    /** @var int */
    private $folderBackground;

    public function __construct(int $folderBackground)
    {
        $this->folderBackground = $folderBackground;
    }

    /** @param list<string> $images */
    public function makeFolderThumbnail(array $images, int $dstHeight): string
    {
        $dst = imagecreatetruecolor($dstHeight, $dstHeight);
        assert($dst !== false); // TODO invalid assertion?
        imagefilledrectangle($dst, 0, 0, $dstHeight - 1, $dstHeight - 1, $this->folderBackground);
        foreach ($images as $i => $data) {
            $src = imagecreatefromstring($data);
            assert($src !== false);  // TODO invalid assertion
            $this->copyResizedAndCropped($dst, $src, $i);
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

    public function makeThumbnail(string $data, int $dstHeight): string
    {
        $size = getimagesizefromstring($data);
        assert($size !== false); // TODO invalid assertion
        list($srcWidth, $srcHeight, $type) = $size;
        $dstWidth = (int) round($srcWidth / $srcHeight * $dstHeight);
        if (
            $dstWidth > $srcWidth || $dstHeight > $srcHeight
            || $dstWidth == $srcWidth && $dstHeight == $srcHeight
            || $type != IMAGETYPE_JPEG
        ) {
            return $data;
        }
        if (!($srcImage = imagecreatefromstring($data))) {
            return $data;
        }
        if (!($dstImage = $this->resize($srcImage, $srcWidth, $srcHeight, $dstWidth, $dstHeight))) {
            return $data;
        }
        return $this->jpegData($dstImage);
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
