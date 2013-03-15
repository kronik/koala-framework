<?php
class Kwc_Form_Dynamic_Form_Component extends Kwc_Form_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['child']['component']['success'] = 'Kwc_Form_Dynamic_Form_Success_Component';
        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $ret['paragraphs'] = $this->getData()->parent->getChildComponent('-paragraphs');
        return $ret;
    }

    protected function _initForm()
    {
        $this->_form = new Kwf_Form('form');
        $referenceMap = array();
        $dependentModels = array();
        foreach ($this->getData()->parent->getChildComponent('-paragraphs')->getRecursiveChildComponents(array('flags'=>array('formField'=>true))) as $c) {
            $f = $c->getComponent()->getFormField();
            $this->_form->fields->add($f);
            if ($f instanceof Kwf_Form_Field_File) {
                $referenceMap[$f->getName()] = array(
                    'refModelClass' => 'Kwf_Uploads_Model',
                    'column' => $f->getName()
                );
            } else if ($f instanceof Kwf_Form_Field_MultiCheckbox) {
                $dependentModels[$f->getName()] = 'Kwc_Form_Field_MultiCheckbox_DataToValuesModel';
            }
        }
        $this->_form->setModel($this->_createModel(array('referenceMap'=>$referenceMap,
                                                         'dependentModels'=>$dependentModels)));
    }

    protected function _createModel(array $config)
    {
        $config['componentClass'] = get_class($this);
        $config['mailerClass'] = 'Kwf_Mail';
        return new Kwc_Form_Dynamic_Form_MailModel($config);
    }

    protected function _beforeInsert(Kwf_Model_Row_Interface $row)
    {
        parent::_beforeInsert($row);
        $row->component_id = $this->getData()->parent->dbId;
    }

    protected function _afterInsert(Kwf_Model_Row_Interface $row)
    {
        parent::_afterInsert($row);

        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        } else {
            $host = Kwf_Registry::get('config')->server->domain;
        }
        if (substr($host, 0, 4) == 'www.') {
            $host = substr($host, 4);
        }
        $row->setFrom("noreply@$host");
        $settings = $this->getData()->parent->getComponent()->getMailSettings();
        $row->addTo($settings['recipient']);
        $row->addCc($settings['recipient_cc']);
        $row->setSubject($settings['subject']);
        $msg = '';
        $formFieldComponents = $this->getData()->parent
            ->getChildComponent('-paragraphs')
            ->getRecursiveChildComponents(array('flags'=>array('formField'=>true)));
        foreach ($formFieldComponents as $c) {
            $message = $c->getComponent()->getSubmitMessage($row);
            if ($message) {
                $msg .= $message."\n";
            }
        }
        $row->sent_mail_content_text = $msg;

        $this->_beforeSendMail($row);

        $row->sendMail(); //manuell aufrufen weils beim speichern nicht automatisch gemacht wird (da da der content nocht nicht vorhanden ist)
    }

    protected function _beforeSendMail(Kwf_Model_Row_Interface $row)
    {
    }
}
