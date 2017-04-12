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
    <div class="foldergallery_folder">
        <a href="<?=$this->urlPrefix()?><?=$this->escape($child->basename)?>">
            <img src="<?=$this->folderImage()?>">
        </a> 
        <div><?=$this->escape($child->name)?></div>
    </div>
<?php   else:?>
    <div class="foldergallery_image">
        <a class="foldergallery_group" href="<?=$this->escape($child->filename)?>" title="<?=$this->escape($child->name)?>">
            <img src="<?=$this->escape($child->filename)?>">
        </a>
        <div><?=$this->escape($child->name)?></div>
    </div>
<?php   endif?>
<?php endforeach?>
</div>
