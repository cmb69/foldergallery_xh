<?php

/**
 * Copyright 2023 Christoph M. Becker
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
