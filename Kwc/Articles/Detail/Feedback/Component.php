<?php
class Kwc_Articles_Detail_Feedback_Component extends Kwc_Feedback_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['child']['component']['form'] = 'Kwc_Articles_Detail_Feedback_Form_Component';
        return $ret;
    }
}
