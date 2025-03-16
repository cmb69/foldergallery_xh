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

use Foldergallery\Value\Response;

class Responder
{
    public static function respond(Response $response): string
    {
        global $title, $hjs;
        if ($response->image() !== null) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            [$data, $maxAge, $now] = $response->image();
            header("Content-Type: image/jpeg");
            header("Cache-Control: private, max-age=" . $maxAge);
            header("Pragma: ", true);
            header("Expires: " . date("r", $now + $maxAge));
            echo $data;
            exit;
        }
        if ($response->title() !== null) {
            $title = $response->title();
        }
        if ($response->hjs() !== null) {
            $hjs .= $response->hjs();
        }
        return $response->output();
    }
}
