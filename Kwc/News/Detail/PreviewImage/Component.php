<?php
class Kwc_News_Detail_PreviewImage_Component extends Kwc_Basic_Image_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['dimensions'] = array(
            array(
                'width' => 120,
                'height' => 90,
                'scale' => Kwf_Media_Image::SCALE_BESTFIT
            )
        );
        return $ret;
    }
}
