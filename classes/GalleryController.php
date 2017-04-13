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
    private $currentSubfolder;

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $lang;

    /**
     * @var Url
     */
    private $pageUrl;

    /**
     * @param string $basefolder
     */
    public function __construct($basefolder)
    {
        global $sn, $pth, $plugin_cf, $plugin_tx;

        $this->basefolder = "{$pth['folder']['images']}$basefolder/";
        $this->currentSubfolder = $this->getCurrentSubfolder();
        $this->config = $plugin_cf['foldergallery'];
        $this->lang = $plugin_tx['foldergallery'];
        $this->pageUrl = new Url($sn, $_GET);
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
        global $pth;

        $this->includeColorbox();
        $view = new View('gallery');
        $view->breadcrumbs = $this->getBreadcrumbs();
        $imageService = new ImageService("{$this->basefolder}{$this->currentSubfolder}", new ThumbnailService);
        $children = $imageService->findEntries();
        foreach ($children as $child) {
            if ($child->isDir) {
                $folder = "{$this->currentSubfolder}{$child->basename}";
                $child->url = $this->pageUrl->with('foldergallery_folder', $folder);
            }
        }
        $view->children = $children;
        $view->folderImage = "{$pth['folder']['plugins']}foldergallery/images/folder.{$this->config['icon_format']}";
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
     * @return string
     */
    private function getBreadcrumbs()
    {
        $breadcrumbs = (new BreadcrumbService($this->currentSubfolder))->getBreadcrumbs();
        foreach ($breadcrumbs as $i => $breadcrumb) {
            if ($i < count($breadcrumbs) - 1) {
                if (isset($breadcrumb->url)) {
                    $breadcrumb->url = $this->pageUrl->with('foldergallery_folder', $breadcrumb->url);
                } else {
                    $breadcrumb->url = $this->pageUrl->without('foldergallery_folder');
                }
            } else {
                $breadcrumb->url = null;
            }
        }
        return $breadcrumbs;
    }
}
