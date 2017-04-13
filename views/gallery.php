<div class="foldergallery">
    <div class="foldergallery_locator">
<?php foreach ($this->breadcrumbs as $breadcrumb):?>
<?php   if (isset($breadcrumb->url)):?>
        <a href="<?=$this->escape($breadcrumb->url)?>"><?=$this->escape($breadcrumb->name)?></a>
        <?=$this->text('locator_separator')?>
<?php   else:?>
        <span><?=$this->escape($breadcrumb->name)?></span>
<?php   endif?>
<?php endforeach?>
    </div>
<?php foreach ($this->children as $child):?>
<?php   if ($child->isDir):?>
    <figure class="foldergallery_folder">
        <a href="<?=$this->escape($child->url)?>">
            <img src="<?=$this->folderImage()?>">
        </a> 
        <figcaption><?=$this->escape($child->caption)?></figcaption>
    </figure>
<?php   else:?>
    <figure class="foldergallery_image">
        <a class="foldergallery_group" href="<?=$this->escape($child->filename)?>" title="<?=$this->escape($child->caption)?>">
            <img src="<?=$this->escape($child->thumbnail)?>" srcset="<?=$this->escape($child->srcset)?>">
        </a>
        <figcaption><?=$this->escape($child->caption)?></figcaption>
    </figure>
<?php   endif?>
<?php endforeach?>
</div>
