<div class="<?=$this->cssClass?>">
    <input type="hidden" class="redirectTo" value="<?=$this->redirectToUrl?>" />
    <h1><?=$this->data->trlKwf('Logged In')?></h1>
    <p><?=$this->data->trlKwf('You have been logged in sucessfully.')?></p>
    <p><?=$this->data->trlKwf("If the needed page doesn't load automatically,")?>
    <?=$this->link($this->redirectToUrl, $this->data->trlKwf('please click here'))?>.</p>
</div>