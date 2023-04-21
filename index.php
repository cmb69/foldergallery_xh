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

use Foldergallery\Dic;
use Foldergallery\Infra\Request;
use Foldergallery\Infra\Responder;

const FOLDERGALLERY_VERSION = "1.0beta1";

function foldergallery(string $basefolder = ""): string
{
    global $pth;
    $folder = $pth["folder"]["images"] . $basefolder . "/";
    return Responder::respond(Dic::makeGalleryController()(Request::current(), $folder));
}
