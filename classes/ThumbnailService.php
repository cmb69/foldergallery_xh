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

namespace Foldergallery;

use stdClass;

class ThumbnailService
{
    /**
     * @var string
     */
    private $cache;

    public function __construct()
    {
        global $pth;

        $this->cache = "{$pth['folder']['plugins']}foldergallery/cache/";
    }

    /**
     * @param string $srcPath
     * @param string $dstHeight
     * @return string
     */
    public function makeThumbnail($srcPath, $dstHeight)
    {
        list($srcWidth, $srcHeight, $type) = getimagesize($srcPath);
        $dstWidth = round($srcWidth / $srcHeight * $dstHeight);
        if ($dstWidth > $srcWidth || $dstHeight > $srcHeight
            || $dstWidth == $srcWidth && $dstHeight == $srcHeight
            || $type != IMG_JPEG
        ) {
            return $srcPath;
        }
        $dstPath = $this->cache . sha1("$srcPath.$dstWidth.$dstHeight") . '.jpg';
        if (!file_exists($dstPath)) {
            return $this->doMakeThumbnail(
                (object) ['path' => $srcPath, 'width' => $srcWidth, 'height' => $srcHeight],
                (object) ['path' => $dstPath, 'width' => $dstWidth, 'height' => $dstHeight]
            );
        }
        return $dstPath;
    }

    /**
     * @return string
     */
    private function doMakeThumbnail(stdClass $src, stdClass $dst)
    {
        if (!(($src->image = imagecreatefromjpeg($src->path))
            && $this->resize($src, $dst)
            && imagejpeg($dst->image, $dst->path))
        ) {
            return $src->path;
        }
        return $dst->path;
    }

    /**
     * @return bool
     */
    private function resize(stdClass $src, stdClass $dst)
    {
        if (!($dst->image = imagecreatetruecolor($dst->width, $dst->height))) {
            return false;
        }
        return imagecopyresampled(
            $dst->image,
            $src->image,
            0,
            0,
            0,
            0,
            $dst->width,
            $dst->height,
            $src->width,
            $src->height
        );
    }
}
