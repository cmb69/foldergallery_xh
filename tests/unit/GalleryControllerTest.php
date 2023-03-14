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
use Foldergallery\Infra\View;
use PHPUnit\Framework\TestCase;

class GalleryControllerTest extends TestCase
{
    public function testRendersEmptyGallery(): void
    {
        $sut = new GalleryController(
            "./plugins/foldergallery/",
            $this->conf(),
            $this->text(),
            $this->imageService(),
            $this->view()
        );
        $response = $sut("/?Gallery", "test");
        Approvals::verifyHtml($response);
    }

    private function imageService(): ImageService
    {
        $service = $this->createMock(ImageService::class);
        $service->method("findEntries")->willReturn([]);
        return $service;
    }

    private function view(): View
    {
        return new View("./views/", $this->text());
    }

    private function conf(): array
    {
        return XH_includeVar("./config/config.php", "plugin_cf")["foldergallery"];
    }

    private function text(): array
    {
        return XH_includeVar("./languages/en.php", "plugin_tx")["foldergallery"];
    }
}
