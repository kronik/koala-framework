<?php
class Kwc_Basic_Text_Link_Extern_TestModel extends Kwc_Basic_LinkTag_Extern_Model
{
    public function __construct($config = array())
    {
        $config['proxyModel'] = new Kwf_Model_FnF(array(
            'primaryKey' => 'component_id',
            'columns' => array(),
            'data'=> array(
                array('component_id'=>'1007-l1-child', 'target'=>'http://vivid.com')
            )
        ));
        parent::__construct($config);
    }
}

