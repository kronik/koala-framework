<?php
/**
 * List mit child daneben; list ist immer sichtbar
 * + multi-file-upload
 */
class Kwc_Abstract_List_ExtConfigListUpload extends Kwc_Abstract_List_ExtConfigList
{
    protected function _getConfig()
    {
        $ret = parent::_getConfig();

        $cacheId = 'extConfig_multiFileUpload_'.$this->_class;
        $multiFileUpload = Kwf_Cache_Simple::fetch($cacheId, $success);
        if (!$success) {
            $multiFileUpload = false;
            $form = Kwc_Abstract_Form::createChildComponentForm($this->_class, 'child');
            if ($form && $field = $this->_getFileUploadField($form)) {
                $multiFileUpload = array(
                    'allowOnlyImages' => $field->getAllowOnlyImages(),
                    'maxResolution' => $field->getMaxResolution(),
                    'maxResolution' => $field->getMaxResolution(),
                    'fileSizeLimit' => $field->getFileSizeLimit(),
                );
            }
            Kwf_Cache_Simple::add($cacheId, $multiFileUpload);
        }
        $ret['list']['multiFileUpload'] = $multiFileUpload;
        return $ret;
    }

    private function _getFileUploadField($form)
    {
        foreach ($form as $i) {
            if ($i instanceof Kwf_Form_Field_File) {
                return $i;
            }
            if (!($i instanceof Kwf_Form_Container_Cards)) { //in cards nicht reinschaun, bei Links wollen wir keinen multi upload
                $ret = $this->_getFileUploadField($i);
                if ($ret) return $ret;
            }
        }
        return null;
    }
}
