<?php
class Kwc_Basic_Image_CacheFullWidth_Root_Component extends Kwf_Component_NoCategoriesRoot
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        unset($ret['generators']['title']);

        $ret['generators']['page']['model'] = new Kwf_Model_FnF(array('data'=>array(
            array('id'=>1, 'pos'=>1, 'visible'=>true, 'name'=>'Foo', 'filename' => 'foo',
                  'parent_id'=>'root', 'component'=>'image', 'is_home'=>false, 'category' =>'main', 'hide'=>false),
            )));
        $ret['generators']['page']['component'] = array(
            'image' => 'Kwc_Basic_Image_CacheFullWidth_Image_Component',
        );

        $ret['generators']['box']['component'] = array(
            'box' => 'Kwc_Basic_Image_CacheFullWidth_Box_Component',
        );

        $ret['contentWidthBoxSubtract'] = array(
            'box' => 100,
        );

        return $ret;
    }
}