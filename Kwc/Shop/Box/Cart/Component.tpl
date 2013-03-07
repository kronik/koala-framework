<div class="<?=$this->cssClass?>" id="<?=$this->data->componentId?>">
    <div class="cartList">
        <h2><?=$this->data->trlKwf('Cart')?></h2>
        <? if (!$this->items) { ?>
            <p><?=$this->data->trlKwf('Cart is empty')?></p>
    </div>
        <? } else { ?>
            <div class="countProducts">
                <p>
                    <?=$this->data->trlKwf('You cart contains {0} products','<strong>'.$this->order->getTotalAmount().'</strong>')?>
                </p>
            </div>
            <table class="tblBoxCart" cellspacing="0" cellpadding="0">
            <?
            $c=1;
            $j=count($this->items);
            foreach ($this->items as $item) { ?>
                <tr class="products<?=($c%2==0 ? ' row2' : ' row1');?>">
                    <td class="product"><?=$this->componentLink($item->product, $item->text)?></td>
                    <? foreach($item->additionalOrderData as $d) { ?>
                        <td class="<?=$d['class']?>"><?=$this->data->trlStaticExecute($d['name'])?>: <?=$d['value']?></td>
                    <? } ?>
                    <td class="price"><?=$this->money($item->price)?></td>
                </tr>
                <tr class="<?=($c==$j ? 'lastline' : 'line');?>">
                    <td colspan="<?=(4+count($item->additionalOrderData))?>">
                        <div class="line"></div>
                    </td>
                </tr>
                <? $c++;
            } ?>
            </table>
            <ul class="moneyInfo webListNone">
                <? foreach ($this->sumRows as $row) { ?>
                    <li<? if(isset($row['class'])) {?> class="<?=$row['class']?>"<? } ?>>
                        <span class="text"><?=$this->data->trlStaticExecute($row['text'])?></span>
                        <span class="price"><?=$this->money($row['amount'],'')?></span>
                        <div class="clear"></div>
                    </li>
                <? } ?>
            </ul>
    </div>
    <ul class="links">
        <? foreach ($this->links as $link) { ?>
            <? if ($link['component']) { ?>
            <li><?=$this->componentLink($link['component'], $this->data->trlStaticExecute($link['text']))?></li>
            <? } ?>
        <? } ?>
    </ul>
    <? } ?>
</div>