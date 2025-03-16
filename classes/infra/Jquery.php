<?php

/**
 * Copyright (c) Christoph M. Becker
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

/** @codeCoverageIgnore */
class Jquery
{
    /** @var string */
    private $jqueryFolder;

    public function __construct(string $jqueryFolder)
    {
        $this->jqueryFolder = $jqueryFolder;
    }

    /** @return void */
    public function include()
    {
        include_once $this->jqueryFolder . "jquery.inc.php";
        include_jQuery();
    }

    /** @return void */
    public function includePlugin(string $name, string $path)
    {
        include_once $this->jqueryFolder . "jquery.inc.php";
        include_jQueryPlugin($name, $path);
    }
}
