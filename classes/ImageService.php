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
     * @param string $folder
     */
    public function __construct($folder)
    {
        $this->folder = $folder;
    }

    /**
     * @return object[]
     */
    public function findEntries()
    {
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
        usort($result, array($this, 'compareEntries'));
        return $result;
    }

    private function createDir($entry)
    {
        return (object) array(
            'name' => $entry,
            'basename' => $entry,
            'filename' => "{$this->folder}{$entry}",
            'isDir' => true
        );
    }

    private function createImage($entry)
    {
        return (object) array(
            'name' => pathinfo($entry, PATHINFO_FILENAME),
            'basename' => $entry,
            'filename' => "{$this->folder}{$entry}",
            'isDir' => false
        );
    }

    /**
     * @param string $filename
     * @return bool
     */
    private function isImageFile($filename)
    {
        return is_file($filename) && strpos(mime_content_type($filename), 'image/') === 0;
    }

    /**
     * @return int
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function compareEntries(stdClass $a, stdClass $b)
    {
        return 2 * ($b->isDir - $a->isDir) + strnatcasecmp($a->name, $b->name);
    }
}
