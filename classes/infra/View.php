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

class View
{
    /** @var string */
    private $templateFolder;

    /** @var array<string,string> */
    private $text;

    /** @param array<string,string> $text */
    public function __construct(string $templateFolder, array $text)
    {
        $this->templateFolder = $templateFolder;
        $this->text = $text;
    }

    /** @param scalar $args */
    public function text(string $key, ...$args): string
    {
        return sprintf($this->esc($this->text[$key]), ...$args);
    }

    /** @param scalar $args */
    public function plain(string $key, ...$args): string
    {
        return sprintf($this->text[$key], ...$args);
    }

    /** @param scalar $args */
    public function error(string $key, ...$args): string
    {
        return XH_message("fail", $this->text[$key], ...$args) . "\n";
    }

    /** @param mixed $value */
    public function json($value): string
    {
        return (string) json_encode($value, JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /** @param array<string,mixed> $_data */
    public function render(string $_template, array $_data): string
    {
        array_walk_recursive($_data, function (&$value) {
            assert(is_null($value) || is_scalar($value));
            if (is_string($value)) {
                $value = $this->esc($value);
            }
        });
        extract($_data);
        ob_start();
        include $this->templateFolder . $_template . ".php";
        return (string) ob_get_clean();
    }

    /** @param string $value */
    public function esc($value): string
    {
        return XH_hsc($value);
    }
}
