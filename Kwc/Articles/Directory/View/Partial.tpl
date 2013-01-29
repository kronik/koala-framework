<div class="item">
    <div class="categories">
        <?=implode(' | ', $this->item->categories)?>
    </div>
    <div class="previewImage">
        <?=$this->componentLink($this->item, $this->component($this->item->getChildComponent('-previewImage')))?>
    </div>
    <div class="previewCenter">
        <div class="date"><?=$this->date($this->item->row->date)?>:</div>
        <h3><?=$this->componentLink($this->item, $this->item->row->title)?></h3>
        <div class="clear"></div>
        <div class="teaser">
            <?=$this->item->row->teaser?>
            <?=$this->componentLink($this->item, 'weiterlesen')?>
        </div>
    </div>
    <div class="clear"></div>
</div>
