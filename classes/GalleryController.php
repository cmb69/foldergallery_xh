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

use Exception;
use Foldergallery\Infra\ImageService;
use Foldergallery\Infra\Jquery;
use Foldergallery\Infra\Response;
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

    /** @var Jquery */
    private $jquery;

    /** @var View */
    private $view;

    /**
     * @param array<string,string> $conf
     * @param array<string,string> $text
     */
    public function __construct(
        string $pluginFolder,
        array $conf,
        array $text,
        ImageService $imageService,
        Jquery $jquery,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->conf = $conf;
        $this->text = $text;
        $this->imageService = $imageService;
        $this->jquery = $jquery;
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

    public function __invoke(string $pageUrl, string $basefolder): Response
    {
        $children = $this->imageService->findEntries("{$basefolder}{$this->getCurrentSubfolder()}");
        foreach ($children as &$child) {
            if ($child["isDir"]) {
                $folder = "{$this->getCurrentSubfolder()}{$child["basename"]}";
                $child["url"] = $this->urlWithFoldergallery($pageUrl, $folder);
            }
        }
        return $this->initializeFrontEnd($this->conf["frontend"])
            ->withOutput($this->view->render("gallery", [
                'breadcrumbs' => $this->getBreadcrumbs($pageUrl),
                'children' => $children
            ]));
    }

    private function initializeFrontEnd(string $frontEnd): Response
    {
        switch ($frontEnd) {
            case "Photoswipe":
                return $this->includePhotoswipe();
            case "Colorbox":
                return $this->includeColorbox();
            default:
                return Response::create($this->view->error("error_frontend", $frontEnd));
        }
    }

    private function includePhotoswipe(): Response
    {
        $photoswipeFolder = $this->pluginFolder . "lib/photoswipe/";
        $hjs = "<link rel=\"stylesheet\" href=\"{$photoswipeFolder}photoswipe.css\">\n"
            . "<link rel=\"stylesheet\" href=\"{$photoswipeFolder}default-skin/default-skin.css\">\n"
            . "<script src=\"{$photoswipeFolder}photoswipe.min.js\"></script>\n"
            . "<script src=\"{$photoswipeFolder}photoswipe-ui-default.min.js\"></script>\n";
        $bjs = $this->view->render("photoswipe", []);
        $filename = "{$this->pluginFolder}foldergallery.min.js";
        if (!file_exists($filename)) {
            $filename = "{$this->pluginFolder}foldergallery.js";
        }
        $bjs .= sprintf("<script src=\"%s\"></script>\n", $filename);
        return Response::create()->withHjs($hjs)->withBjs($bjs);
    }

    private function includeColorbox(): Response
    {
        $this->jquery->include();
        $colorboxFolder = $this->pluginFolder . "colorbox/";
        $this->jquery->includePlugin("colorbox", "{$colorboxFolder}jquery.colorbox-min.js");
        $hjs = "<link rel=\"stylesheet\" href=\"{$colorboxFolder}colorbox.css\" type=\"text/css\">\n";
        $config = array('rel' => 'foldergallery_group', 'maxWidth' => '100%', 'maxHeight' => '100%');
        foreach ($this->text as $key => $value) {
            if (strpos($key, 'colorbox_') === 0) {
                $config[substr($key, strlen('colorbox_'))] = $value;
            }
        }
        $config = json_encode($config);
        $bjs = <<<SCRIPT
<script>
jQuery(function ($) {
    $(".foldergallery_group").colorbox($config);
});
</script>

SCRIPT;
        return Response::create()->withHjs($hjs)->withBjs($bjs);
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
