<?php
class Kwc_Shop_Cart_Detail_Form_Component extends Kwc_Form_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['child']['component']['success'] = false;
        return $ret;
    }

    protected function _initForm()
    {
        $addToCart = $this->getData()->parent->getComponent()->getAddToCartForm();
        if ($addToCart) {
            $f = $addToCart->getComponent()->getForm();
            $this->_form = clone $f;
            $productRow = $this->getData()->parent->getComponent()->getOrderProductRow();
            $this->_form->setName('order'.$productRow->id);
            $this->_form->setId($productRow->id);
        }
    }
}
