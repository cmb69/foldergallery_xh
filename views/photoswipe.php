<?php

use Plib\View;

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
<link rel="stylesheet" href="<?=$this->esc($stylesheet)?>">
<script type="module">
import PhotoSwipeLightbox from "<?=$this->esc($lightbox)?>";
const lightbox = new PhotoSwipeLightbox({
    gallery: ".foldergallery",
    children: ".foldergallery_image a",
    bgOpacity: <?=$this->esc($opacity)?>,
    pswpModule: () => import("<?=$this->esc($core)?>"),
});
lightbox.init();
</script>
