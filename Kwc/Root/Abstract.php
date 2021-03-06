<?php
class Kwc_Root_Abstract extends Kwc_Abstract
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['box'] = array(
            'class' => 'Kwf_Component_Generator_Box_Static',
            'component' => array(),
            'inherit' => true,
            'priority' => 0
        );
        $ret['generators']['title'] = array(
            'class' => 'Kwf_Component_Generator_Box_Static',
            'component' => 'Kwc_Box_Title_Component',
            'inherit' => true,
            'priority' => 0
        );
        $ret['componentName'] = trlKwf('Root');
        $ret['contentWidth'] = 600;
        $ret['contentWidthBoxSubtract'] = array();
        return $ret;
    }

    public function formatPath($parsedUrl)
    {
        if (!Kwf_Config::getValue('server.domain')) {
            //domain is optional (but recommended)
            //for easy setup of examples just ignore the domain (as we don't have anything to compare to anyway)
            return $parsedUrl['path'];
        }
        $b = Kwf_Config::getValue('server.domain') == $parsedUrl['host']
            || Kwf_Config::getValue('server.previewDomain') == $parsedUrl['host'];
        if (!$b && isset($parsedUrl['port'])) {
            $b = Kwf_Config::getValue('server.domain') == $parsedUrl['host'].':'.$parsedUrl['port']
                || Kwf_Config::getValue('server.previewDomain') == $parsedUrl['host'].':'.$parsedUrl['port'];
        }
        if (!$b) {
            $p =  Kwf_Config::getValue('server.noRedirectPattern');
            if (!$p) return null;
            if (!preg_match('/'.$p.'/', $parsedUrl['host'])) {
                return null;
            }
        }
        return $parsedUrl['path'];
    }

    public function getPageByUrl($path, $acceptLangauge)
    {
        if ($path == '') {
            $ret = $this->getData()->getChildPage(array('home' => true), array());
        } else {
            foreach (Kwc_Abstract::getComponentClasses() as $c) {
                if (Kwc_Abstract::getFlag($c, 'shortcutUrl')) {
                    $ret = call_user_func(array($c, 'getDataByShortcutUrl'), $c, $path);
                    if ($ret) return $ret;
                }
            }
            $ret = $this->getData()->getChildPageByPath($path);
        }

        if ($ret && !$ret->isPage && Kwf_Component_Abstract::getFlag($ret->componentClass, 'hasHome')) {
            $ret = $ret->getChildPage(array('home' => true), array());
        }
        return $ret;
    }

    protected function _getMasterChildContentWidth(Kwf_Component_Data $sourcePage)
    {
        $ret = $this->_getSetting('contentWidth');
        foreach ($this->_getSetting('contentWidthBoxSubtract') as $box=>$width) {
            //TODO hier sollte eigentlich der boxname verwendet werden, der muss nicht die id sein
            $c = $sourcePage->getChildComponent('-'.$box);
            if ($c && $c->hasContent()) {
                $ret -= $width;
            }
        }
        return $ret;
    }
}
