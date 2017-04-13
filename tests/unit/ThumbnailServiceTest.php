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

use PHPUnit_Framework_TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class ThumbnailServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ThumbnailService
     */
    private $subject;

    protected function setUp()
    {
        global $pth;

        vfsStream::setup('root');
        mkdir(vfsStream::url('root/foldergallery/cache/'), 0777, true);
        $this->setUpImages();
        $pth = array(
            'folder' => ['plugins' => vfsStream::url('root/')]
        );
        $this->subject = new ThumbnailService;
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
        $expected = 'vfs://root/foldergallery/cache/9dec6781e4447a3c4a0aeb51c642f4812c0e3425.jpg';
        $actual = $this->subject->makeThumbnail(vfsStream::url('root/landscape.jpg'), 128);
        $this->assertSame($expected, $actual);
        $info = getimagesize($expected);
        $this->assertSame(128, $info[0]);
        $this->assertSame(96, $info[1]);
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
}
