<?php
class Kwc_Paragraphs_ParagraphsModel extends Kwc_Paragraphs_Model
{
    public function __construct($config = array())
    {
        $config['proxyModel'] = new Kwf_Model_FnF(array(
            'columns' => array('id', 'component_id', 'device_visible', 'pos', 'visible', 'component'),
            'primaryKey' => 'id',
            'data'=> array(
                array('id' => 1, 'component_id'=>'root', 'device_visible' => 'all', 'pos'=>1, 'visible' => 1, 'component' => 'paragraph'),
                array('id' => 2, 'component_id'=>'root', 'device_visible' => 'all', 'pos'=>2, 'visible' => 0, 'component' => 'paragraph'),
                array('id' => 11, 'component_id'=>'foo', 'device_visible' => 'all', 'pos'=>1, 'visible' => 1, 'component' => 'paragraph')
            )
        ));
        parent::__construct($config);
    }
}
