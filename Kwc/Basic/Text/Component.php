<?php
class Kwc_Basic_Text_Component extends Kwc_Abstract
{
    public static function getSettings()
    {
        $ret = array_merge(parent::getSettings(), array(
            'ownModel'          => 'Kwc_Basic_Text_Model',
            'componentName'     => trlKwfStatic('Text'),
            'componentIcon'     => new Kwf_Asset('paragraph_page'),
            'width'             => 550,
            'height'            => 400,
            'enableAlignments'  => false,
            'enableColors'      => false,
            'enableFont'        => false,
            'enableFontSize'    => false,
            'enableFormat'      => true,
            'enableLists'       => true,
            'enableSourceEdit'  => true,
            'enableBlock'       => false,
            'enableUndoRedo'    => true,
            'enableLinks'       => false, //nur wenn link komponente nicht vorhanden
            'enableInsertChar'  => true,
            'enablePastePlain'  => true,
            'enableTidy'        => true,
            'stylesIdPattern'   => false, //zB: '^company_[0-9]+',
            'enableStyles'      => true,
            'enableStylesEditor'=> true,
            'enableTagsWhitelist'=> true,
            'defaultText'       => Kwc_Abstract::LOREM_IPSUM,

            //veraltert NICHT VERWENDEN!! (in der Kwc_Mail komponente ist ein besserer ersatz)
            'emailStyles'       => array()
        ));

        $ret['stylesModel'] = 'Kwc_Basic_Text_StylesModel';

        $ret['generators']['child'] = array(
            'class' => 'Kwc_Basic_Text_Generator',
            'component' => array(
                //auf false setzen um buttons zu deaktivieren
                'image'         => false,
                'link'          => 'Kwc_Basic_LinkTag_Component',
                'download'      => 'Kwc_Basic_DownloadTag_Component'
            ),
            'model' => 'Kwc_Basic_Text_ChildComponentsModel'
        );

        $ret['assetsAdmin']['files'][] = 'kwf/Kwc/Basic/Text/StylesEditor.js';
        $ret['assetsAdmin']['files'][] = 'kwf/Kwc/Basic/Text/StylesEditorTab.js';
        $ret['assetsAdmin']['dep'][] = 'ExtHtmlEdit';
        $ret['assetsAdmin']['dep'][] = 'ExtWindow';
        $ret['assetsAdmin']['dep'][] = 'KwfAutoForm';
        $ret['assetsAdmin']['dep'][] = 'KwfAutoGrid';
        $ret['assetsAdmin']['dep'][] = 'ExtSimpleStore';
        $ret['assetsAdmin']['dep'][] = 'KwfColorField';

        $ret['assets']['dep'][] = 'KwfMailDecode';

        $ret['cssClass'] = 'webStandard kwcText';

        $ret['assets']['files']['styles'] = 'dynamic/Kwc_Basic_Text_StylesAsset:Kwc_Basic_Text_StylesModel';
        $ret['flags']['searchContent'] = true;
        $ret['flags']['hasFulltext'] = true;
        $ret['extConfig'] = 'Kwf_Component_Abstract_ExtConfig_Form';
        $ret['throwHasContentChangedOnRowColumnsUpdate'] = 'content';
        return $ret;
    }

    public static function validateSettings($settings, $component)
    {
        //nicht parent aufrufen weil da würde das model erstellt werden ohne componentClass zu übergeben
    }

    public function getOwnModel()
    {
        return self::getTextModel(get_class($this));
    }

    public static function getTextModel($componentClass)
    {
        static $models = array();
        if (!isset($models[$componentClass])) {
            $m = Kwc_Abstract::getSetting($componentClass, 'ownModel');
            $models[$componentClass] = new $m(array('componentClass' => $componentClass));
        }
        return $models[$componentClass];
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $ret['contentParts'] = array();
        $childs = $this->getData()->getChildComponents();
        foreach ($this->_getRow()->getContentParts() as $part) {
            if (is_array($part)) {
                if ($part['type'] == 'image') {
                    $part['nr'] = 'i'.$part['nr'];
                } else if ($part['type'] == 'link') {
                    $part['nr'] = 'l'.$part['nr'];
                } else if ($part['type'] == 'download') {
                    $part['nr'] = 'd'.$part['nr'];
                } else {
                    continue;
                }
                foreach ($childs as $row) {
                    if ($row->dbId == $this->getData()->dbId.'-'.$part['nr']) {
                        $ret['contentParts'][] = array(
                            'type' => $part['type'],
                            'component'=>$row
                        );
                        break;
                    }
                }
            } else {
                $ret['contentParts'][] = $part;
            }
        }
        $ret['styles'] = $this->_getSetting('emailStyles');
        return $ret;
    }

    public function hasContent()
    {
        $content = $this->_getRow()->content;
        $content = strip_tags($content);
        // replace every non-word-character, to see if there is real content,
        // or just a bunch of white-spaces
        $content = preg_replace('/\s*/u', '', $content);
        if (!empty($content)) return true;
        return false;
    }

    public function getSearchContent()
    {
        $ret = '';
        foreach ($this->_getRow()->getContentParts() as $part) {
            if (is_string($part)) {
                $part = strip_tags($part);
                $ret .= ' '.$part;
            }
        }
        return $ret;
    }

    public function getFulltextContent()
    {
        $html = '';
        foreach ($this->_getRow()->getContentParts() as $part) {
            if (is_string($part)) $html .= ' '.$part;
        }

        $ret = array();

        $ret['content'] = $this->_stripTags($html);

        $tags = array(
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'strong',
        );
        foreach ($tags as $tag) {
            if (preg_match_all("#<$tag.*?>(.*?)</$tag>#", $html, $m)) {
                $html = preg_replace("#<$tag.*?>.*?</$tag>#", '', $html);
                foreach ($m[1] as $text) {
                    $text = $this->_stripTags($text);
                    if ($text) {
                        if (!isset($ret['content'.$tag])) {
                            $ret['content'.$tag] =  $text;
                        } else {
                            $ret['content'.$tag] .=  ' '.$text;
                        }
                    }
                }
            }
        }

        $html = $this->_stripTags($html);
        if ($html) {
            $ret['normalContent'] = $html;
        }
        return $ret;
    }

    private static function _stripTags($html)
    {
        $html = preg_replace("#<[^>]*>#", ' ', $html);
        $html = str_replace("\r", ' ', $html);
        $html = str_replace("\n", ' ', $html);
        $html = preg_replace('#  +#', ' ', $html);
        $html = trim($html);
        return $html;
    }
}
