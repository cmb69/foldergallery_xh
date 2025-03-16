<?php

namespace Foldergallery\Logic;

use Foldergallery\Value\Breadcrumb;
use Foldergallery\Value\Item;
use Foldergallery\Value\Url;
use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    /** @dataProvider breadcrumbs */
    public function testBreadcrumbs(string $folder, string $first, Url $url, array $items, array $expected)
    {
        $breadcrumbs = Util::breadcrumbs($folder, $first, $url, $items);
        $this->assertEquals($expected, $breadcrumbs);
    }

    public function breadcrumbs(): array
    {
        return [
            ["foo/bar", "Start", Url::from("http://example.com/"), [new Item("", "")], [
                new Breadcrumb("Start", "/"),
                new Breadcrumb("foo", "/?&foldergallery_folder=foo"),
                new Breadcrumb("bar", null),
            ]],
            ["", "Start", Url::from("http://example.com/"), [new Item("", "")], [
                new Breadcrumb("Start", null),
            ]],
            ["", "Start", Url::from("http://example.com/"), [new Item("", "", [10, 10])], [
            ]],
        ];
    }
}
