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
use Foldergallery\Value\Item;

class ImageService
{
    /** @var array<string,string>|null */
    private $data = null;

    /** @return list<Item> */
    public function findItems(string $folder): ?array
    {
        $this->readImageData($folder);
        if (!is_dir($folder)) {
            return null;
        }
        $items = [];
        if (($dir = opendir($folder))) {
            while (($entry = readdir($dir)) !== false) {
                if ($entry[0] === ".") {
                    continue;
                }
                $filename = $folder . $entry;
                if (is_dir($filename)) {
                    $items[] = new Item($this->getCaption($entry), $folder . $entry);
                } elseif ($this->isImageFile($filename)) {
                    $items[] = $this->createImage($folder, $entry);
                }
            }
            closedir($dir);
        }
        usort($items, function ($a, $b) {
            return 2 * ($b->isFolder() - $a->isFolder()) + strnatcasecmp($a->filename(), $b->filename());
        });
        return $items;
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

    /** @return list<Image> */
    public function readFirstImagesIn(string $baseFolder, string $folder): array
    {
        $folder = $baseFolder . $folder . "/";
        $images = [];
        if (($dir = opendir($folder))) {
            while (($entry = readdir($dir)) !== false) {
                $filename = $folder  . $entry;
                if ($entry[0] !== "." && $this->isImageFile($filename)) {
                    if (!($image = $this->readImage($filename))) {
                        continue;
                    }
                    $images[] = $image;
                }
                if (count($images) === 2) {
                    break;
                }
            }
            closedir($dir);
        }
        return $images;
    }

    private function createImage(string $folder, string $entry): Item
    {
        $filename = "{$folder}{$entry}";
        $size = getimagesize($filename);
        assert($size !== false); // TODO invalid assertion
        list($width, $height) = $size;
        $size = [$width, $height];
        if (extension_loaded("exif") && ($exif = exif_read_data($filename))) {
            if (isset($exif["Orientation"]) && $exif["Orientation"] >= 5) {
                $size = [$height, $width];
            }
        }
        return new Item($this->getCaption($entry), $filename, $size);
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

    public function readImage(string $filename): ?Image
    {
        if (!($data = file_get_contents($filename))) {
            return null;
        }
        $orientation = 0;
        if (extension_loaded("exif") && ($exif = exif_read_data($filename))) {
            $orientation = $exif["Orientation"] ?? 0;
        }
        $icc = "";
        getimagesizefromstring($data, $info);
        if (isset($info["APP2"]) && !strncmp($info["APP2"], "ICC_PROFILE", strlen("ICC_PROFILE"))) {
            $icc = $info["APP2"];
        }
        return new Image($data, $orientation, $icc);
    }
}
