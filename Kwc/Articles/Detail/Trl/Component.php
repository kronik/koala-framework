<?php
class Kwc_Articles_Detail_Trl_Component extends Kwc_Directories_Item_Detail_Trl_Component
{
    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $ret['title'] = $this->getData()->row->title;
        return $ret;
    }

    public static function modifyItemData($item)
    {
        parent::modifyItemData($item);
        $item->title = $item->row->title;
        $item->teaser = $item->row->teaser;
        $item->date = $item->chained->row->date;
    }
}
