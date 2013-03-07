<?php
class Kwc_Shop_AddToCart_OrderProductData extends Kwc_Shop_AddToCartAbstract_OrderProductData
{
    public function getPrice($orderProduct)
    {
        return $orderProduct->getParentRow('ProductPrice')->price * $orderProduct->amount;
    }

    public function getAmount($orderProduct)
    {
        return $orderProduct->amount;
    }

    public function getProductText($orderProduct)
    {
        $product = $orderProduct->getParentRow('ProductPrice')->getParentRow('Product');
        return $product->__toString();
    }

    public function getAdditionalOrderData($row)
    {
        $ret = parent::getAdditionalOrderData($row);
        $ret[] = array(
            'class' => 'amount',
            'name' => trlKwfStatic('Amount'),
            'value' => $row->amount
        );
        return $ret;
    }

    public function alterBackendOrderForm(Kwc_Shop_AddToCartAbstract_FrontendForm $form)
    {
        $component = null;
        foreach (Kwc_Abstract::getComponentClasses() as $c) {
            if (is_instance_of($c, 'Kwc_Shop_Products_Detail_Component')) {
                $generators = Kwc_Abstract::getSetting($c, 'generators');
                foreach ($generators['addToCart']['component'] as $key => $class) {
                    if ($class == $this->_class) $component = $key;
                }
            }
        }
        $m = Kwf_Model_Abstract::getInstance('Kwc_Shop_Products');
        $s = $m->select();
        $s->whereEquals('visible', 1);
        if ($component) $s->whereEquals('component', $component);
        $s->order('pos');
        $data = array();
        foreach ($m->getRows($s) as $product) {
            $data[] = array(
                $product->current_price_id,
                $product->__toString().' ('.$product->current_price.' €)'
            );
        }
        $form->prepend(new Kwf_Form_Field_Select('shop_product_price_id', trlKwfStatic('Product')))
            ->setValues($data)
            ->setAllowBlank(false);
    }
}
