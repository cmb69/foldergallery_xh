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

class Request
{
    /** @codeCoverageIgnore */
    public static function current(): self
    {
        return new self;
    }

    public function url(): string
    {
        $queryString = $this->queryString();
        return $this->sn() . ($queryString !== "" ? "?$queryString" : "");
    }

    public function folder(): string
    {
        if (!preg_match_all('/foldergallery_folder=([^&]*)/', $this->queryString(), $matches)) {
            return "";
        }
        $folder = urldecode(end($matches[1]));
        $folder = preg_replace(array('/\\\\/', '/\.{1,2}\//', '/\/+$/'), "", $folder . "/");
        return $folder === "" ? $folder : $folder . "/";
    }

    /** @codeCoverageIgnore */
    protected function sn(): string
    {
        global $sn;
        return $sn;
    }

    /** @codeCoverageIgnore */
    protected function queryString(): string
    {
        return $_SERVER["QUERY_STRING"];
    }
}
