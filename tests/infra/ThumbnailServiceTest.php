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
use org\bovigo\vfs\vfsStream;

class ThumbnailServiceTest extends TestCase
{
    /**
     * @var ThumbnailService
     */
    private $subject;

    protected function setUp(): void
    {
        vfsStream::setup('root');
        mkdir(vfsStream::url('root/foldergallery/cache/'), 0777, true);
        $this->setUpImages();
        $this->subject = new ThumbnailService(
            vfsStream::url('root/foldergallery/cache/'),
            hexdec("ffdd44")
        );
    }

    private function setUpImages()
    {
        $im = imagecreatetruecolor(1024, 768);
        imagefilledrectangle($im, 0, 0, 999, 999, 0xffffff);
        imagejpeg($im, vfsStream::url('root/landscape.jpg'));
        $im = imagecreatetruecolor(768, 1024);
        imagefilledrectangle($im, 0, 0, 999, 999, 0xffffff);
        imagejpeg($im, vfsStream::url('root/portrait.jpg'));
    }

    public function testLandscape()
    {
        $expected = 'vfs://root/foldergallery/cache/dc8f662fdb72d3fb9e296b6a4dfd70337d9a45b1.jpg';
        $actual = $this->subject->makeThumbnail(vfsStream::url('root/landscape.jpg'), 128);
        $this->assertSame($expected, $actual);
        $info = getimagesize($expected);
        $this->assertSame(171, $info[0]);
        $this->assertSame(128, $info[1]);
        $this->assertSame(IMG_JPEG, $info[2]);
    }

    public function testPortrait()
    {
        $expected = 'vfs://root/foldergallery/cache/63147356af2b30823235333ffd4c9a14ec0b403f.jpg';
        $actual = $this->subject->makeThumbnail(vfsStream::url('root/portrait.jpg'), 128);
        $this->assertSame($expected, $actual);
        $info = getimagesize($expected);
        $this->assertSame(96, $info[0]);
        $this->assertSame(128, $info[1]);
        $this->assertSame(IMG_JPEG, $info[2]);
    }

    public function testFolderThumbnail(): void
    {
        $expected = "vfs://root/foldergallery/cache/60dd3bc42e82660290e280ca4b791264a1aa40f3.jpg";
        $actual = $this->subject->makeFolderThumbnail("vfs://root/", ["landscape.jpg", "portrait.jpg"], 128);
        $this->assertEquals($expected, $actual);
        $info = getimagesize($expected);
        $this->assertEquals([128, 128, IMG_JPEG], array_slice($info, 0, 3));
    }
}
