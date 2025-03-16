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

namespace Foldergallery\Logic;

use Foldergallery\Value\Breadcrumb;
use Foldergallery\Value\Item;
use Foldergallery\Value\Url;

class Util
{
    /**
     * @param list<Item> $items
     * @return list<Breadcrumb>
     */
    public static function breadcrumbs(string $path, string $firstName, Url $url, array $items): array
    {
        $result = explode('/', rtrim($path, "/"));
        $result = array_filter($result);
        $path = '';
        $result = array_map(function ($part) use (&$path, $url) {
            $path .= "$part/";
            return new Breadcrumb($part, $url->with("foldergallery_folder", rtrim($path, '/'))->relative());
        }, $result);
        array_unshift($result, new Breadcrumb($firstName, $url->without("foldergallery_folder")->relative()));
        $result[count($result) - 1] = new Breadcrumb($result[count($result) - 1]->name(), null);
        if (count($result) < 2 && !self::hasFolders($items)) {
            return [];
        }

        return $result;
    }

    /** @param list<Item> $items */
    private static function hasFolders(array $items): bool
    {
        return array_reduce($items, function (bool $carry, Item $item) {
            return $carry || $item->isFolder();
        }, false);
    }

    /** @param list<Item> $items */
    public static function meanRatio(array $items): float
    {
        $ratios = array_filter(array_map(function (Item $item) {
            return $item->ratio();
        }, $items));
        return $ratios ? array_product($ratios) ** (1 / count($ratios)) : 1.0;
    }
}
