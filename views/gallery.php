<?php

use Foldergallery\Infra\View;

/**
 * @var View $this
 * @var list<stdClass> $breadcrumbs
 * @var list<stdClass> $children
 */
?>
<div class="foldergallery">
    <div class="foldergallery_locator">
<?php foreach ($breadcrumbs as $breadcrumb):?>
<?php   if ($breadcrumb->isLink):?>
        <a href="<?=$breadcrumb->url?>"><?=$breadcrumb->name?></a>
        <?=$this->text('locator_separator')?>
<?php   else:?>
        <span><?=$breadcrumb->name?></span>
<?php   endif?>
<?php endforeach?>
    </div>
    <div class="foldergallery_figures">
<?php foreach ($children as $child):?>
<?php   if ($child->isDir):?>
        <figure class="foldergallery_folder">
            <a href="<?=$child->url?>">
                <img src="<?=$child->thumbnail?>" srcset="<?=$child->srcset?>" alt="<?=$child->caption?>">
            </a> 
            <figcaption><?=$child->caption?></figcaption>
        </figure>
<?php   else:?>
        <figure class="foldergallery_image">
            <a class="foldergallery_group" href="<?=$child->filename?>" data-size="<?=$child->size?>">
                <img src="<?=$child->thumbnail?>" srcset="<?=$child->srcset?>" alt="<?=$child->caption?>">
            </a>
            <figcaption><?=$child->caption?></figcaption>
        </figure>
<?php   endif?>
<?php endforeach?>
    </div>
</div>
