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

namespace Foldergallery\Infra;

class Response
{
    public static function create(string $output = ""): self
    {
        $that = new self;
        $that->output = $output;
        return $that;
    }

    /** @var string */
    private $output;

    /** @var string|null */
    private $hjs = null;

    /** @var string|null */
    private $bjs = null;

    public function withOutput(string $output): self
    {
        $that = clone $this;
        $that->output .= $output;
        return $that;
    }

    public function withHjs(string $hjs): self
    {
        $that = clone $this;
        $that->hjs = $hjs;
        return $that;
    }

    public function withBjs(string $bjs): self
    {
        $that = clone $this;
        $that->bjs = $bjs;
        return $that;
    }

    public function output(): string
    {
        return $this->output;
    }

    public function hjs(): ?string
    {
        return $this->hjs;
    }

    public function bjs(): ?string
    {
        return $this->bjs;
    }

    public function respond(): string
    {
        global $hjs, $bjs;

        if ($this->hjs !== null) {
            $hjs .= $this->hjs;
        }
        if ($this->bjs !== null) {
            $bjs .= $this->bjs;
        }
        return $this->output;
    }
}
