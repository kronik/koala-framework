<?php
class Kwc_Root_Category_Trl_Component extends Kwc_Chained_Trl_Component
{
    public static function getSettings($masterComponentClass)
    {
        $ret = parent::getSettings($masterComponentClass);
        $ret['generators']['page']['class'] = 'Kwc_Root_Category_Trl_Generator';
        $ret['generators']['page']['model'] = 'Kwc_Root_Category_Trl_GeneratorModel';
        return $ret;
    }
}
