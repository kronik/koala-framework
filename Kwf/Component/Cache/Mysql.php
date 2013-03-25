<?php
class Kwf_Component_Cache_Mysql extends Kwf_Component_Cache
{
    protected $_models;

    public function __construct()
    {
        $this->_models = array (
            'cache' => 'Kwf_Component_Cache_Mysql_Model',
            'url' => 'Kwf_Component_Cache_Mysql_UrlModel',
        );
    }

    /**
     * @return Kwf_Model_Abstract
     */
    public function getModel($type = 'cache')
    {
        if (!isset($this->_models[$type])) return null;
        if (is_string($this->_models[$type])) {
            $this->_models[$type] = Kwf_Model_Abstract::getInstance($this->_models[$type]);
        }
        return $this->_models[$type];
    }

    public function save(Kwf_Component_Data $component, $content, $renderer='component', $type = 'component', $value = '')
    {
        $settings = $component->getComponent()->getViewCacheSettings();
        if ($type != 'componentLink' && $type != 'master' && $type != 'page' && !$settings['enabled']) {
            $content = self::NO_CACHE;
        }

        // MySQL
        $data = array(
            'component_id' => (string)$component->componentId,
            'db_id' => (string)$component->dbId,
            'page_db_id' => (string)$component->getPageOrRoot()->dbId,
            'expanded_component_id' => (string)$component->getExpandedComponentId(),
            'component_class' => $component->componentClass,
            'renderer' => $renderer,
            'type' => $type,
            'value' => (string)$value,
            'expire' => is_null($settings['lifetime']) ? null : time() + $settings['lifetime'],
            'deleted' => false,
            'content' => $content
        );
        $options = array(
            'buffer' => true,
            'replace' => true,
            'skipModelObserver' => true
        );
        $this->getModel('cache')->import(Kwf_Model_Abstract::FORMAT_ARRAY, array($data), $options);

        // APC
        $cacheId = $this->_getCacheId($component->componentId, $renderer, $type, $value);
        $ttl = 0;
        if ($settings['lifetime']) $ttl = $settings['lifetime'];
        Kwf_Component_Cache_Memory::getInstance()->save($content, $cacheId, array(), $ttl);

        return true;
    }

    public function load($componentId, $renderer='component', $type = 'component', $value = '')
    {
        if ($componentId instanceof Kwf_Component_Data) {
            $componentId = $componentId->componentId;
        }
        $cacheId = $this->_getCacheId($componentId, $renderer, $type, $value);
        $content = Kwf_Component_Cache_Memory::getInstance()->load($cacheId);
        if ($content === false) {
            Kwf_Benchmark::count('comp cache mysql');
            $select = $this->getModel('cache')->select()
                ->whereEquals('component_id', $componentId)
                ->whereEquals('renderer', $renderer)
                ->whereEquals('type', $type)
                ->whereEquals('deleted', false)
                ->whereEquals('value', $value)
                ->where(new Kwf_Model_Select_Expr_Or(array(
                    new Kwf_Model_Select_Expr_Higher('expire', time()),
                    new Kwf_Model_Select_Expr_IsNull('expire'),
                )));
            $options = array(
                'columns' => array('content', 'expire'),
            );
            $row = $this->getModel('cache')->export(Kwf_Model_Db::FORMAT_ARRAY, $select, $options);
            $content = isset($row[0]) ? $row[0]['content'] : null;
            if (isset($row[0])) {
                $ttl = 0;
                if ($row[0]['expire']) $ttl = $row[0]['expire']-time();
                Kwf_Component_Cache_Memory::getInstance()->save($content, $cacheId, array(), $ttl);
            }
        }
        return $content;
    }

    public function deleteViewCache($select)
    {
        $select->whereEquals('deleted', false);
        $model = $this->getModel();
        $log = Kwf_Component_Events_Log::getInstance();
        $cacheIds = array();
        $options = array(
            'columns' => array('component_id', 'renderer', 'type', 'value'),
        );
        $partialIds = array();
        $deleteIds = array();
        foreach ($model->export(Kwf_Model_Abstract::FORMAT_ARRAY, $select, $options) as $row) {
            $cacheIds[] = $this->_getCacheId($row['component_id'], $row['renderer'], $row['type'], $row['value']);
            if ($log) {
                $log->log("delete view cache $row[component_id] $row[renderer] $row[type] $row[value]", Zend_Log::INFO);
            }
            $type = $row['type'];
            $value = $row['value'];
            $cId = $row['component_id'];
            if ($type == 'partial' && $value != '') {
                if (!isset($partialIds[$cId])) $partialIds[$cId] = array();
                $partialIds[$cId][] = $value;
            } else if ($value == '') {
                if (!isset($deleteIds[$type])) $deleteIds[$type] = array();
                $deleteIds[$type][] = $cId;
            } else {
                throw new Kwf_Exception('Should not happen.');
            }
        }
        foreach ($partialIds as $componentId => $values) {
            $select = $model->select();
            $select->where(new Kwf_Model_Select_Expr_And(array(
                new Kwf_Model_Select_Expr_Equals('component_id', $componentId),
                new Kwf_Model_Select_Expr_Equals('type', 'partial'),
                new Kwf_Model_Select_Expr_Equals('value', $values)
            )));
            $model->updateRows(array('deleted' => true), $select);
        }
        foreach ($deleteIds as $type => $componentIds) {
            $select = $model->select();
            $select->where(new Kwf_Model_Select_Expr_And(array(
                new Kwf_Model_Select_Expr_Equals('component_id', $componentIds),
                new Kwf_Model_Select_Expr_Equals('type', $type)
            )));
            $model->updateRows(array('deleted' => true), $select);
        }

        foreach ($cacheIds as $cacheId) {
            Kwf_Component_Cache_Memory::getInstance()->remove($cacheId);
        }

        file_put_contents('log/clear-view-cache', date('Y-m-d H:i:s').' '.round(microtime(true)-Kwf_Benchmark::$startTime, 2).'s; '.Kwf_Component_Events::$eventsCount.' events; '.count($deleteIds).' view cache entries deleted; '.(isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'')."\n", FILE_APPEND);
    }

    protected static function _getCacheId($componentId, $renderer, $type, $value)
    {
        return "cc_".str_replace('-', '__', $componentId)."_{$renderer}_{$type}_{$value}";
    }

    public static function getCacheId($componentId, $renderer, $type, $value)
    {
        return self::_getCacheId($componentId, $renderer, $type, $value);
    }

    // wird nur von Kwf_Component_View_Renderer->saveCache() verwendet
    public function test($componentId, $renderer, $type = 'component', $value = '')
    {
        return !is_null($this->load($componentId, $renderer, $type, $value));
    }
}
