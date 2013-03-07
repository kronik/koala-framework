<?php
class Kwc_Mail_Editable_Trl_Component extends Kwc_Mail_Trl_Component
{
    public static function getSettings($masterComponentClass)
    {
        $ret = parent::getSettings($masterComponentClass);
        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $ret['content'] = $this->getData()->getChildComponent('-content');
        return $ret;
    }

    // used here: Kwc_Mail_Editable_ComponentsModel
    public function getNameForEdit()
    {
        return $this->getData()->chained->getComponent()->getNameForEdit().
                ' ('.$this->getData()->getLanguage().')';
    }
}
