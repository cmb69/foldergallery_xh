<?php

use Foldergallery\Infra\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("403 Forbidden"); exit;}

/**
 * @var View $this
 * @var string $stylesheet
 * @var string $skin_stylesheet
 * @var string $script
 * @var string $skin_script
 */
?>
<!-- foldergallery photoswipe -->
<link rel="stylesheet" href="<?=$stylesheet?>">
<link rel="stylesheet" href="<?=$skin_stylesheet?>">
<script src="<?=$script?>"></script>
<script src="<?=$skin_script?>"></script>
