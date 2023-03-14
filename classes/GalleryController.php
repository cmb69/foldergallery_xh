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

    /** @var array<string,string> */
    private $lang;

    /** @var string */
    private $pageUrl;

    /**
     * @param string $basefolder
     */
    public function __construct($basefolder)
    {
        global $pth, $plugin_tx, $sn;

        $this->basefolder = "{$pth['folder']['images']}$basefolder/";
        $this->currentSubfolder = $this->getCurrentSubfolder();
        $this->lang = $plugin_tx['foldergallery'];
        $this->pageUrl = $sn . ($_SERVER["QUERY_STRING"] ? "?" . $_SERVER["QUERY_STRING"] : "");
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

    /** @return void */
    public function indexAction()
    {
        global $pth, $plugin_cf, $plugin_tx;

        $frontend = $plugin_cf['foldergallery']['frontend'];
        $this->{"include$frontend"}();
        $imageService = new ImageService("{$this->basefolder}{$this->currentSubfolder}", new ThumbnailService);
        $children = $imageService->findEntries();
        foreach ($children as $child) {
            if ($child->isDir) {
                $folder = "{$this->currentSubfolder}{$child->basename}";
                $child->url = $this->urlWithFoldergallery($folder);
            }
        }
        $view = new View($pth["folder"]["plugins"] . "foldergallery/views/", $plugin_tx["foldergallery"]);
        echo $view->render("gallery", [
            'breadcrumbs' => $this->getBreadcrumbs(),
            'children' => $children
        ]);
    }

    /** @return void */
    private function includePhotoswipe()
    {
        global $hjs, $bjs, $pth, $plugin_tx;

        $hjs .= sprintf(
            '<link rel="stylesheet" href="%sfoldergallery/lib/photoswipe/photoswipe.css">',
            $pth['folder']['plugins']
        );
        $hjs .= sprintf(
            '<link rel="stylesheet" href="%sfoldergallery/lib/photoswipe/default-skin/default-skin.css">',
            $pth['folder']['plugins']
        );
        $hjs .= sprintf(
            '<script src="%sfoldergallery/lib/photoswipe/photoswipe.min.js"></script>',
            $pth['folder']['plugins']
        );
        $hjs .= sprintf(
            '<script src="%sfoldergallery/lib/photoswipe/photoswipe-ui-default.min.js"></script>',
            $pth['folder']['plugins']
        );
        ob_start();
        $view = new View($pth["folder"]["plugins"] . "foldergallery/views/", $plugin_tx["foldergallery"]);
        echo $view->render("photoswipe", []);
        $bjs .= ob_get_clean();
        $filename = "{$pth['folder']['plugins']}foldergallery/foldergallery.min.js";
        if (!file_exists($filename)) {
            $filename = "{$pth['folder']['plugins']}foldergallery/foldergallery.js";
        }
        $bjs .= sprintf('<script src="%s"></script>', $filename);
    }

    /** @return void */
    private function includeColorbox()
    {
        global $pth, $hjs, $bjs;

        include_once "{$pth['folder']['plugins']}jquery/jquery.inc.php";
        include_jquery();
        $colorboxFolder = "{$pth['folder']['plugins']}foldergallery/colorbox/";
        include_jqueryplugin('colorbox', "{$colorboxFolder}jquery.colorbox-min.js");
        $hjs .= '<link rel="stylesheet" href="' . $colorboxFolder . 'colorbox.css" type="text/css">';
        $config = array('rel' => 'foldergallery_group', 'maxWidth' => '100%', 'maxHeight' => '100%');
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

    /** @return array<object> */
    private function getBreadcrumbs()
    {
        $breadcrumbs = (new BreadcrumbService($this->currentSubfolder))->getBreadcrumbs();
        foreach ($breadcrumbs as $i => $breadcrumb) {
            if ($i < count($breadcrumbs) - 1) {
                if (isset($breadcrumb->url)) {
                    $breadcrumb->url = $this->urlWithFoldergallery($breadcrumb->url);
                } else {
                    $breadcrumb->url = $this->urlWithoutFoldergallery();
                }
                $breadcrumb->isLink = true;
            } else {
                $breadcrumb->url = null;
                $breadcrumb->isLink = false;
            }
        }
        return $breadcrumbs;
    }

    /** @param string $value */
    private function urlWithFoldergallery($value): string
    {
        return $this->urlWithoutFoldergallery() . "&foldergallery_folder=" . urlencode($value);
    }

    private function urlWithoutFoldergallery(): string
    {
        return preg_replace('/&foldergallery_folder=[^&]+/', "", $this->pageUrl);
    }
}
