<?php
class Vpc_Guestbook_Component extends Vpc_Posts_Directory_Component
{
    /**
     * Der Post ist erst inaktiv und muss erst freigeschaltet werden
     */
    const INACTIVE_ON_SAVE = 'inactive_on_save';
    /**
     * Der Post ist sofort aktiv und kann später deaktiviert werden
     */
    const ACTIVE_ON_SAVE = 'active_on_save';

    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['detail']['component'] = 'Vpc_Guestbook_Detail_Component';
        $ret['generators']['write']['component'] = 'Vpc_Guestbook_Write_Component';
        $ret['generators']['child']['component']['mail'] = 'Vpc_Guestbook_Mail_Component';
        $ret['generators']['child']['component']['activate'] = 'Vpc_Guestbook_ActivatePost_Component';
        $ret['generators']['child']['component']['deactivate'] = 'Vpc_Guestbook_DeactivatePost_Component';

        return $ret;
    }

    public function getSelect($overrideValues = array())
    {
        $ret = parent::getSelect($overrideValues);
        $ret->order('id', 'DESC');
        return $ret;
    }
}