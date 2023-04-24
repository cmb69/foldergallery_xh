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

namespace Foldergallery\Value;

class Item
{
    /** @var string */
    private $caption;

    /** @var string */
    private $filename;

    /** @var string|null */
    private $size;

    public function __construct(string $caption, string $filename, ?string $size = null)
    {
        $this->caption = $caption;
        $this->filename = $filename;
        $this->size = $size;
    }

    public function isFolder(): bool
    {
        return $this->size === null;
    }

    public function caption(): string
    {
        return $this->caption;
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function size(): ?string
    {
        return $this->size;
    }
}
