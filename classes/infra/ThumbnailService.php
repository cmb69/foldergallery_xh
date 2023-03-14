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
    /** @var string */
    private $cacheFolder;

    /** @var int */
    private $folderBackground;

    public function __construct(string $cacheFolder, int $folderBackground)
    {
        $this->cacheFolder = $cacheFolder;
        $this->folderBackground = $folderBackground;
    }

    /**
     * @param string $srcPath
     * @param string[] $images
     * @param int $dstHeight
     * @return string
     */
    public function makeFolderThumbnail($srcPath, array $images, $dstHeight)
    {
        $dstPath = $this->cacheFolder . sha1("$srcPath." . implode('.', $images) . ".$dstHeight") . '.jpg';
        $dst = imagecreatetruecolor($dstHeight, $dstHeight);
        imagefilledrectangle($dst, 0, 0, $dstHeight - 1, $dstHeight - 1, $this->folderBackground);
        foreach ($images as $i => $basename) {
            $this->copyResizedAndCropped($dst, "$srcPath/$basename", $i);
        }
        imagejpeg($dst, $dstPath);
        return $dstPath;
    }

    /**
     * @param resource|GdImage $im
     * @param string $filename
     * @param int $index
     * @return void
     */
    private function copyResizedAndCropped($im, $filename, $index)
    {
        $im2 = imagecreatefromjpeg($filename);
        $w = imagesx($im2);
        $h = imagesy($im2);
        $d = $w - $h;
        if ($d >= 0) {
            $sx = round($d / 2);
            $sy = 0;
            $sw = $w - $d;
            $sh = $h;
        } else {
            $d = abs($d);
            $sx = 0;
            $sy = round($d / 2);
            $sw = $w;
            $sh = $h - $d;
        }
        $size = imagesx($im);
        $dx = $dy = ($index * 5 + 1)/16 * $size;
        $dw = $dh = (int) round(9/16 * $size);
        imagecopyresampled($im, $im2, $dx, $dy, $sx, $sy, $dw, $dh, $sw, $sh);
    }

    /**
     * @param string $srcPath
     * @param int $dstHeight
     * @return string
     */
    public function makeThumbnail($srcPath, $dstHeight)
    {
        list($srcWidth, $srcHeight, $type) = getimagesize($srcPath);
        $dstWidth = (int) round($srcWidth / $srcHeight * $dstHeight);
        if ($dstWidth > $srcWidth || $dstHeight > $srcHeight
            || $dstWidth == $srcWidth && $dstHeight == $srcHeight
            || $type != IMG_JPEG
        ) {
            return $srcPath;
        }
        $dstPath = $this->cacheFolder . sha1("$srcPath.$dstWidth.$dstHeight") . '.jpg';
        if (!file_exists($dstPath)) {
            return $this->doMakeThumbnail(
                ['path' => $srcPath, 'width' => $srcWidth, 'height' => $srcHeight],
                ['path' => $dstPath, 'width' => $dstWidth, 'height' => $dstHeight]
            );
        }
        return $dstPath;
    }

    /**
     * @param array{path:string,width:int,height:int} $src
     * @param array{path:string,width:int,height:int} $dst
     * @return string
     */
    private function doMakeThumbnail(array $src, array $dst)
    {
        if (!(($srcImage = imagecreatefromjpeg($src["path"]))
            && ($dstImage = $this->resize($srcImage, $src, $dst))
            && imagejpeg($dstImage, $dst["path"]))
        ) {
            return $src["path"];
        }
        return $dst["path"];
    }

    /**
     * @param GdImage $srcImage
     * @param array{path:string,width:int,height:int} $src
     * @param array{path:string,width:int,height:int} $dst
     * @return GdImage|null
     */
    private function resize($srcImage, array $src, array $dst)
    {
        if (!($dstImage = imagecreatetruecolor($dst["width"], $dst["height"]))) {
            return null;
        }
        $success = imagecopyresampled(
            $dstImage,
            $srcImage,
            0,
            0,
            0,
            0,
            $dst["width"],
            $dst["height"],
            $src["width"],
            $src["height"]
        );
        return $success ? $dstImage : null;
    }
}
