<?php
class Kwc_Form_Field_File_Component extends Kwc_Form_Field_Abstract_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['componentName'] = trlKwfStatic('Form.File Upload');
        $ret['componentIcon'] = new Kwf_Asset('textfield');
        return $ret;
    }

    protected function _getFormField()
    {
        $ret = new Kwf_Form_Field_File($this->getData()->componentId);
        $ret->setFieldLabel($this->getRow()->field_label);
        $ret->setAllowBlank(!$this->getRow()->required);
        $ret->setHideLabel($this->getRow()->hide_label);
        return $ret;
    }

    public function getSubmitMessage($row)
    {
        $message = '';
        $uploadRow = $row->getParentRow($this->getFormField()->getName());
        if ($uploadRow) {
            $row->addAttachment($uploadRow);
            $message = $this->getFormField()->getFieldLabel()
                .": {$uploadRow->filename}.{$uploadRow->extension} "
                        .$this->getData()->trlKwf('attached');
        }
        return $message;
    }
}