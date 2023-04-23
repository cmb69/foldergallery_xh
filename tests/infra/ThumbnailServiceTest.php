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

namespace Foldergallery\Infra;

use Foldergallery\Infra\ThumbnailService;
use PHPUnit\Framework\TestCase;

class ThumbnailServiceTest extends TestCase
{
    private $subject;

    private $landscape;
    private $portrait;

    protected function setUp(): void
    {
        $this->setUpImages();
        $this->subject = new ThumbnailService(
            hexdec("ffdd44")
        );
    }

    private function setUpImages()
    {
        $im = imagecreatetruecolor(1024, 768);
        imagefilledrectangle($im, 0, 0, 999, 999, 0xffffff);
        ob_start();
        imagejpeg($im);
        $this->landscape = ob_get_clean();
        $im = imagecreatetruecolor(768, 1024);
        imagefilledrectangle($im, 0, 0, 999, 999, 0xffffff);
        ob_start();
        imagejpeg($im);
        $this->portrait = ob_get_clean();
    }

    public function testLandscape()
    {
        $actual = $this->subject->makeThumbnail($this->landscape, 128);
        $info = getimagesizefromstring($actual);
        $this->assertSame(171, $info[0]);
        $this->assertSame(128, $info[1]);
        $this->assertSame(IMG_JPEG, $info[2]);
    }

    public function testPortrait()
    {
        $actual = $this->subject->makeThumbnail($this->portrait, 128);
        $info = getimagesizefromstring($actual);
        $this->assertSame(96, $info[0]);
        $this->assertSame(128, $info[1]);
        $this->assertSame(IMG_JPEG, $info[2]);
    }

    public function testFolderThumbnail(): void
    {
        $actual = $this->subject->makeFolderThumbnail([$this->landscape, $this->portrait], 128);
        $info = getimagesizefromstring($actual);
        $this->assertEquals([128, 128, IMG_JPEG], array_slice($info, 0, 3));
    }
}
