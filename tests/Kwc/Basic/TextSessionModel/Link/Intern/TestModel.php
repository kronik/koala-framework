<?php
class Kwc_Basic_TextSessionModel_Link_Intern_TestModel extends Kwc_Basic_LinkTag_Intern_Model
{
    public function __construct($config = array())
    {
        $config['proxyModel'] = new Kwf_Model_Session(array(
            'namespace' => 'TextSessionModel_Link_Intern_TestModel',
            'primaryKey' => 'component_id',
        ));
        parent::__construct($config);
    }
}
