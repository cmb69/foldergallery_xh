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
