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

namespace Foldergallery\Logic;

class Util
{
    /** @return list<array{name:string,url:?string}> */
    public static function breadcrumbs(string $path, string $firstName): array
    {
        $result = explode('/', rtrim($path, "/"));
        $result = array_filter($result);
        $url = '';
        foreach ($result as &$part) {
            $url .= "$part/";
            $part = ['name' => $part, 'url' => rtrim($url, '/')];
        }
        array_unshift($result, ['name' => $firstName, "url" => null]);
        return $result;
    }

    public static function urlWithFolderGallery(string $url, string $value): string
    {
        return self::urlWithoutFoldergallery($url) . "&foldergallery_folder=" . urlencode($value);
    }

    public static function urlWithoutFoldergallery(string $url): string
    {
        return preg_replace('/&foldergallery_folder=[^&]+/', "", $url);
    }
}
