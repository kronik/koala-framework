<?php
class Vpc_Shop_Cart_Checkout_Payment_Abstract_Component extends Vpc_Abstract_Composite_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['viewCache'] = false;

        $ret['generators']['child']['component']['confirmLink'] = 'Vpc_Shop_Cart_Checkout_Payment_Abstract_ConfirmLink_Component';

        $ret['generators']['confirm'] = array(
            'class' => 'Vps_Component_Generator_Page_Static',
            'component' => 'Vpc_Shop_Cart_Checkout_Payment_Abstract_Confirm_Component',
            'name' => trlVps('Send order')
        );

        $ret['generators']['mail'] = array(
            'class' => 'Vps_Component_Generator_Static',
            'component' => 'Vpc_Shop_Cart_Checkout_Payment_Abstract_Mail_Component',
        );

        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $checkout = Vps_Component_Data_Root::getInstance()
            ->getComponentByClass(
                'Vpc_Shop_Cart_Component',
                array('subroot' => $this->getData())
            )
            ->getChildComponent('_checkout');

        $ret['order'] = $this->_getOrder();
        $ret['orderProducts'] = $ret['order']->getChildRows('Products');

        $ret['sumRows'] = $this->_getSumRows($this->_getOrder());

        $ret['paymentTypeText'] = $this->_getSetting('componentName');

        return $ret;
    }

    protected function _getOrder()
    {
        return Vps_Model_Abstract::getInstance('Vpc_Shop_Cart_Orders')
                            ->getCartOrder();
    }

    //kann überschrieben werden um zeilen pro payment zu ändern
    protected function _getSumRows($order)
    {
        return $this->getData()->parent->getComponent()->getSumRows($order);
    }

    //da kann zB eine Nachnahmegebühr zurückgegeben werden
    //darf nur von Vpc_Shop_Cart_Checkout_Component::getAdditionalSumRows() aufgerufen werden!
    public function getAdditionalSumRows()
    {
        return array();
    }

    public function sendConfirmMail($order)
    {
        $mail = $this->getData()->getChildComponent('-mail')->getComponent();
        $data = array(
            'order' => $order,
            'sumRows' => $this->getData()->parent->getComponent()->getSumRows($order)
        );
        $mail->send($order, $data);
    }

    public function confirmOrder($order)
    {
        $this->sendConfirmMail($order);

//         $order->status = 'ordered';
        $order->date = new Zend_Db_Expr('NOW()');
        $order->save();
    }
}