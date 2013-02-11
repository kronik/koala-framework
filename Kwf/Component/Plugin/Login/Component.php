<?php
class Kwf_Component_Plugin_Login_Component extends Kwf_Component_Plugin_Password_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['loginForm']['component'] = 'Kwc_User_Login_Component';
        $ret['validUserRoles'] = null;
        return $ret;
    }

    public function isLoggedIn()
    {
        if (!Zend_Session::sessionExists() && !Kwf_Config::getValue('autologin')) return false;
        $user = Zend_Registry::get('userModel')->getAuthedUser();
        if (is_null($user)) return false;
        if (!$this->_getSetting('validUserRoles')) return true;
        if (in_array($user->role, $this->_getSetting('validUserRoles'))) {
            return true;
        }
        return false;
    }

    public function skipProcessInput()
    {
        return !$this->isLoggedIn();
    }
}
