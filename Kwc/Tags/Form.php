<?php
class Kwc_Tags_Form extends Kwf_Form
{
    protected $_model = 'Kwc_Tags_ComponentModel';
    protected function _initFields()
    {
        $this->setCreateMissingRow(true);
        parent::_initFields();
        $tagsControllerUrl = Kwc_Abstract_Admin::getInstance('Kwc_Tags_Component')->getControllerUrl();
        $this->add(new Kwf_Form_Field_SuperBoxSelect('ComponentToTag', 'Tag', trlKwf('Tags')))
            ->setWidth(300)
            ->setListWidth(300)
            ->setTpl('<tpl for="."><div class="x-combo-list-item">{name} <span style="font-size: 10px; color: gray;">({count_used} '. trlKwf('uses') .')</span></div></tpl>')
            ->setAllowAddNewData(true)
            ->setTriggerAction('all')
            ->setMinChars(1)
            ->setValues($tagsControllerUrl.'/json-data')
            ->setAddNewItemUrl($tagsControllerUrl.'/json-add-item');
    }
}
