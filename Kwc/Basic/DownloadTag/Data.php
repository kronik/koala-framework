<?php
class Kwc_Basic_DownloadTag_Data extends Kwf_Component_Data
{
    public function __get($var)
    {
        if ($var == 'url') {
            $m = Kwc_Abstract::createModel($this->componentClass);
            $row = $m->getRow($this->dbId);
            if (!$row) return null;
            $fRow = $row->getParentRow('File');
            if (!$fRow) return null;
            $filename = $row->filename;
            if (!$filename) {
                $filename = $fRow->filename;
            }
            $filename .= '.'.$fRow->extension;
            return Kwf_Media::getUrl($this->componentClass, $this->componentId, 'default', $filename);
        } else if ($var == 'rel') {
            $ret = 'popup_blank';
            return $ret;
        } else {
            return parent::__get($var);
        }
    }
}
