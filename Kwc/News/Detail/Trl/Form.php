<?php
class Kwc_News_Detail_Trl_Form extends Kwc_News_Detail_Abstract_Trl_Form
{
    protected function _init()
    {
        parent::_init();
        $detail = Kwc_Abstract::getChildComponentClass($this->getDirectoryClass(), 'detail');
        $this->add(Kwc_Abstract_Form::createChildComponentForm($detail, '-image', 'image'));
    }
}
