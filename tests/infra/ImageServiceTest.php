<?php

namespace Foldergallery\Infra;

use Foldergallery\Value\Item;
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
        $this->subject = new ImageService("vfs://root/", 128, $thumbnailServiceStub);
    }

    public function testFindsItems()
    {
        $expected = [
            new Item("foo", "vfs://root/foo"),
            new Item("image", "vfs://root/image.jpg", [10, 10]),
        ];
        $this->assertEquals($expected, $this->subject->findItems(vfsStream::url('root/')));
    }
}
