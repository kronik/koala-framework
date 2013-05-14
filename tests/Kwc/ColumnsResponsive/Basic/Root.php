<?php
class Kwc_ColumnsResponsive_Basic_Root extends Kwf_Component_NoCategoriesRoot
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['page']['model'] = new Kwf_Model_FnF(array('data'=>array(
            array('id'=>3000, 'pos'=>1, 'visible'=>true, 'name'=>'Foo', 'filename' => 'foo',
                  'parent_id'=>'root', 'component'=>'columns', 'is_home'=>false, 'category' =>'main', 'hide'=>false),
        )));
        $ret['generators']['page']['component'] = array(
            'columns' => 'Kwc_ColumnsResponsive_Basic_Paragraphs_Component',
        );

        unset($ret['generators']['title']);
        unset($ret['generators']['box']);

        $ret['contentWidth'] = 900;
        return $ret;
    }
}
