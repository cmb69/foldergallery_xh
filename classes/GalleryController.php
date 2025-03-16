<?php

/**
 * Copyright (c) Christoph M. Becker
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
use Foldergallery\Infra\Request;
use Foldergallery\Infra\ThumbnailService;
use Foldergallery\Infra\View;
use Foldergallery\Logic\Util;
use Foldergallery\Value\Breadcrumb;
use Foldergallery\Value\Item;
use Foldergallery\Value\Response;
use Foldergallery\Value\Url;
use Plib\Jquery;

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
        if (($items = $this->imageService->findItems($basefolder . $request->folder())) === null) {
            return Response::create($this->view->error("error_gallery_notfound", $request->folder()));
        }
        [$hjs, $output] = $this->initializeFrontEnd($this->conf["frontend"]);
        return Response::create($output . $this->view->render("gallery", [
            "breadcrumbs" => $this->breadcrumbRecords($request, $items),
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
            $data = $this->thumbnailService->makeThumbnail($image, $dstHeight, $request->ratio());
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
            "",
            $this->view->render("photoswipe", [
                "stylesheet" => $this->pluginFolder . "lib/photoswipe/photoswipe.css",
                "core" => $this->pluginFolder . "lib/photoswipe/photoswipe.esm.min.js",
                "lightbox" => $this->pluginFolder . "lib/photoswipe/photoswipe-lightbox.esm.min.js",
                "opacity" => $this->conf["photoswipe_opacity"],
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
                "stylesheet" => $this->pluginFolder . "lib/colorbox/" . $this->conf["colorbox_theme"] . "/colorbox.css",
            ]),
            $this->view->render("colorbox", [
                "config" => [
                    "rel" => "foldergallery_group",
                    "maxWidth" => "100%",
                    "maxHeight" => "100%",
                    "slideshow" => (bool) $this->conf["colorbox_slideshow"],
                    "slideshowSpeed" => (int) $this->conf["colorbox_slideshow_speed"],
                    "slideshowAuto" => (bool) $this->conf["colorbox_slideshow_auto"],
                    "current" => $this->view->plain("colorbox_current"),
                    "previous" => $this->view->plain("colorbox_previous"),
                    "next" => $this->view->plain("colorbox_next"),
                    "slideshowStart" => $this->view->plain("colorbox_start_slideshow"),
                    "slideshowStop" => $this->view->plain("colorbox_stop_slideshow"),
                    "close" => $this->view->plain("colorbox_close"),
                    "imgError" => $this->view->plain("colorbox_imgError"),
                ],
            ]),
        ];
    }

    /**
     * @param list<Item> $items
     * @return list<array{name:string,url:string|null}>
     */
    private function breadcrumbRecords(Request $request, array $items)
    {
        return array_map(function (Breadcrumb $breadcrumb) {
            return [
                "name" => $breadcrumb->name(),
                "url" => $breadcrumb->url(),
            ];
        }, Util::breadcrumbs($request->folder(), $this->view->plain("locator_start"), $request->url(), $items));
    }

    /**
     * @param list<Item> $items
     * @return list<array{caption:string,filename:string,thumbnail:string,srcset:string,isDir:bool,width:int|null,height:int|null,url:string|null}>
     */
    private function itemRecords(array $items, string $folder, Url $url): array
    {
        $meanRatio = Util::meanRatio($items);
        return array_map(function (Item $item) use ($folder, $url, $meanRatio) {
            $folderUrl = $url->with("foldergallery_folder", $folder . basename($item->filename()));
            $thumbUrl = $url->with("foldergallery_thumb", basename($item->filename()));
            $ratio = $item->ratio();
            if ($this->conf["thumb_crop"]) {
                $ratio = sqrt($ratio * $meanRatio);
            }
            return [
                "caption" => $item->caption(),
                "filename" => $item->filename(),
                "thumbnail" => $thumbUrl->with("foldergallery_size", "1x")
                    ->with("foldergallery_ratio", (string) $ratio)->relative(),
                "srcset" => $this->srcset($thumbUrl, $ratio),
                "isDir" => $item->isFolder(),
                "width" => $item->size() ? $item->size()[0] : null,
                "height" => $item->size() ? $item->size()[1] : null,
                "url" => $item->isFolder() ? $folderUrl->relative() : null,
            ];
        }, $items);
    }

    private function srcset(Url $url, float $ratio): string
    {
        return implode(", ", array_map(function (string $size) use ($url, $ratio) {
            $url = $url->with("foldergallery_size", $size)->with("foldergallery_ratio", (string) $ratio);
            return $url->relative() . " " . $size;
        }, ["1x", "2x", "3x"]));
    }
}
