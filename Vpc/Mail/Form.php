<?php
class Vpc_Mail_Form extends Vpc_Abstract_Form
{
    protected function _initFields()
    {
        parent::_initFields();

        $this->add(new Vps_Form_Field_TextField('subject', trlVps('Subject')))
            ->setAllowBlank(false)
            ->setWidth(300);
        $this->add(new Vps_Form_Field_TextField('from_email', trlVps('From Address')))
            ->setVtype('email')
            ->setWidth(300);
        $this->add(new Vps_Form_Field_TextField('from_name', trlVps('From Name')))
            ->setWidth(300);
        $this->add(new Vps_Form_Field_TextField('reply_email', trlVps('Reply Address')))
            ->setVtype('email')
            ->setWidth(300);
    }
}