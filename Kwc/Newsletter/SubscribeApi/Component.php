<?php
class Kwc_Newsletter_SubscribeApi_Component extends Kwc_Form_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['componentName'] = trlKwfStatic('Newsletter subscribing');
        $ret['placeholder']['submitButton'] = trlKwfStatic('Subscribe the newsletter');
        return $ret;
    }

    public static function validateSettings($settings, $componentClass)
    {
        parent::validateSettings($settings, $componentClass);
        if (!Kwf_Config::getValue('newsletter.subscribeApiDomain')) {
            throw new Kwf_Exception("newsletter.subscribeApiDomain config setting is required for '$componentClass'");
        }
    }

    protected function _processParams(array $params)
    {
        return $params;
    }

    public function insertSubscription(array $params)
    {
        $c = new Zend_Http_Client($this->_getSubscribeApiUrl());
        $c->setConfig(array(
            'timeout' => 30
        ));
        $params = $this->_processParams($params);
        $c->setParameterPost($params);
        $response = $c->request(Zend_Http_Client::POST);
        if (!$response->isSuccessful()) {
            throw new Kwf_Exception("subscribe failed: ".$response->getBody());
        } else {
            $res = json_decode($response->getBody());
            if (!$res) {
                throw new Kwf_Exception("can't parse response: ".$response->getBody());
            }
            if (!$res->success) {
                throw new Kwf_Exception("subscribe failed: ".$response->getBody());
            }
        }
    }

    protected function _getSubscribeApiUrl()
    {
        $domain = Kwf_Config::getValue('newsletter.subscribeApiDomain');
        return "http://$domain/admin/component/edit/Newsletter_Subscribe_Component/Api/json-insert";
    }

    protected function _initForm()
    {
        parent::_initForm();
        $this->_form->setModel(new Kwf_Model_FnF());
    }

    protected function _afterInsert(Kwf_Model_Row_Interface $row)
    {
        parent::_afterInsert($row);
        $params = array(
            'gender' => $row->gender,
            'title' => $row->title,
            'firstname' => $row->firstname,
            'lastname' => $row->lastname,
            'email' => $row->email,
            'format' => $row->format,
        );
        $this->insertSubscription($params);
    }
}
