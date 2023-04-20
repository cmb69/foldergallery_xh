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

    /** @var ImageService */
    private $imageService;

    /** @var Jquery */
    private $jquery;

    /** @var View */
    private $view;

    /** @param array<string,string> $conf */
    public function __construct(
        string $pluginFolder,
        array $conf,
        ImageService $imageService,
        Jquery $jquery,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->conf = $conf;
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
        [$hjs, $output] = $this->initializeFrontEnd($this->conf["frontend"]);
        return Response::create($output . $this->view->render("gallery", [
            "breadcrumbs" => $this->getBreadcrumbs($request),
            "children" => $children
        ]))->withHjs($hjs);
    }

    /** @return array{string,string} */
    private function initializeFrontEnd(string $frontEnd): array
    {
        switch ($frontEnd) {
            case "Photoswipe":
                return $this->includePhotoswipe();
            case "Colorbox":
                return $this->includeColorbox();
            default:
                return ["", $this->view->error("error_frontend", $frontEnd)];
        }
    }

    /** @return array{string,string} */
    private function includePhotoswipe(): array
    {
        return [
            $this->view->render("photoswipe_head", [
                "stylesheet" => $this->pluginFolder . "lib/photoswipe/photoswipe.css",
                "skin_stylesheet" => $this->pluginFolder . "lib/photoswipe/default-skin/default-skin.css",
                "script" => $this->pluginFolder . "lib/photoswipe/photoswipe.min.js",
                "skin_script" => $this->pluginFolder . "lib/photoswipe/photoswipe-ui-default.min.js",
            ]),
            $this->view->render("photoswipe", [
                "script" => $this->pluginFolder . "foldergallery.min.js",
            ]),
        ];
    }

    /** @return array{string,string} */
    private function includeColorbox(): array
    {
        $this->jquery->include();
        $this->jquery->includePlugin("colorbox", $this->pluginFolder . "lib/colorbox/jquery.colorbox-min.js");
        return [
            $this->view->render("colorbox_head", [
                "stylesheet" => $this->pluginFolder . "lib/colorbox/colorbox.css",
            ]),
            $this->view->render("colorbox", [
                "config" => [
                    "rel" => "foldergallery_group",
                    "maxWidth" => "100%",
                    "maxHeight" => "100%",
                    "current" => $this->view->plain("colorbox_current"),
                    "previous" => $this->view->plain("colorbox_previous"),
                    "next" => $this->view->plain("colorbox_next"),
                    "close" => $this->view->plain("colorbox_close"),
                    "imgError" => $this->view->plain("colorbox_imgError"),
                ],
            ]),
        ];
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
