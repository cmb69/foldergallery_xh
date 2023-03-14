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

class ImageService
{
    /**
     * @var string
     */
    private $folder;

    /**
     * @var ThumbnailService
     */
    private $thumbnailService;

    /**
     * @var int
     */
    private $thumbSize;

    /**
     * @var ?array<mixed>
     */
    private $data;

    /**
     * @param string $folder
     */
    public function __construct($folder, ThumbnailService $thumbnailService)
    {
        global $plugin_cf;

        $this->folder = $folder;
        $this->thumbnailService = $thumbnailService;
        $this->thumbSize = $plugin_cf['foldergallery']['thumb_size'];
        $this->data = null;
    }

    /**
     * @return object[]
     */
    public function findEntries()
    {
        $this->readImageData();
        $result = [];
        foreach (scandir($this->folder) as $entry) {
            if (strpos($entry, '.') === 0) {
                continue;
            }
            $filename = "{$this->folder}{$entry}";
            if (is_dir($filename)) {
                $result[] = $this->createDir($entry);
            } elseif ($this->isImageFile($filename)) {
                $result[] = $this->createImage($entry);
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
    private function readImageData()
    {
        global $sl;

        if (is_readable("{$this->folder}foldergallery.php")) {
            $data = include "{$this->folder}foldergallery.php";
            if (isset($data[$sl])) {
                $this->data = $data[$sl];
            }
        }
    }

    /**
     * @param string $entry
     * @return object
     */
    private function createDir($entry)
    {
        $filename = "{$this->folder}{$entry}";
        $images = $this->firstImagesIn($entry);
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
            'filename' => "{$this->folder}{$entry}",
            'thumbnail' => $thumbnail,
            'srcset' => $srcset,
            'isDir' => true
        );
    }

    /**
     * @param string $folder
     * @return string[]
     */
    private function firstImagesIn($folder)
    {
        $folder = "{$this->folder}$folder/";
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
    private function createImage($entry)
    {
        $caption = $this->getCaption($entry);
        $filename = "{$this->folder}{$entry}";
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
