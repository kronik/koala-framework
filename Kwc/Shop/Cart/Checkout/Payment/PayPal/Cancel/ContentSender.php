<?php
class Kwc_Shop_Cart_Checkout_Payment_PayPal_Cancel_ContentSender extends Kwf_Component_Abstract_ContentSender_Default
{
    public function sendContent($includeMaster)
    {
        $session = new Zend_Session_Namespace('kwcShopCart');
        if ($session->paypalCartId) {
            Kwc_Shop_Cart_Orders::setCartOrderId($session->paypalCartId);
            $order = Kwf_Model_Abstract::getInstance('Kwc_Shop_Cart_Orders')->getCartOrder();
            if ($order) {
                $order->status = 'cart';
                $order->save();
            }
            unset($session->paypalCartId);
        }
        header('Location: ' . $this->_data->parent->parent->parent->getUrl());
    }
}
