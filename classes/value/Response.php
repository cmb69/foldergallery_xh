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

class Response
{
    public static function create(string $output = ""): self
    {
        $that = new self();
        $that->output = $output;
        return $that;
    }

    public static function createImage(string $data, int $maxAge, int $now): self
    {
        $that = new self();
        $that->image = [$data, $maxAge, $now];
        return $that;
    }

    /** @var string */
    private $output;

    /** @var array{string,int,int}|null */
    private $image = null;

    /** @var string|null */
    private $title = null;

    /** @var string|null */
    private $hjs = null;

    public function withTitle(string $title): self
    {
        $that = clone $this;
        $that->title = $title;
        return $that;
    }

    public function withHjs(string $hjs): self
    {
        $that = clone $this;
        $that->hjs = $hjs;
        return $that;
    }

    public function output(): string
    {
        return $this->output;
    }

    /** @return array{string,int,int}|null */
    public function image()
    {
        return $this->image;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function hjs(): ?string
    {
        return $this->hjs;
    }
}
