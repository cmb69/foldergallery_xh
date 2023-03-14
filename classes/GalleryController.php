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

use Foldergallery\Infra\ImageService;
use Foldergallery\Infra\View;
use Foldergallery\Logic\Util;

class GalleryController
{
    /** @var string */
    private $pluginFolder;

    /** @var array<string,string> */
    private $conf;

    /** @var array<string,string> */
    private $text;

    /** @var ImageService */
    private $imageService;

    /** @var View */
    private $view;

    /**
     * @param array<string,string> $conf
     * @param array<string,string> $text
     */
    public function __construct(string $pluginFolder, array $conf, array $text, ImageService $imageService, View $view)
    {
        $this->pluginFolder = $pluginFolder;
        $this->conf = $conf;
        $this->text = $text;
        $this->imageService = $imageService;
        $this->view = $view;
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

    public function __invoke(string $pageUrl, string $basefolder): string
    {
        $frontend = $this->conf['frontend'];
        $this->{"include$frontend"}();
        $children = $this->imageService->findEntries("{$basefolder}{$this->getCurrentSubfolder()}");
        foreach ($children as &$child) {
            if ($child["isDir"]) {
                $folder = "{$this->getCurrentSubfolder()}{$child["basename"]}";
                $child["url"] = $this->urlWithFoldergallery($pageUrl, $folder);
            }
        }
        return $this->view->render("gallery", [
            'breadcrumbs' => $this->getBreadcrumbs($pageUrl),
            'children' => $children
        ]);
    }

    /** @return void */
    private function includePhotoswipe()
    {
        global $hjs, $bjs;

        $hjs .= sprintf(
            '<link rel="stylesheet" href="%slib/photoswipe/photoswipe.css">',
            $this->pluginFolder
        );
        $hjs .= sprintf(
            '<link rel="stylesheet" href="%slib/photoswipe/default-skin/default-skin.css">',
            $this->pluginFolder
        );
        $hjs .= sprintf(
            '<script src="%slib/photoswipe/photoswipe.min.js"></script>',
            $this->pluginFolder
        );
        $hjs .= sprintf(
            '<script src="%slib/photoswipe/photoswipe-ui-default.min.js"></script>',
            $this->pluginFolder
        );
        ob_start();
        echo $this->view->render("photoswipe", []);
        $bjs .= ob_get_clean();
        $filename = "{$this->pluginFolder}foldergallery.min.js";
        if (!file_exists($filename)) {
            $filename = "{$this->pluginFolder}foldergallery.js";
        }
        $bjs .= sprintf('<script src="%s"></script>', $filename);
    }

    /** @return void */
    private function includeColorbox()
    {
        global $hjs, $bjs;

        include_once "{$this->pluginFolder}../jquery/jquery.inc.php";
        include_jquery();
        $colorboxFolder = "{$this->pluginFolder}colorbox/";
        include_jqueryplugin('colorbox', "{$colorboxFolder}jquery.colorbox-min.js");
        $hjs .= '<link rel="stylesheet" href="' . $colorboxFolder . 'colorbox.css" type="text/css">';
        $config = array('rel' => 'foldergallery_group', 'maxWidth' => '100%', 'maxHeight' => '100%');
        foreach ($this->text as $key => $value) {
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

    /** @return list<array{name:string,url:string,isLink:bool}> */
    private function getBreadcrumbs(string $pageUrl)
    {
        $records = [];
        $breadcrumbs = Util::breadcrumbs($this->getCurrentSubfolder(), $this->text['locator_start']);
        foreach ($breadcrumbs as $i => $breadcrumb) {
            $record = [];
            $record["name"] = $breadcrumb["name"];
            if ($i < count($breadcrumbs) - 1) {
                if (isset($breadcrumb["url"])) {
                    $record["url"] = $this->urlWithFoldergallery($pageUrl, $breadcrumb["url"]);
                } else {
                    $record["url"] = $this->urlWithoutFoldergallery($pageUrl);
                }
                $record["isLink"] = true;
            } else {
                $record["url"] = null;
                $record["isLink"] = false;
            }
            $records[] = $record;
        }
        return $records;
    }

    /** @param string $value */
    private function urlWithFoldergallery(string $pageUrl, $value): string
    {
        return $this->urlWithoutFoldergallery($pageUrl) . "&foldergallery_folder=" . urlencode($value);
    }

    private function urlWithoutFoldergallery(string $pageUrl): string
    {
        return preg_replace('/&foldergallery_folder=[^&]+/', "", $pageUrl);
    }
}
