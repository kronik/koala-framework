<?php
class Kwc_User_Detail_Guestbook_Component extends Kwc_Posts_Directory_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['componentName'] = trlKwf('Guestbook');
        $ret['placeholder']['writeText'] = trlKwf('New Entry');
        return $ret;
    }

    public function getSelect()
    {
        $select = parent::getSelect();
        $select->order('create_time', 'DESC');
        return $select;
    }
}