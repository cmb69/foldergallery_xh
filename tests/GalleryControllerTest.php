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
use Foldergallery\Infra\ImageService;
use Foldergallery\Infra\Jquery;
use Foldergallery\Infra\Request;
use Foldergallery\Infra\View;
use PHPUnit\Framework\TestCase;

class GalleryControllerTest extends TestCase
{
    public function testRendersGallery(): void
    {
        $sut = new GalleryController(
            "./plugins/foldergallery/",
            $this->conf(),
            $this->imageService(),
            $this->jquery(),
            $this->view()
        );
        $response = $sut($this->request(), "test");
        Approvals::verifyHtml($response->output());
    }

    public function testRendersGallerySubfolder(): void
    {
        $sut = new GalleryController(
            "./plugins/foldergallery/",
            $this->conf("Colorbox"),
            $this->imageService(),
            $this->jquery(),
            $this->view()
        );
        $request = $this->createMock(Request::class);
        $request->method("url")->willReturn("/?Gallery&foldergallery_folder=sub");
        $request->method("folder")->willReturn("sub/");
        $response = $sut($request, "test");
        Approvals::verifyHtml($response->output());
    }

    public function testRendersHjs(): void
    {
        $sut = new GalleryController(
            "./plugins/foldergallery/",
            $this->conf(),
            $this->imageService(),
            $this->jquery(),
            $this->view()
        );
        $response = $sut($this->request(), "test");
        Approvals::verifyHtml($response->hjs());
    }

    public function testRendersColorboxHjs(): void
    {
        $sut = new GalleryController(
            "./plugins/foldergallery/",
            $this->conf("Colorbox"),
            $this->imageService(),
            $this->jquery(true),
            $this->view()
        );
        $response = $sut($this->request(), "test");
        Approvals::verifyHtml($response->hjs());
    }

    public function testUnsupportedFrontEnd(): void
    {
        $sut = new GalleryController(
            "./plugins/foldergallery/",
            $this->conf("Unsupported"),
            $this->imageService(),
            $this->jquery(),
            $this->view()
        );
        $response = $sut($this->request(), "test");
        Approvals::verifyHtml($response->output());
    }

    private function request(): Request
    {
        $request = $this->createMock(Request::class);
        $request->method("url")->willReturn("/?Gallery");
        $request->method("folder")->willReturn("");
        return $request;
    }

    private function imageService(): ImageService
    {
        $service = $this->createMock(ImageService::class);
        $service->method("findEntries")->willReturn([$this->subfolder(), $this->image()]);
        return $service;
    }

    private function jquery(bool $used = false): Jquery
    {
        $jquery = $this->createMock(Jquery::class);
        if ($used) {
            $jquery->expects($this->once())->method("include");
            $jquery->expects($this->once())->method("includePlugin")
                ->with("colorbox", "./plugins/foldergallery/lib/colorbox/jquery.colorbox-min.js");
        }
        return $jquery;
    }

    private function view(): View
    {
        return new View("./views/", $this->text());
    }

    private function conf(string $frontEnd = "Photoswipe"): array
    {
        $conf = XH_includeVar("./config/config.php", "plugin_cf")["foldergallery"];
        $conf["frontend"] = $frontEnd;
        return $conf;
    }

    private function text(): array
    {
        return XH_includeVar("./languages/en.php", "plugin_tx")["foldergallery"];
    }

    private function image(): array
    {
        return [
            "caption" => "Foto",
            "basename" => null,
            "filename" => "./userfiles/images/test/Foto.jpg",
            "thumbnail" => "./plugins/foldergallery/cache/987063af227eefe7990217434bf913e66e05194b.jpg",
            "srcset" => "./plugins/foldergallery/cache/987063af227eefe7990217434bf913e66e05194b.jpg 1x, ./plugins/foldergallery/cache/cd61be09e4859ba1cb621fa7555c534d01b72562.jpg 2x, ./plugins/foldergallery/cache/0f7a1df87f33789abad3b6449d8f8738635aac39.jpg 3x",
            "isDir" => false,
            "size" => "1520x2688",
        ];
    }

    private function subfolder(): array
    {
        return [
            "caption" => "sub",
            "basename" => "sub",
            "filename" => "./userfiles/images/test/sub",
            "thumbnail" => "./plugins/foldergallery/cache/798d64164c925053c757bce207f14a5beeea2527.jpg",
            "srcset" => "./plugins/foldergallery/cache/798d64164c925053c757bce207f14a5beeea2527.jpg 1x, ./plugins/foldergallery/cache/90f9ff2fdd5b5eeadbebc6389834f03271d8abbc.jpg 2x, ./plugins/foldergallery/cache/7c2941af8c00e286f85a0b5fd63cdc5b268c7275.jpg 3x",
            "isDir" => true,
            "size" => null,
        ];
    }
}