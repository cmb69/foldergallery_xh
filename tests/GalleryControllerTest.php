<?php

namespace Foldergallery;

use ApprovalTests\Approvals;
use Foldergallery\Infra\FakeRequest;
use Foldergallery\Infra\ImageService;
use Foldergallery\Infra\ThumbnailService;
use Foldergallery\Value\Image;
use Foldergallery\Value\Item;
use PHPUnit\Framework\TestCase;
use Plib\Jquery;
use Plib\View;

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
        $this->imageService->method("findItems")->willReturn([
            new Item("sub", "./userfiles/images/test/sub"),
            new Item("Foto", "./userfiles/images/test/Foto.jpg", [1520, 2688]),
        ]);
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

    public function testReportsNonExistentGallery(): void
    {
        $this->imageService = $this->createMock(ImageService::class);
        $response = $this->sut()(new FakeRequest(["query" => "Gallery"]), "test");
        $this->assertStringContainsString("Gallery  not found!", $response->output());
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
}
