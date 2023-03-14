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

use Foldergallery\Infra\ThumbnailService;

class ImageService
{
    /** @var int */
    private $thumbSize;

    /** @var ThumbnailService */
    private $thumbnailService;

    /**
     * @var ?array<mixed>
     */
    private $data;

    public function __construct(int $thumbSize, ThumbnailService $thumbnailService)
    {
        $this->thumbSize = $thumbSize;
        $this->thumbnailService = $thumbnailService;
        $this->data = null;
    }

    /**
     * @return object[]
     */
    public function findEntries(string $folder)
    {
        $this->readImageData($folder);
        $result = [];
        foreach (scandir($folder) as $entry) {
            if (strpos($entry, '.') === 0) {
                continue;
            }
            $filename = "{$folder}{$entry}";
            if (is_dir($filename)) {
                $result[] = $this->createDir($folder, $entry);
            } elseif ($this->isImageFile($filename)) {
                $result[] = $this->createImage($folder, $entry);
            }
        }
        usort($result, function ($a, $b) {
            return 2 * ($b->isDir - $a->isDir) + strnatcasecmp($a->filename, $b->filename);
        });
        return $result;
    }

    /**
     * @return void
     */
    private function readImageData(string $folder)
    {
        global $sl;

        if (is_readable("{$folder}foldergallery.php")) {
            $data = include "{$folder}foldergallery.php";
            if (isset($data[$sl])) {
                $this->data = $data[$sl];
            }
        }
    }

    /**
     * @param string $entry
     * @return object
     */
    private function createDir(string $folder, $entry)
    {
        $filename = "{$folder}{$entry}";
        $images = $this->firstImagesIn($folder, $entry);
        $srcset = '';
        $thumbnail = $this->thumbnailService->makeFolderThumbnail($filename, $images, $this->thumbSize);
        $srcset .= "$thumbnail 1x";
        foreach (range(2, 3) as $i) {
            $thumb = $this->thumbnailService->makeFolderThumbnail($filename, $images, $i * $this->thumbSize);
            $srcset .= ", $thumb {$i}x";
        }
        return (object) array(
            'caption' => $this->getCaption($entry),
            'basename' => $entry,
            'filename' => "{$folder}{$entry}",
            'thumbnail' => $thumbnail,
            'srcset' => $srcset,
            'isDir' => true
        );
    }

    /**
     * @param string $folder
     * @return string[]
     */
    private function firstImagesIn(string $baseFolder, $folder)
    {
        $folder = "{$baseFolder}$folder/";
        $result = [];
        foreach (scandir($folder) as $basename) {
            $filename = "{$folder}$basename";
            if ($basename[0] !== '.' && $this->isImageFile($filename)) {
                $result[] = $basename;
            }
            if (count($result) === 2) {
                break;
            }
        }
        return $result;
    }

    /**
     * @param string $entry
     * @return object
     */
    private function createImage(string $folder, $entry)
    {
        $caption = $this->getCaption($entry);
        $filename = "{$folder}{$entry}";
        $srcset = '';
        $thumbnail = $this->thumbnailService->makeThumbnail($filename, $this->thumbSize);
        if ($thumbnail !== $filename) {
            $srcset .= "$thumbnail 1x";
            foreach (range(2, 3) as $i) {
                $thumb = $this->thumbnailService->makeThumbnail($filename, $i * $this->thumbSize);
                if ($thumb !== $filename) {
                    $srcset .= ", $thumb {$i}x";
                }
            }
        }
        $isDir = false;
        list($width, $height) = getimagesize($filename);
        $size = "{$width}x{$height}";
        return (object) compact('caption', 'filename', 'thumbnail', 'srcset', 'isDir', 'size');
    }

    /**
     * @param string $entry
     * @return string
     */
    private function getCaption($entry)
    {
        if (isset($this->data[$entry])) {
            return $this->data[$entry];
        } else {
            return pathinfo($entry, PATHINFO_FILENAME);
        }
    }

    /**
     * @param string $filename
     * @return bool
     */
    private function isImageFile($filename)
    {
        return is_file($filename)
            && in_array(pathinfo($filename, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'JPG', 'JPEG']);
    }
}
