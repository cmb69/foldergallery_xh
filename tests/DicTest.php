<?php

namespace Foldergallery;

use PHPUnit\Framework\TestCase;

class DicTest extends TestCase
{
    public function setUp(): void
    {
        global $pth, $plugin_cf, $plugin_tx, $_SERVER;

        $pth = ["folder" => ["plugins" => ""]];
        $plugin_cf = ["foldergallery" => ["folder_background" => "", "thumb_size" => ""]];
        $plugin_tx = ["foldergallery" => []];
        $_SERVER = ["QUERY_STRING" => ""];
    }

    public function testMakesGalleryController(): void
    {
        $this->assertInstanceOf(GalleryController::class, Dic::makeGalleryController());
    }

    public function testMakesInfoController(): void
    {
        $this->assertInstanceOf(InfoController::class, Dic::makeInfoController());
    }
}
