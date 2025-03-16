<?php

namespace Foldergallery\Infra;

use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    /** @dataProvider folders */
    public function testFolder(string $queryString, string $expected): void
    {
        $sut = new FakeRequest(["query" => $queryString]);
        $folder = $sut->folder();
        $this->assertEquals($expected, $folder);
    }

    public function folders(): array
    {
        return [
            ["", ""],
            ["foldergallery_folder=foo", "foo/"],
            ["foldergallery_folder=foo&foldergallery_folder=bar", "bar/"],
            ["foldergallery_folder=foo%2Fbar", "foo/bar/"],
            ["foldergallery_folder=\\", ""],
            ["foldergallery_folder=..", ""],
        ];
    }
}
