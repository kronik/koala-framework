<?php
class Kwc_Paging_Abstract_Component extends Kwc_Abstract
    implements Kwf_Component_Partial_Interface
{
    private $_entries;
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['pagesize'] = 10;
        $ret['maxPagingLinks'] = 13;
        $ret['bigPagingSteps'] = array(10, 50);
        $ret['nextPrevOnly'] = false;

        // if one of the following is set to false, the link won't be output:
        // first, previous, next, last
        $ret['placeholder'] = array(
            'first'    => '&laquo;',
            'previous' => '&#x8B;',
            'next'     => '&#x9B;',
            'last'     => '&raquo;',
            'prefix'   => trlKwfStatic('Page').':'
        );
        $ret['cssClass'] = 'webPaging webStandard';
        return $ret;
    }

    public static function getPartialClass($componentClass)
    {
        return 'Kwf_Component_Partial_Pager';
    }

    public function getViewCacheSettings()
    {
        $ret = parent::getViewCacheSettings();
        $c = $this->getData()->parent;
        if ($c->getComponent() instanceof Kwc_Directories_List_View_Component &&
            $c->getComponent()->getPartialClass($c->componentClass) == 'Kwf_Component_Partial_Id')
        {
            $ret['enabled'] = false;
        }
        return $ret;
    }

    public function getCount()
    {
        if (!isset($this->_entries)) {
            $this->_entries = $this->getData()->parent->getComponent()->getPagingCount();
            if (!$this->_entries) {
                $this->_entries = 0;
            } else if ($this->_entries instanceof Kwf_Component_Select) {
                $this->_entries = $this->getData()->parent->countChildComponents($this->_entries);
            } else if ($this->_entries instanceof Kwf_Model_Select) {
                throw new Kwf_Exception("Not yet implemented, probably not really possible");
            } else if ($this->_entries instanceof Zend_Db_Select) {
                $select = $this->_entries;
                $select->setIntegrityCheck(false);
                $select->reset(Zend_Db_Select::COLUMNS);
                if ($select instanceof Kwf_Db_Table_Select) {
                    $table = $select->getTableName().'.';
                } else {
                    $table = '';
                }
                $select->from(null, array('count' => "COUNT(DISTINCT {$table}id)"));
                $r = $select->query()->fetchAll();
                if (!isset($r[0])) {
                    $this->_entries  = 0;
                } else {
                    $this->_entries = $r[0]['count'];
                }
                if ($select->getPart(Zend_Db_Select::LIMIT_COUNT)) {
                    //falls select ein limit hat dieses verwenden
                    $this->_entries  = min($this->_entries, $select->getPart(Zend_Db_Select::LIMIT_COUNT));
                }
            }
        }
        return $this->_entries;
    }

    private function _getLinkData($pageNumber, $text = null)
    {
        if (is_null($text)) $text = $pageNumber;

        $params = array();
        $get = array();
        foreach ($_GET as $p=>$v) {
            if ($p != $this->_getParamName() && !is_array($v)) {
                $params[] = "$p=".urlencode($v);
                $get[$p] = $v;
            }
        }
        $params = implode('&', $params);

        $currentPage = $this->_getCurrentpage();
        $p = '';
        if ($pageNumber == 1) {
            if ($params) $p = '?'.$params;
        } else {
            $p = '?'.$this->_getParamName().'='.$pageNumber;
            if ($params) $p .= '&'.$params;
            $get[$this->_getParamName()] = $pageNumber;
        }

        $classes = array();
        if ($currentPage == $pageNumber) $classes[] = 'active';

        $buttonTexts = $this->_getPlaceholder();
        if ($text==$buttonTexts['next']) {
            $classes[] = 'jumpNext';
        } else if ($text==$buttonTexts['previous']) {
            $classes[] = 'jumpPrevious';
        } else if ($text==$buttonTexts['first']) {
            $classes[] = 'jumpFirst';
        } else if ($text==$buttonTexts['last']) {
            $classes[] = 'jumpLast';
        }

        $linktext = '<span';
        if (!is_numeric($text)) $linktext .= ' class="navigation"';
        $linktext .= '>';
        $linktext .= $text;
        $linktext .= '</span>';

        return array(
            // Für alte Version
            'text' => $text,
            'href' => $this->getUrl().$p,
            'rel'  => '',
            'active' => $currentPage == $pageNumber,
            // ab hier für componentLinkHelper
            'get' => $get,
            'class' => $classes,
            'linktext' => $linktext,
            'currentPageNumber' => $currentPage,
            'pageNumber' => $pageNumber
        );
    }

    protected function _getParamName()
    {
        return $this->getDbId();
    }

    protected function _getPages()
    {
        return ceil($this->getCount() / $this->_getPageSize());
    }

    protected function _getCurrentPage()
    {
        return self::getCurrentPageByParam($this->_getParamName());
    }

    public function getCurrentPage()
    {
        return $this->_getCurrentPage();
    }

    public static function getCurrentPageByParam($paramName)
    {
        if (!isset($_GET[$paramName])) {
            $page = 1;
        } else {
            $page = (int)$_GET[$paramName];
        }
        if ($page < 1) $page = 1;
        return $page;
    }

    public function hasContent()
    {
        return ($this->_getPages() > 1 ? true : false);
    }

    public function getLimit()
    {
        $ret = array();
        $ret['limit'] = $this->_getPageSize();
        $ret['start'] = ($this->_getCurrentPage()-1)*$this->_getPageSize();
        return $ret;
    }

    public function limitSelect(Kwf_Model_Select $select)
    {
        $limit = $this->getLimit();
        if ($select->hasPart(Kwf_Model_Select::LIMIT_COUNT)) {
            //wenn schon ein limit gesetzt
            $existingLimitCount = $select->getPart(Kwf_Model_Select::LIMIT_COUNT);
            if ($existingLimitCount < $limit['limit']) {
                return;
            }
        }
        $select->limit($limit);
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $ret['pages'] = $this->_getPages();
        $ret['currentPage'] = $this->_getCurrentpage();
        $ret['show'] = $this->getCount() > $this->_getPageSize();
        $ret['partialParams'] = $this->getPartialParams();
        return $ret;
    }

    protected function _getPageSize()
    {
        return $this->_getSetting('pagesize');
    }

    public function getPartialParams()
    {
        return array(
            'class' => get_class($this),
            'paramName' => $this->_getParamName(),
            'pages' => $this->_getPages(),
            'pagesize' => $this->_getPageSize()
        );
    }

    public function getPartialVars($partial, $nr, $info)
    {
        $pages = $partial->getParam('pages');
        return array(
            'pageLinks' => $this->_getPageLinks($pages, $nr)
        );
    }

    protected function _getPageLinks($pages, $currentPage)
    {
        $buttonTexts = $this->_getPlaceholder();

        $pageLinks = array();
        if ($currentPage >= 3 && !$this->_getSetting('nextPrevOnly') && $buttonTexts['first'] !== false) {
            $pageLinks[] = $this->_getLinkData(1, $buttonTexts['first']);
        }
        if ($currentPage >= 2 && $buttonTexts['previous'] !== false) {
            $pageLinks[] = $this->_getLinkData($currentPage-1, $buttonTexts['previous']);
        }

        $appendPagelinks = array();
        if (!$this->_getSetting('nextPrevOnly')) {
            $bigSteps = $this->_getSetting('bigPagingSteps');
            rsort($bigSteps);
            foreach ($bigSteps as $stepOffset) {
                if ($this->_getSetting('maxPagingLinks') < $stepOffset * 2) {
                    if ($currentPage >= $stepOffset + 1) {
                        $pageLinks[] = $this->_getLinkData($currentPage - $stepOffset);
                    }

                    if ($currentPage <= $pages - $stepOffset) {
                        array_unshift($appendPagelinks, $this->_getLinkData($currentPage + $stepOffset));
                    }
                }
            }
        }

        if ($currentPage < $pages && $buttonTexts['next'] !== false) {
            $appendPagelinks[] = $this->_getLinkData($currentPage+1, $buttonTexts['next']);
        }
        if ($currentPage < $pages - 1 && !$this->_getSetting('nextPrevOnly') && $buttonTexts['last'] !== false) {
            $appendPagelinks[] = $this->_getLinkData($pages, $buttonTexts['last']);
        }

        if (!$this->_getSetting('nextPrevOnly')) {
            $linksPerDirection = floor(
                ($this->_getSetting('maxPagingLinks') - (count($pageLinks) + count($appendPagelinks) + 1)) / 2
            );
            if ($linksPerDirection < 0) $linksPerDirection = 0;

            $fromPage = $currentPage - $linksPerDirection;
            $toPage = $currentPage + $linksPerDirection;
            if ($fromPage < 1) {
                $toPage += abs($fromPage - 1);
            }
            if ($toPage > $pages) {
                $fromPage -= ($toPage - $pages);
            }
            if ($fromPage < 1) $fromPage = 1;
            if ($toPage > $pages) $toPage = $pages;

            for ($i = $fromPage; $i <= $toPage; $i++) {
                $pageLinks[] = $this->_getLinkData($i);
            }
        }

        foreach ($appendPagelinks as $linkData) {
            $pageLinks[] = $linkData;
        }
        return $pageLinks;
    }
}
