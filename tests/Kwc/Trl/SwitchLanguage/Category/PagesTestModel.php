<?php
class Kwc_Trl_SwitchLanguage_Category_PagesTestModel extends Kwf_Model_FnF
{
    protected $_data = array(
            array('id'=>1, 'pos'=>1, 'visible'=>true, 'name'=>'Home de', 'filename' => 'home_de',
                  'parent_id'=>'root-master-main', 'component'=>'empty', 'is_home'=>true, 'hide'=>false),
            array('id'=>2, 'pos'=>1, 'visible'=>true, 'name'=>'Test', 'filename' => 'test',
                  'parent_id'=>'root-master-main', 'component'=>'empty', 'is_home'=>false, 'hide'=>false),
            array('id'=>3, 'pos'=>2, 'visible'=>false, 'name'=>'Test2', 'filename' => 'test2',
                  'parent_id'=>'root-master-main', 'component'=>'empty', 'is_home'=>false, 'hide'=>false),
            array('id'=>4, 'pos'=>3, 'visible'=>true, 'name'=>'Test3', 'filename' => 'test3',
                  'parent_id'=>'root-master-main', 'component'=>'empty', 'is_home'=>false, 'hide'=>false),
    );
}
