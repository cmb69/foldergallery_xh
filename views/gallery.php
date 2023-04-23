<?php

use Foldergallery\Infra\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("403 Forbidden"); exit;}

/**
 * @var View $this
 * @var list<array{name:string,url:string,isLink:bool}> $breadcrumbs
 * @var list<array{caption:string,basename:string|null,filename:string,thumbnail:string,srcset:string,isDir:bool,size:string|null,url:string|null}> $children
 */
?>
<!-- foldergallery gallery -->
<div class="foldergallery">
  <div class="foldergallery_locator">
<?foreach ($breadcrumbs as $breadcrumb):?>
<?  if ($breadcrumb['isLink']):?>
    <a href="<?=$breadcrumb['url']?>"><?=$breadcrumb['name']?></a>
    <?=$this->text('locator_separator')?>
<?  else:?>
    <span><?=$breadcrumb['name']?></span>
<?  endif?>
<?endforeach?>
  </div>
  <div class="foldergallery_figures">
<?foreach ($children as $child):?>
<?  if ($child['isDir']):?>
    <figure class="foldergallery_folder">
      <a href="<?=$child['url']?>">
        <img src="<?=$child['thumbnail']?>" srcset="<?=$child['srcset']?>" alt="<?=$child['caption']?>">
      </a> 
      <figcaption><?=$child['caption']?></figcaption>
    </figure>
<?  else:?>
    <figure class="foldergallery_image">
      <a class="foldergallery_group" href="<?=$child['filename']?>" data-size="<?=$child['size']?>">
        <img src="<?=$child['thumbnail']?>" srcset="<?=$child['srcset']?>" alt="<?=$child['caption']?>">
      </a>
      <figcaption><?=$child['caption']?></figcaption>
    </figure>
<?  endif?>
<?endforeach?>
  </div>
</div>
