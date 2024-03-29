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

use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    /** @dataProvider breadcrumbs */
    public function testBreadcrumbs(string $folder, string $first, array $expected)
    {
        $breadcrumbs = Util::breadcrumbs($folder, $first);
        $this->assertEquals($expected, $breadcrumbs);
    }

    public function breadcrumbs(): array
    {
        return [
            ["foo/bar", "Start", [
                ['name' => 'Start', "url" => null],
                ['name' => 'foo', 'url' => 'foo'],
                ['name' => 'bar', 'url' => 'foo/bar']
            ]],
            ["", "Start", [
                ["name" => "Start", "url" => null],
            ]],
        ];
    }
}
