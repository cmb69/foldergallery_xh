<?php

const CMSIMPLE_XH_VERSION = "CMSimple_XH 1.7.5";
const FOLDERGALLERY_VERSION = "1.0beta1";

require_once './vendor/autoload.php';
require_once "../../cmsimple/functions.php";

spl_autoload_register(function (string $className) {
    $parts = explode("\\", $className);
    if ($parts[0] !== "Foldergallery") {
        return;
    }
    if (count($parts) === 3) {
        $parts[1] = strtolower($parts[1]);
    }
    $filename = implode("/", array_slice($parts, 1));
    if (is_readable("./classes/$filename.php")) {
        include_once "./classes/$filename.php";
    }
});
