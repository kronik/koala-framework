<?php
class Kwc_Articles_Detail_Feedback_Form_Trl_Component extends Kwc_Feedback_Form_Trl_Component
{
    public static function getSettings($masterComponentClass)
    {
        $ret = parent::getSettings($masterComponentClass);
        $ret['generators']['child']['component'] = 'Kwc_Articles_Detail_Feedback_Form_Trl_Form_Component';
        return $ret;
    }
}
