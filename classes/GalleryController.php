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
use Foldergallery\Infra\ThumbnailService;
use Foldergallery\Infra\View;
use Foldergallery\Logic\Util;
use Foldergallery\Value\Item;
use Foldergallery\Value\Response;
use Foldergallery\Value\Url;

class GalleryController
{
    /** @var string */
    private $pluginFolder;

    /** @var array<string,string> */
    private $conf;

    /** @var ImageService */
    private $imageService;

    /** @var ThumbnailService */
    private $thumbnailService;

    /** @var Jquery */
    private $jquery;

    /** @var View */
    private $view;

    /** @param array<string,string> $conf */
    public function __construct(
        string $pluginFolder,
        array $conf,
        ImageService $imageService,
        ThumbnailService $thumbnailService,
        Jquery $jquery,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->conf = $conf;
        $this->imageService = $imageService;
        $this->thumbnailService = $thumbnailService;
        $this->jquery = $jquery;
        $this->view = $view;
    }

    public function __invoke(Request $request, string $basefolder): Response
    {
        if ($request->thumb()) {
            return $this->thumbnail($request, $basefolder);
        }
        $items = $this->imageService->findItems($basefolder . $request->folder());
        [$hjs, $output] = $this->initializeFrontEnd($this->conf["frontend"]);
        return Response::create($output . $this->view->render("gallery", [
            "breadcrumbs" => $this->getBreadcrumbs($request),
            "children" => $this->itemRecords($items, $request->folder(), $request->url()),
        ]))->withHjs($hjs);
    }

    private function thumbnail(Request $request, string $basefolder): Response
    {
        $dstHeight = (int) $request->size() * (int) $this->conf["thumb_size"];
        $folder = $request->folder() . $request->thumb();
        if (!pathinfo($request->thumb(), PATHINFO_EXTENSION)) {
            $images = $this->imageService->readFirstImagesIn($basefolder, $folder);
            $data = $this->thumbnailService->makeFolderThumbnail($images, $dstHeight);
        } else {
            $image = $this->imageService->readImage($basefolder . $folder);
            assert($image !== null); // TODO invalid assertion
            $data = $this->thumbnailService->makeThumbnail($image, $dstHeight);
        }
        return Response::createImage($data, 3 * 60 * 60, $request->time());
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

    /** @return list<array{name:string,url:string|null,isLink:bool}> */
    private function getBreadcrumbs(Request $request)
    {
        $records = [];
        $breadcrumbs = Util::breadcrumbs($request->folder(), $this->view->plain("locator_start"));
        foreach ($breadcrumbs as $i => $breadcrumb) {
            $record = [];
            $record["name"] = $breadcrumb["name"];
            if ($i < count($breadcrumbs) - 1) {
                if (isset($breadcrumb["url"])) {
                    $record["url"] = $request->url()->with("foldergallery_folder", $breadcrumb["url"])->relative();
                } else {
                    $record["url"] = $request->url()->without("foldergallery_folder")->relative();
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

    /**
     * @param list<Item> $items
     * @return list<array{caption:string,filename:string,thumbnail:string,srcset:string,isDir:bool,size:string|null,url:string|null}>
     */
    private function itemRecords(array $items, string $folder, Url $url): array
    {
        return array_map(function (Item $item) use ($folder, $url) {
            $folderUrl = $url->with("foldergallery_folder", $folder . basename($item->filename()));
            $thumbUrl = $url->with("foldergallery_thumb", basename($item->filename()));
            return [
                "caption" => $item->caption(),
                "filename" => $item->filename(),
                "thumbnail" => $thumbUrl->with("foldergallery_size", "1x")->relative(),
                "srcset" => $this->srcset($thumbUrl),
                "isDir" => $item->isFolder(),
                "size" => $item->size(),
                "url" => $item->isFolder() ? $folderUrl->relative() : null,
            ];
        }, $items);
    }

    private function srcset(Url $url): string
    {
        return implode(", ", array_map(function (string $size) use ($url) {
            return $url->with("foldergallery_size", $size)->relative() . " " . $size;
        }, ["1x", "2x", "3x"]));
    }
}
