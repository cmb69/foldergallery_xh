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
use Foldergallery\Infra\Jquery;
use Foldergallery\Infra\Request;
use Foldergallery\Infra\View;
use Foldergallery\Logic\Util;
use Foldergallery\Value\Response;

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

    public function __invoke(Request $request, string $basefolder): Response
    {
        $children = $this->imageService->findEntries($basefolder . $request->folder());
        foreach ($children as &$child) {
            if ($child["isDir"]) {
                $folder = $request->folder() . $child["basename"];
                $child["url"] = Util::urlWithFoldergallery($request->url(), $folder);
            }
        }
        return $this->initializeFrontEnd($this->conf["frontend"])
            ->withOutput($this->view->render("gallery", [
                'breadcrumbs' => $this->getBreadcrumbs($request),
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
    private function getBreadcrumbs(Request $request)
    {
        $records = [];
        $breadcrumbs = Util::breadcrumbs($request->folder(), $this->view->plain("locator_start"));
        foreach ($breadcrumbs as $i => $breadcrumb) {
            $record = [];
            $record["name"] = $breadcrumb["name"];
            if ($i < count($breadcrumbs) - 1) {
                if (isset($breadcrumb["url"])) {
                    $record["url"] = Util::urlWithFoldergallery($request->url(), $breadcrumb["url"]);
                } else {
                    $record["url"] = Util::urlWithoutFoldergallery($request->url());
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
}
