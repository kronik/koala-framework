<?php
class Kwf_Component_Plugin_Facebook_ThirdPartyCookies extends Kwf_Component_Plugin_Abstract
    implements Kwf_Component_Plugin_Interface_ViewReplace
    //NOTE: if RedirectToPagetab plugin is also used in same component, you have to implement
    //thirdpartycookies first!
{
    public function replaceOutput()
    {
        $facebook = Kwf_Util_Facebook_Api::getInstance();
        //no redirect if cli, or address contains assets, admin or kwf
        if (php_sapi_name() != 'cli' && !preg_match('#^/admin|^/kwf|^/assets#',
            $_SERVER['REQUEST_URI'])) {
            if (!isset($_COOKIE['PHPSESSID'])) {
                Zend_Session::start();
                if (!isset($_COOKIE['PHPSESSID'])) {
                    $serverDomain = 'http://'.Kwf_Config::getValue('server.domain');
                    return "<script>top.location.href=\"{$serverDomain}/home\";</script>";
                }
            }
        }
        return false;
    }
}
