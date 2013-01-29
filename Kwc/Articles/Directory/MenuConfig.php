<?php
class Kwc_Articles_Directory_MenuConfig extends Kwf_Component_Abstract_MenuConfig_SameClass
{
    public function addResources(Kwf_Acl $acl)
    {
        parent::addResources($acl);

        $acl->add(
            new Kwf_Acl_Resource_ComponentClass_MenuUrl(
                'kwc_article_authors', array('text'=>trlKwf('Authors'), 'icon'=>'user_red'),
                Kwc_Admin::getInstance($this->_class)->getControllerUrl('Authors'),
                $this->_class
            ),
            'settings'
        );

        $acl->add(
            new Kwf_Acl_Resource_ComponentClass_MenuUrl(
                'kwc_article_categories', array('text'=>trlKwf('Categories'), 'icon'=>'application_side_tree'),
                Kwc_Admin::getInstance($this->_class)->getControllerUrl('Categories'),
                $this->_class
            ),
            'settings'
        );

        $acl->add(
            new Kwf_Acl_Resource_ComponentClass_MenuUrl(
                'kwc_article_tags', array('text'=>trlKwf('Tags'), 'icon'=>'tag_blue_edit'),
                Kwc_Admin::getInstance($this->_class)->getControllerUrl('Tags').'?type=tag',
                $this->_class
            ),
            'settings'
        );

        $acl->add(new Kwf_Acl_Resource_ComponentClass_MenuUrl(
            'kwc_article_tag-suggestions', array('text'=>trlKwf('New tags'), 'icon'=>'tag_blue_edit'),
            Kwc_Admin::getInstance($this->_class)->getControllerUrl('TagSuggestions'),
            $this->_class
            ),
            'kwf_component_root'
        );
    }
}
