<?php

use Foldergallery\Infra\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("403 Forbidden"); exit;}

/**
 * @var View $this
 * @var string $stylesheet
 * @var string $core
 * @var string $lightbox
 * @var string $opacity
 */
?>
<!-- foldergallery photoswipe -->
<link rel="stylesheet" href="<?=$stylesheet?>">
<script type="module">
import PhotoSwipeLightbox from "<?=$lightbox?>";
const lightbox = new PhotoSwipeLightbox({
    gallery: ".foldergallery",
    children: ".foldergallery_image a",
    bgOpacity: <?=$opacity?>,
    pswpModule: () => import("<?=$core?>"),
});
lightbox.init();
</script>
