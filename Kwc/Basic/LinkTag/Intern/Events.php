<?php
class Kwc_Basic_LinkTag_Intern_Events extends Kwc_Abstract_Events
{
    private $_pageIds;

    public function getListeners()
    {
        $ret = parent::getListeners();
        if (Kwc_Abstract::createOwnModel($this->_class) instanceof Kwc_Basic_LinkTag_Intern_Model) {
            $ret[] = array(
                'class' => null,
                'event' => 'Kwf_Component_Event_Page_RecursiveUrlChanged',
                'callback' => 'onRecursiveUrlChanged'
            );
            $ret[] = array(
                'class' => null,
                'event' => 'Kwf_Component_Event_Component_RecursiveRemoved',
                'callback' => 'onRecursiveRemovedAdded'
            );
            $ret[] = array(
                'class' => null,
                'event' => 'Kwf_Component_Event_Component_RecursiveAdded',
                'callback' => 'onRecursiveRemovedAdded'
            );
            $ret[] = array(
                'class' => null,
                'event' => 'Kwf_Component_Event_Page_Added',
                'callback' => 'onPageRemovedAdded'
            );
            $ret[] = array(
                'class' => null,
                'event' => 'Kwf_Component_Event_Page_Removed',
                'callback' => 'onPageRemovedAdded'
            );
        }
        return $ret;
    }

    //usually child componets can be deleted using %, but not those from pages table as the ids always start with numeric
    //this method returns all child ids needed for deleting recursively
    private function _getIdsFromRecursiveEvent(Kwf_Component_Event_Component_RecursiveAbstract $event)
    {
        $c = $event->component;
        $ids = array($c->dbId);
        $c = $c->getPageOrRoot();
        foreach (Kwf_Component_Data_Root::getInstance()->getPageGenerators() as $gen) {
            $ids = array_merge($ids, $gen->getVisiblePageChildIds($c->dbId));
        }
        return $ids;
    }

    public function onRecursiveUrlChanged(Kwf_Component_Event_Page_RecursiveUrlChanged $event)
    {
        foreach ($this->_getIdsFromRecursiveEvent($event) as $childPageId) {
            foreach ($this->_getDbIds($childPageId, true) as $dbId) {
                foreach (Kwf_Component_Data_Root::getInstance()->getComponentsByDbId($dbId) as $c) {
                    $this->fireEvent(new Kwf_Component_Event_Component_ContentChanged($this->_class, $c));
                    if ($c->isPage) {
                        $this->fireEvent(new Kwf_Component_Event_Page_UrlChanged($this->_class, $c));
                    }
                }
            }
        }
    }

    public function onRecursiveRemovedAdded(Kwf_Component_Event_Component_RecursiveAbstract $event)
    {
        foreach ($this->_getIdsFromRecursiveEvent($event) as $childPageId) {
            foreach ($this->_getDbIds($childPageId, true) as $dbId) {
                foreach (Kwf_Component_Data_Root::getInstance()->getComponentsByDbId($dbId) as $c) {
                    $this->fireEvent(new Kwf_Component_Event_Component_ContentChanged($this->_class, $c));
                    if ($c->isPage) {
                        $this->fireEvent(new Kwf_Component_Event_Page_UrlChanged($this->_class, $c));
                    }
                }
            }
        }
    }

    public function onPageRemovedAdded(Kwf_Component_Event_Component_AbstractFlag $event)
    {
        foreach ($this->_getDbIds($event->component->dbId, false) as $dbId) {
            foreach (Kwf_Component_Data_Root::getInstance()->getComponentsByDbId($dbId) as $c) {
                $this->fireEvent(new Kwf_Component_Event_Component_ContentChanged($this->_class, $c));
                if ($c->isPage) {
                    $this->fireEvent(new Kwf_Component_Event_Page_UrlChanged($this->_class, $c));
                }
            }
        }
    }

    protected function _onOwnRowUpdate(Kwf_Component_Data $c, Kwf_Component_Event_Row_Abstract $event)
    {
        parent::_onOwnRowUpdate($c, $event);
        $this->_pageIds = null;
    }

    private function _getDbIds($targetId, $includeSubpages = true)
    {
        if (!$this->_pageIds) {
            $ids = array();
            $model = Kwc_Abstract::createOwnModel($this->_class);
            foreach ($model->export(Kwf_Model_Abstract::FORMAT_ARRAY) as $row) {
                $target = $row['target'];
                if (!isset($ids[$target])) $ids[$target] = array();
                $ids[$target][] = $row['component_id'];
            }
            $this->_pageIds = $ids;
        }
        $ret = array();
        foreach ($this->_pageIds as $targetPageId => $dbIds) {
            if ($includeSubpages) {
                if ((string)$targetPageId == (string)$targetId
                    || substr($targetPageId, 0, strlen($targetId)+1) == $targetId.'-'
                    || substr($targetPageId, 0, strlen($targetId)+1) == $targetId.'_'
                ) {
                    $ret = array_merge($ret, $dbIds);
                }
            } else {
                if ((string)$targetPageId === (string)$targetId) {
                    $ret = array_merge($ret, $dbIds);
                }
            }
        }
        return $ret;
    }

}
