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

    /** @var array<string,string>|null */
    private $data;

    public function __construct(int $thumbSize, ThumbnailService $thumbnailService)
    {
        $this->thumbSize = $thumbSize;
        $this->thumbnailService = $thumbnailService;
        $this->data = null;
    }

    /** @return array<array{caption:string,basename:?string,filename:string,thumbnail:string,srcset:string,isDir:bool,size:?string}> */
    public function findEntries(string $folder): array
    {
        $this->readImageData($folder);
        $result = [];
        $entries = scandir($folder);
        assert($entries !== false); // TODO invalid assertion
        foreach ($entries as $entry) {
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
            return 2 * ($b["isDir"] - $a["isDir"]) + strnatcasecmp($a["filename"], $b["filename"]);
        });
        return $result;
    }

    /** @return void */
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

    /** @return array{caption:string,basename:string,filename:string,thumbnail:string,srcset:string,isDir:bool,size:null} */
    private function createDir(string $folder, string $entry): array
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
        return [
            'caption' => $this->getCaption($entry),
            'basename' => $entry,
            'filename' => "{$folder}{$entry}",
            'thumbnail' => $thumbnail,
            'srcset' => $srcset,
            'isDir' => true,
            "size" => null,
        ];
    }

    /** @return list<string> */
    private function firstImagesIn(string $baseFolder, string $folder): array
    {
        $folder = "{$baseFolder}$folder/";
        $result = [];
        $entries = scandir($folder);
        assert($entries !== false); // TODO invalid assertion
        foreach ($entries as $basename) {
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

    /** @return array{caption:string,basename:null,filename:string,thumbnail:string,srcset:string,isDir:bool,size:string} */
    private function createImage(string $folder, string $entry): array
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
        $size = getimagesize($filename);
        assert($size !== false); // TODO invalid assertion
        list($width, $height) = $size;
        $size = "{$width}x{$height}";
        return [
            "caption" => $caption,
            "basename" => null,
            "filename" => $filename,
            "thumbnail" => $thumbnail,
            "srcset" => $srcset,
            "isDir" => $isDir,
            "size" => $size,
        ];
    }

    private function getCaption(string $entry): string
    {
        if (isset($this->data[$entry])) {
            return $this->data[$entry];
        } else {
            return pathinfo($entry, PATHINFO_FILENAME);
        }
    }

    private function isImageFile(string $filename): bool
    {
        return is_file($filename)
            && in_array(pathinfo($filename, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'JPG', 'JPEG']);
    }
}
