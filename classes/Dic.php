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
use Foldergallery\Infra\ThumbnailService;
use Plib\Jquery;
use Plib\SystemChecker;
use Plib\View;

class Dic
{
    public static function makeGalleryController(): GalleryController
    {
        global $pth, $plugin_cf;
        return new GalleryController(
            $pth["folder"]["plugins"] . "foldergallery/",
            $plugin_cf["foldergallery"],
            self::makeImageService(),
            self::makeThumbnailService(),
            new Jquery($pth["folder"]["plugins"] . "jquery/"),
            self::makeView()
        );
    }

    public static function makeInfoController(): InfoController
    {
        global $pth;

        return new InfoController(
            $pth["folder"]["plugins"] . "foldergallery/",
            new SystemChecker(),
            self::makeView()
        );
    }

    private static function makeImageService(): ImageService
    {
        return new ImageService();
    }

    private static function makeThumbnailService(): ThumbnailService
    {
        global $plugin_cf;
        return new ThumbnailService(
            (int) hexdec($plugin_cf['foldergallery']['folder_background'])
        );
    }

    private static function makeView(): View
    {
        global $pth, $plugin_tx;
        return new View(
            $pth["folder"]["plugins"] . "foldergallery/views/",
            $plugin_tx["foldergallery"]
        );
    }
}
