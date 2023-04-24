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
use Foldergallery\Value\Image;
use PHPUnit\Framework\TestCase;

class ThumbnailServiceTest extends TestCase
{
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ThumbnailService(
            hexdec("ffdd44")
        );
    }

    /** @dataProvider landscapeThumbnailData */
    public function testLandscapeThumbnail(Image $image, array $expected): void
    {
        $data = $this->subject->makeThumbnail($image, 64, 2.0);
        $im = imagecreatefromstring($data);
        $this->assertEquals(128, imagesx($im));
        $this->assertEquals(64, imagesy($im));
        imagetruecolortopalette($im, false, 4);
        $colors = $this->colors($im);
        $this->assertEquals($colors[$expected[0]], imagecolorat($im, 31, 15));
        $this->assertEquals($colors[$expected[1]], imagecolorat($im, 95, 15));
        $this->assertEquals($colors[$expected[2]], imagecolorat($im, 31, 47));
        $this->assertEquals($colors[$expected[3]], imagecolorat($im, 95, 47));
    }

    public function landscapeThumbnailData(): array
    {
        return [
            "orient 1" => [new Image($this->landscape(), 1), ["red", "green", "blue", "white"]],
            "orient 2" => [new Image($this->landscape(), 2), ["green", "red", "white", "blue"]],
            "orient 3" => [new Image($this->landscape(), 3), ["white", "blue", "green", "red"]],
            "orient 4" => [new Image($this->landscape(), 4), ["blue", "white", "red", "green"]],
            "orient 5" => [new Image($this->portrait(), 5), ["red", "blue", "green", "white"]],
            "orient 6" => [new Image($this->portrait(), 6), ["blue", "red", "white", "green"]],
            "orient 7" => [new Image($this->portrait(), 7), ["white", "green", "blue", "red"]],
            "orient 8" => [new Image($this->portrait(), 8), ["green", "white", "red", "blue"]],
        ];
    }

    /** @dataProvider portraitThumbnailData */
    public function testPortraitThumbnail(Image $image, array $expected): void
    {
        $data = $this->subject->makeThumbnail($image, 64, 0.5);
        $im = imagecreatefromstring($data);
        $this->assertEquals(32, imagesx($im));
        $this->assertEquals(64, imagesy($im));
        imagetruecolortopalette($im, false, 4);
        $colors = $this->colors($im);
        $this->assertEquals($colors[$expected[0]], imagecolorat($im, 7, 15));
        $this->assertEquals($colors[$expected[1]], imagecolorat($im, 23, 15));
        $this->assertEquals($colors[$expected[2]], imagecolorat($im, 7, 47));
        $this->assertEquals($colors[$expected[3]], imagecolorat($im, 23, 47));
    }

    public function portraitThumbnailData(): array
    {
        return [
            "orient 1" => [new Image($this->portrait(), 1), ["red", "green", "blue", "white"]],
            "orient 2" => [new Image($this->portrait(), 2), ["green", "red", "white", "blue"]],
            "orient 3" => [new Image($this->portrait(), 3), ["white", "blue", "green", "red"]],
            "orient 4" => [new Image($this->portrait(), 4), ["blue", "white", "red", "green"]],
            "orient 5" => [new Image($this->landscape(), 5), ["red", "blue", "green", "white"]],
            "orient 6" => [new Image($this->landscape(), 6), ["blue", "red", "white", "green"]],
            "orient 7" => [new Image($this->landscape(), 7), ["white", "green", "blue", "red"]],
            "orient 8" => [new Image($this->landscape(), 8), ["green", "white", "red", "blue"]],
        ];
    }

    public function testRetainsIccProfile(): void
    {
        $icc = "SUNDX1BST0ZJTEUAAQEAAAIwQURCRQIQAABtbnRyUkdCIFhZWiAH0AAIAAsAEwA3ACdhY3NwQVBQTAAAAABub25lAAAAAAAAAAAAAAAAAAAAAAAA9tYAAQAAAADTLUFEQkUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAApjcHJ0AAAA/AAAADJkZXNjAAABMAAAAGl3dHB0AAABnAAAABRia3B0AAABsAAAABRyVFJDAAABxAAAAA5nVFJDAAAB1AAAAA5iVFJDAAAB5AAAAA5yWFlaAAAB9AAAABRnWFlaAAACCAAAABRiWFlaAAACHAAAABR0ZXh0AAAAAENvcHlyaWdodCAyMDAwIEFkb2JlIFN5c3RlbXMgSW5jb3Jwb3JhdGVkAAAAZGVzYwAAAAAAAAAPV2lkZSBHYW11dCBSR0IAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAWFlaIAAAAAAAAPbcAAEAAAAA0zpYWVogAAAAAAAAAAAAAAAAAAAAAGN1cnYAAAAAAAAAAQIzAABjdXJ2AAAAAAAAAAECMwAAY3VydgAAAAAAAAABAjMAAFhZWiAAAAAAAAC3agAAQjsAAAAAWFlaIAAAAAAAABnbAAC5hwAADRxYWVogAAAAAAAAJZEAAAQ+AADGEQ==";
        $image = new Image($this->portrait(), 0, $icc);
        $data = $this->subject->makeThumbnail($image, 64, 0.5);
        getimagesizefromstring($data, $info);
        $this->assertEquals($icc, $info["APP2"]);
    }

    public function testFolderThumbnail(): void
    {
        $actual = $this->subject->makeFolderThumbnail([new Image($this->landscape(), 0), new Image($this->portrait(), 0)], 64);
        $info = getimagesizefromstring($actual);
        $this->assertEquals([64, 64, IMAGETYPE_JPEG], array_slice($info, 0, 3));
    }

    private function landscape(): string
    {
        $im = imagecreatetruecolor(200, 100);
        imagefilledrectangle($im, 0, 0, 99, 99, 0xff0000);
        imagefilledrectangle($im, 100, 0, 199, 99, 0x00ff00);
        imagefilledrectangle($im, 0, 50, 99, 199, 0x0000ff);
        imagefilledrectangle($im, 100, 50, 199, 199, 0xfffffff);
        ob_start();
        imagejpeg($im);
        return ob_get_clean();
    }

    private function portrait(): string
    {
        $im = imagecreatetruecolor(100, 200);
        imagefilledrectangle($im, 0, 0, 49, 99, 0xff0000);
        imagefilledrectangle($im, 50, 0, 99, 99, 0x00ff00);
        imagefilledrectangle($im, 0, 100, 49, 199, 0x0000ff);
        imagefilledrectangle($im, 50, 100, 99, 199, 0xfffffff);
        ob_start();
        imagejpeg($im);
        return ob_get_clean();
    }

    private function colors($im): array
    {
        return [
            "red" => imagecolorclosest($im, 0xff, 0x00, 0x00),
            "green" => imagecolorclosest($im, 0x00, 0xff, 0x00),
            "blue" => imagecolorclosest($im, 0x00, 0x00, 0xff),
            "white" => imagecolorclosest($im, 0xff, 0xff, 0xff),
        ];
    }
}
