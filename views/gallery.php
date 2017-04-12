<div class="foldergallery">
<?=$this->locator()?>
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
