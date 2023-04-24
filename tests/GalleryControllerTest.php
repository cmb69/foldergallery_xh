<?php

/**
 * Copyright 2023 Christoph M. Becker
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

use ApprovalTests\Approvals;
use Foldergallery\Infra\FakeRequest;
use Foldergallery\Infra\ImageService;
use Foldergallery\Infra\Jquery;
use Foldergallery\Infra\Request;
use Foldergallery\Infra\ThumbnailService;
use Foldergallery\Infra\View;
use Foldergallery\Value\Image;
use Foldergallery\Value\Url;
use PHPUnit\Framework\TestCase;

class GalleryControllerTest extends TestCase
{
    private $pluginFolder;
    private $conf;
    private $imageService;
    private $thumbnailService;
    private $jquery;
    private $view;

    public function setUp(): void
    {
        $this->pluginFolder = "./plugins/foldergallery/";
        $this->conf = XH_includeVar("./config/config.php", "plugin_cf")["foldergallery"];
        $this->imageService = $this->createMock(ImageService::class);
        $this->imageService->method("findEntries")->willReturn([$this->subfolder(), $this->image()]);
        $this->thumbnailService = $this->createMock(ThumbnailService::class);
        $this->jquery = $this->createMock(Jquery::class);
        $this->view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["foldergallery"]);
    }

    private function sut(): GalleryController
    {
        return new GalleryController(
            $this->pluginFolder,
            $this->conf,
            $this->imageService,
            $this->thumbnailService,
            $this->jquery,
            $this->view
        );
    }

    public function testRendersGallery(): void
    {
        $response = $this->sut()(new FakeRequest(["query" => "Gallery"]), "test");
        Approvals::verifyHtml($response->output());
    }

    public function testRendersGallerySubfolder(): void
    {
        $this->conf["frontend"] = "Colorbox";
        $request = new FakeRequest(["query" => "Gallery&foldergallery_folder=sub"]);
        $response = $this->sut()($request, "test");
        Approvals::verifyHtml($response->output());
    }

    public function testRendersHjs(): void
    {
        $response = $this->sut()(new FakeRequest(["query" => "Gallery"]), "test");
        Approvals::verifyHtml($response->hjs());
    }

    public function testRendersColorboxHjs(): void
    {
        $this->conf["frontend"] = "Colorbox";
        $this->jquery->expects($this->once())->method("include");
        $this->jquery->expects($this->once())->method("includePlugin")
            ->with("colorbox", "./plugins/foldergallery/lib/colorbox/jquery.colorbox-min.js");
        $response = $this->sut()(new FakeRequest(["query" => "Gallery"]), "test");
        Approvals::verifyHtml($response->hjs());
    }

    public function testUnsupportedFrontEnd(): void
    {
        $this->conf["frontend"] = "Unsupported";
        $response = $this->sut()(new FakeRequest(["query" => "Gallery"]), "test");
        Approvals::verifyHtml($response->output());
    }

    public function testDeliversFolderThumbnail(): void
    {
        $images = [new Image("some image data", 0), new Image("other image data", 0)];
        $this->imageService->expects($this->once())->method("readFirstImagesIn")->with("test", "sub")
            ->willReturn($images);
        $this->thumbnailService->expects($this->once())->method("makeFolderThumbnail")
            ->with($images, 128)->willReturn("thumb/nail");
        $request = new FakeRequest(["query" => "Gallery&foldergallery_thumb=sub&foldergallery_size=1x"]);
        $response = $this->sut()($request, "test");
        [$data] = $response->image();
        $this->assertEquals("thumb/nail", $data);
    }

    public function testDeliversImageThumbnail(): void
    {
        $this->imageService->expects($this->once())->method("readImage")->with("./userfiles/images/test/image.jpg")
            ->willReturn(new Image("some image data", 0));
        $this->thumbnailService->expects($this->once())->method("makeThumbnail")
            ->with(new Image("some image data", 0), 128)->willReturn("thumb/nail");
        $request = new FakeRequest(["query" => "Gallery&foldergallery_thumb=image.jpg&foldergallery_size=1x"]);
        $response = $this->sut()($request, "./userfiles/images/test/");
        [$data] = $response->image();
        $this->assertEquals("thumb/nail", $data);
    }

    private function image(): array
    {
        return [
            "caption" => "Foto",
            "basename" => null,
            "filename" => "./userfiles/images/test/Foto.jpg",
            "thumbnails" => [
                "1x" => "./plugins/foldergallery/cache/987063af227eefe7990217434bf913e66e05194b.jpg",
                "2x" => "./plugins/foldergallery/cache/cd61be09e4859ba1cb621fa7555c534d01b72562.jpg",
                "3x" => "./plugins/foldergallery/cache/0f7a1df87f33789abad3b6449d8f8738635aac39.jpg",
            ],
            "isDir" => false,
            "size" => "1520x2688",
            "images" => null,
        ];
    }

    private function subfolder(): array
    {
        return [
            "caption" => "sub",
            "basename" => "sub",
            "filename" => "./userfiles/images/test/sub",
            "thumbnails" => [
                "1x" => "./plugins/foldergallery/cache/798d64164c925053c757bce207f14a5beeea2527.jpg",
                "2x" => "./plugins/foldergallery/cache/90f9ff2fdd5b5eeadbebc6389834f03271d8abbc.jpg",
                "3x" => "./plugins/foldergallery/cache/7c2941af8c00e286f85a0b5fd63cdc5b268c7275.jpg",
            ],
            "isDir" => true,
            "size" => null,
            "images" => [],
        ];
    }
}
