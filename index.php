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

class Foldergallery
{
    /**
     * @var string
     */
    private $basefolder;

    /**
     * @var string
     */
    private $subfolder;

    /**
     * @param string $basefolder
     */
    public function __construct($basefolder)
    {
        global $pth;

        $this->basefolder = "{$pth['folder']['images']}$basefolder/";
        $this->currentSubfolder = $this->getCurrentSubfolder();
    }

    /**
     * @return string
     */    
    private function getCurrentSubfolder()
    {
        if (!isset($_GET['foldergallery_folder'])) {
            return '';
        }
        return preg_replace(array('/\\\\/', '/\.{1,2}\//'), '', "{$_GET['foldergallery_folder']}/");
    }

    /**
     * @return string
     */
    public function show()
    {
        global $pth, $sn, $su;

        $html = '<div class="foldergallery">';
        $html .= $this->showLocator();
        $children = $this->findChildren();
        foreach ($children as $child) {
            if ($child->isDir) {
                $html .= '<div class="foldergallery_folder">'
                    . '<a href="' . "$sn?$su" . '&foldergallery_folder=' . XH_hsc("{$this->currentSubfolder}{$child->basename}") . '">'
                    . '<img src="' . XH_hsc($pth['folder']['plugins']) . 'foldergallery/images/folder.png">'
                    . '</a>'
                    . '<div>' . XH_hsc($child->name) . '</div>'
                    . '</div>';
            } else {
                $html .= '<div class="foldergallery_image">'
                    . '<a href="' . XH_hsc($child->filename) . '">'
                    . '<img src="' . XH_hsc($child->filename) . '">'
                    . '</a>'
                    . '<div>' . XH_hsc($child->name) . '</div>'
                    . '</div>';
            }
        }
        $html .= '</div>';
        return $html;
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
     * @return object[]
     */
    private function findChildren()
    {
        $result = [];
        $entries = scandir("{$this->basefolder}{$this->currentSubfolder}");
        foreach ($entries as $entry) {
            if (strpos($entry, '.') === 0) {
                continue;
            }
            $filename = "{$this->basefolder}{$this->currentSubfolder}$entry";
            $isDir = is_dir($filename);
            if (!$isDir && !$this->isImageFile($filename)) {
                continue;
            }
            $results[] = (object) array(
                'name' => $isDir ? $entry : pathinfo($entry, PATHINFO_FILENAME),
                'basename' => $entry,
                'filename' => $filename,
                'isDir' => $isDir
            );
        }
        usort($results, function ($a, $b) {
            return 2 * ($b->isDir - $a->isDir) + strnatcasecmp($a->name, $b->name);
        });
        return $results;
    }

    /**
     * @return string
     */
    private function showLocator()
    {
        global $sn, $su;

        $parts = array();
        $breadcrumbs = $this->getBreadcrumbs();
        foreach ($breadcrumbs as $i => $breadcrumb) {
            $url = "$sn?$su" . (isset($breadcrumb->url) ? XH_hsc("&foldergallery_folder={$breadcrumb->url}") : '');
            if ($i < count($breadcrumbs) - 1) {
                $part = '<a href="' . $url . '">' . $breadcrumb->name . '</a>';
            } else {
                $part = $breadcrumb->name;
            }
            $parts[] = $part;
        }
        return '<div class="foldergallery_locator">' . implode(' > ', $parts) . '</div>';
    }

    /**
     * @return object[]
     */
    private function getBreadcrumbs()
    {
        $parts = explode('/', $this->currentSubfolder);
        array_pop($parts);
        $url = '';
        foreach ($parts as &$part) {
            $url .= "$part/";
            $part = (object) array('name' => $part, 'url' => rtrim($url, '/'));
        }
        array_unshift($parts, (object) array('name' => 'Home'));
        return $parts;
    }
}

/**
 * @param string $basefolder
 * @return string
 */
function foldergallery($basefolder = '')
{
    $gallery = new Foldergallery($basefolder);
    return $gallery->show();
}
