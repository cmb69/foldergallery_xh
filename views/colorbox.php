<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("403 Forbidden"); exit;}

/**
 * @var View $this
 * @var array<string,string> $config
 */
?>
<!-- foldergallery colorbox -->
<script id="foldergallery_colorbox" data-config='<?=$this->json($config)?>'>
jQuery(function ($) {
    $(".foldergallery_group").colorbox($("#foldergallery_colorbox").data("config"));
});
</script>
