<?php
class Kwc_Form_Field_Checkbox_Component extends Kwc_Form_Field_Abstract_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['componentName'] = trlKwfStatic('Form.Checkbox');
        $ret['componentIcon'] = new Kwf_Asset('textfield'); //TODO besseres icon
        return $ret;
    }

    protected function _getFormField()
    {
        $ret = new Kwf_Form_Field_Checkbox($this->getData()->componentId);
        $ret->setFieldLabel($this->getRow()->field_label);
        $ret->setBoxLabel($this->getRow()->box_label);
        $ret->setDefaultValue($this->getRow()->default_value);
        $ret->setAllowBlank(!$this->getRow()->required);
        $ret->setHideLabel($this->getRow()->hide_label);
        return $ret;
    }

    public function getSubmitMessage($row)
    {
        $message = '';
        $label = $this->getFormField()->getFieldLabel();
        if (!$label) {
            $label = $this->getFormField()->getBoxLabel();
        }
        if ($label) {
            if ($row->{$this->getFormField()->getName()}) {
                $message = $label.': '.$this->getData()->trlKwf('Yes');
            } else {
                $message = $label.': '.$this->getData()->trlKwf('No');
            }
        }
        return $message;
    }
}
