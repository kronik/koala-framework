<?php
class Kwc_Trl_LinkIntern_LinkTagIntern_Component extends Kwc_Basic_LinkTag_Intern_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['ownModel'] = 'Kwc_Trl_LinkIntern_LinkTagIntern_TestModel';
        return $ret;
    }
}
