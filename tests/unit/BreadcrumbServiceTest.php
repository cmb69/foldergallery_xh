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

namespace Foldergallery;

use PHPUnit_Framework_TestCase;

class BreadcrumbServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var BreadcrumbService
     */
    private $subject;

    protected function setUp()
    {
        global $plugin_tx;

        $plugin_tx = array(
            'foldergallery' => ['locator_start' => 'Start']
        );
        $this->subject = new BreadcrumbService('foo/bar/');
    }

    public function testGetBreadcrumbs()
    {
        $expected = array(
            (object) ['name' => 'Start'],
            (object) ['name' => 'foo', 'url' => 'foo'],
            (object) ['name' => 'bar', 'url' => 'foo/bar']
        );
        $this->assertEquals($expected, $this->subject->getBreadcrumbs());
    }
}
