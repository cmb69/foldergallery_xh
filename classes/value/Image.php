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

namespace Foldergallery\Value;

class Image
{
    /** @var string */
    private $data;

    /** @var int */
    private $orientation;

    /** @var string */
    private $icc;

    public function __construct(string $data, int $orientation, string $icc = "")
    {
        $this->data = $data;
        $this->orientation = $orientation;
        $this->icc = $icc;
    }

    public function data(): string
    {
        return $this->data;
    }

    public function orientation(): int
    {
        return $this->orientation;
    }

    public function icc(): string
    {
        return $this->icc;
    }
}
