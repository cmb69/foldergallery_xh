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
            $filename = "{$this->basefolder}{$this->currentSubfolder}$child";
            if (is_dir($filename)) {
                $html .= '<div class="foldergallery_folder">'
                    . '<a href="' . "$sn?$su" . '&foldergallery_folder=' . XH_hsc("{$this->currentSubfolder}$child") . '">'
                    . '<img src="' . XH_hsc($pth['folder']['plugins']) . 'foldergallery/images/folder.png">'
                    . '</a>'
                    . '<div>' . XH_hsc($child) . '</div>'
                    . '</div>';
            } elseif ($this->isImageFile($filename)) {
                $html .= '<div class="foldergallery_image">'
                    . '<a href="' . XH_hsc($filename) . '">'
                    . '<img src="' . XH_hsc($filename) . '">'
                    . '</a>'
                    . '<div>' . XH_hsc($child) . '</div>'
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
     * @return string[]
     */
    private function findChildren()
    {
        $entries = scandir("{$this->basefolder}{$this->currentSubfolder}");
        natcasesort($entries);
        return array_filter($entries, function ($entry) {
            return strpos($entry, '.') !== 0;
        });
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
