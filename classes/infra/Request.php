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

use Foldergallery\Value\Url;

class Request
{
    /** @codeCoverageIgnore */
    public static function current(): self
    {
        return new self();
    }

    public function url(): Url
    {
        $rest = $this->query();
        if ($rest !== "") {
            $rest = "?" . $rest;
        }
        return Url::from(CMSIMPLE_URL . $rest);
    }

    public function folder(): string
    {
        $folder = $this->url()->param("foldergallery_folder");
        if (!is_string($folder)) {
            return "";
        }
        $folder = preg_replace(array('/\\\\/', '/\.{1,2}\//', '/\/+$/'), "", $folder . "/");
        return $folder === "" ? $folder : $folder . "/";
    }

    public function thumb(): string
    {
        $thumb = $this->url()->param("foldergallery_thumb");
        if (!is_string($thumb)) {
            return "";
        }
        return basename($thumb);
    }

    public function size(): string
    {
        $size = $this->url()->param("foldergallery_size");
        if (!is_string($size)) {
            return "1x";
        }
        if (!in_array($size, ["1x", "2x", "3x"], true)) {
            return "1x";
        }
        return $size;
    }

    public function ratio(): float
    {
        $ratio = $this->url()->param("foldergallery_ratio");
        if (!is_string($ratio)) {
            return 1.0; // ?
        }
        return (float) $ratio;
    }

    /** @codeCoverageIgnore */
    protected function query(): string
    {
        return $_SERVER["QUERY_STRING"];
    }

    public function time(): int
    {
        return (int) $_SERVER["REQUEST_TIME"];
    }
}
