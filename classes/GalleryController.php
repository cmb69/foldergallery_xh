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

class GalleryController
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
     * @var array
     */
    private $lang;

    /**
     * @param string $basefolder
     */
    public function __construct($basefolder)
    {
        global $pth, $plugin_tx;

        $this->basefolder = "{$pth['folder']['images']}$basefolder/";
        $this->currentSubfolder = $this->getCurrentSubfolder();
        $this->lang = $plugin_tx['foldergallery'];
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

    public function indexAction()
    {
        global $pth, $sn, $su;

        $this->includeColorbox();
        $view = new View('gallery');
        $view->locator = new HtmlString($this->showLocator());
        $view->children = $this->findChildren();
        $view->folderImage = "{$pth['folder']['plugins']}foldergallery/images/folder.png";
        $pageName = html_entity_decode($su, ENT_QUOTES, 'UTF-8');
        $view->urlPrefix = "$sn?$pageName&foldergallery_folder={$this->currentSubfolder}";
        $view->render();
    }

    private function includeColorbox()
    {
        global $pth, $hjs, $bjs;

        include_once "{$pth['folder']['plugins']}jquery/jquery.inc.php";
        include_jquery();
        $colorboxFolder = "{$pth['folder']['plugins']}foldergallery/colorbox/";
        include_jqueryplugin('colorbox', "{$colorboxFolder}jquery.colorbox-min.js");
        $hjs .= '<link rel="stylesheet" href="' . $colorboxFolder . 'colorbox.css" type="text/css">';
        $config = array('rel' => 'foldergallery_group');
        foreach ($this->lang as $key => $value) {
            if (strpos($key, 'colorbox_') === 0) {
                $config[substr($key, strlen('colorbox_'))] = $value;
            }
        }
        $config = json_encode($config);
        $bjs .= <<<SCRIPT
<script>
jQuery(function ($) {
    $(".foldergallery_group").colorbox($config);
});
</script>
SCRIPT;
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
        return '<div class="foldergallery_locator">' . implode(XH_hsc($this->lang['locator_separator']), $parts) . '</div>';
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
        array_unshift($parts, (object) array('name' => XH_hsc($this->lang['locator_start'])));
        return $parts;
    }
}
