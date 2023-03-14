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

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ImageServiceTest extends TestCase
{
    /**
     * @var ImageService
     */
    private $subject;

    protected function setUp(): void
    {
        vfsStream::setup('root');
        mkdir(vfsStream::url('root/foo'), 0777, true);
        file_put_contents(vfsStream::url('root/foo.txt'), 'blah');
        $im = imagecreatetruecolor(10, 10);
        imagefilledrectangle($im, 0, 0, 9, 9, 0xffffff);
        imagejpeg($im, vfsStream::url('root/image.jpg'));
        $thumbnailServiceStub = $this->createMock(ThumbnailService::class);
        $thumbnailServiceStub->method('makeThumbnail')->willReturn('thumb/nail');
        $thumbnailServiceStub->method('makeFolderThumbnail')->willReturn('thumb/nail');
        $this->subject = new ImageService(128, $thumbnailServiceStub);
    }

    public function testFindEntries()
    {
        $expected = array(
            (object) array(
                'caption' => 'foo',
                'basename' => 'foo',
                'filename' => vfsStream::url('root/foo'),
                'isDir' => true,
                'thumbnail' => 'thumb/nail',
                'srcset' => 'thumb/nail 1x, thumb/nail 2x, thumb/nail 3x'
            ),
            (object) array(
                'caption' => 'image',
                'filename' => vfsStream::url('root/image.jpg'),
                'isDir' => false,
                'thumbnail' => 'thumb/nail',
                'srcset' => 'thumb/nail 1x, thumb/nail 2x, thumb/nail 3x',
                'size' => "10x10",
            )
        );
        $this->assertEquals($expected, $this->subject->findEntries(vfsStream::url('root/')));
    }
}
