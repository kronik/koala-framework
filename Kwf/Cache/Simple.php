<?php
/**
 * A simple and fast cache. Doesn't have all the Zend_Cache bloat.
 *
 * If available it uses apc user cache direclty (highly recommended!!), else it falls
 * back to Zend_Cache using a memcache backend.
 *
 * If aws.simpleCacheCluster is set Aws ElastiCache will be used.
 */
class Kwf_Cache_Simple
{
    public static function getZendCache()
    {
        static $cache;
        if (!isset($cache)) {
            if (Kwf_Config::getValue('aws.simpleCacheCluster')) {
                $cache = new Zend_Cache_Core(array(
                    'lifetime' => null,
                    'write_control' => false,
                    'automatic_cleaning_factor' => 0,
                    'automatic_serialization' => true
                ));
                $cache->setBackend(new Kwf_Util_Aws_ElastiCache_CacheBackend(array(
                    'cacheClusterId' => Kwf_Config::getValue('aws.simpleCacheCluster'),
                )));

                //namespace is incremented in Kwf_Util_ClearCache
                //use memcache directly as Zend would not save the integer directly and we can't increment it then
                $v = $cache->getBackend()->getMemcache()->get('cache_namespace');
                if (!$v) {
                    $v = time();
                    $cache->getBackend()->getMemcache()->set('cache_namespace', $v);
                }
                $cache->setOption('cache_id_prefix', $v);
            } else {
                if (!Kwf_Config::getValue('server.memcache.host') && extension_loaded('apc')) {
                    $cache = false;
                } else {
                    $cache = new Zend_Cache_Core(array(
                        'lifetime' => null,
                        'write_control' => false,
                        'automatic_cleaning_factor' => 0,
                        'automatic_serialization' => true
                    ));
                    if (Kwf_Config::getValue('server.memcache.host')) {
                        $cache->setBackend(new Kwf_Cache_Backend_Memcached());
                    } else {
                        //fallback to file backend (NOT recommended!)
                        $cache->setBackend(new Kwf_Cache_Backend_File(array(
                            'cache_dir' => 'cache/simple'
                        )));
                    }
                }
            }
        }
        return $cache;
    }

    private static function _processId($cacheId)
    {
        static $prefix;
        if (!isset($prefix)) $prefix = self::getUniquePrefix().'-';
        $cacheId = str_replace('-', '__', $prefix.$cacheId);
        $cacheId = preg_replace('#[^a-zA-Z0-9_]#', '_', $cacheId);
        return $cacheId;
    }

    public static function fetch($cacheId, &$success = true)
    {
        static $cache;
        if (!isset($cache)) $cache = self::getZendCache();
        if (!$cache) {
            static $prefix;
            if (!isset($prefix)) $prefix = self::getUniquePrefix().'-';
            return apc_fetch($prefix.$cacheId, $success);
        } else {
            $ret = $cache->load(self::_processId($cacheId));
            $success = $ret !== false;
            return $ret;
        }
    }

    public static function add($cacheId, $data, $ttl = null)
    {
        static $cache;
        if (!isset($cache)) $cache = self::getZendCache();
        if (!$cache) {
            static $prefix;
            if (!isset($prefix)) $prefix = self::getUniquePrefix().'-';
            return apc_add($prefix.$cacheId, $data, $ttl);
        } else {
            return self::getZendCache()->save($data, self::_processId($cacheId), array(), $ttl);
        }
    }

    public static function delete($cacheIds)
    {
        static $cache;
        if (!isset($cache)) $cache = self::getZendCache();

        if (!is_array($cacheIds)) $cacheIds = array($cacheIds);
        static $prefix;
        if (!isset($prefix)) $prefix = self::getUniquePrefix().'-';
        $ret = true;
        $ids = array();
        foreach ($cacheIds as $cacheId) {
            if (!$cache) {
                $r = apc_delete($prefix.$cacheId);
                $ids[] = $prefix.$cacheId;
            } else {
                $r = $cache->remove(self::_processId($cacheId));
            }
            if (!$r) $ret = false;
        }
        if (!$cache && php_sapi_name() == 'cli' && $ids) {
            $result = Kwf_Util_Apc::callClearCacheByCli(array('cacheIds' => implode(',', $ids)), Kwf_Util_Apc::SILENT);
            if (!$result['result']) $ret = false;
        }
        return $ret;
    }

    public static function clear($cacheIdPrefix)
    {
        static $cache;
        if (!isset($cache)) $cache = self::getZendCache();

        if (!$cache) {
            if (!class_exists('APCIterator')) {
                apc_clear_cache('user');
            } else {
                static $prefix;
                if (!isset($prefix)) $prefix = self::getUniquePrefix().'-';
                $it = new APCIterator('user', '#^'.preg_quote($prefix.$cacheIdPrefix).'#', APC_ITER_NONE);
                if ($it->getTotalCount() && !$it->current()) {
                    //APCIterator is borked, delete everything
                    //see https://bugs.php.net/bug.php?id=59938
                    apc_clear_cache('user');
                } else {
                    //APCIterator seems to work, use it for deletion
                    apc_delete($it);
                }
            }
        } else {
            //we can't do any better here :/
            if (Kwf_Config::getValue('aws.simpleCacheCluster')) {
                throw new Kwf_Exception_NotYetImplemented("We don't want to clear the whole");
            }
            $cache->clean();
        }
    }

    public static function getUniquePrefix()
    {
        static $ret;
        if (!isset($ret)) {
            $ret = getcwd().'-'.Kwf_Setup::getConfigSection().'-';
        }
        return $ret;
    }
}
