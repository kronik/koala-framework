<?php
class Kwf_Benchmark
{
    private static $_enabled = false;
    private static $_logEnabled = false;
    protected static $_counter = array();
    public static $benchmarks = array();
    private static $_checkpoints = array();
    private static $_subCheckpoints = array();
    public static $startTime; //wird von Kwf_Setup::setUp gesetzt

    private static function _getInstance()
    {
        static $i;
        if (!isset($i)) {
            $c = Kwf_Config::getValue('benchmarkClass');
            if (!class_exists($c)) {
                $c = 'Kwf_Benchmark';
            }
            $i = new $c();
        }
        return $i;
    }

    /**
     * Startet eine Sequenz
     *
     * @param string $identifier
     */
    public static function start($identifier = null, $addInfo = null)
    {
        if (!self::$_enabled) return null;
        return new Kwf_Benchmark_Profile($identifier, $addInfo);
    }

    public static function enable()
    {
        self::$_enabled = true;
    }

    public static function disable()
    {
        self::$_enabled = false;
    }

    public static function enableLog()
    {
        self::$_logEnabled = true;
    }

    public static function isEnabled()
    {
        return self::$_enabled;
    }

    public static function reset()
    {
        self::$_counter = array();
    }
    public static function getCounterValue($name)
    {
        if (!isset(self::$_counter[$name])) return null;
        $ret = self::$_counter[$name];
        if (is_array($ret)) $ret = count($ret);
        return $ret;
    }
    public static function count($name, $value = null)
    {
        if (!self::$_enabled && !self::$_logEnabled) return false;

        self::_countArray(self::$_counter, $name, $value);

        foreach (self::$benchmarks as $b) {
            if (!$b->stopped) {
                self::_countArray($b->counter, $name, $value);
            }
        }
    }

    public static function countBt($name, $value = null)
    {
        if (!self::$_enabled && !self::$_logEnabled) return false;

        self::_countArray(self::$_counter, $name, $value, true);

        foreach (self::$benchmarks as $b) {
            if (!$b->stopped) {
                self::_countArray($b->counter, $name, $value, true);
            }
        }
    }

    private static function _countArray(&$counter, $name, $value, $backtrace = false)
    {
        if (!isset($counter[$name])) {
            if (!is_null($value)) {
                $counter[$name] = array();
            } else {
                $counter[$name] = 0;
            }
        }
        if (!is_null($value)) {
            if (!is_array($counter[$name])) {
                throw new Kwf_Exception("Missing value for counter '$name'");
            }
            $bt = false;
            if ($backtrace) {
                $b = debug_backtrace();
                unset($b[0]);
                unset($b[1]);
                $bt = '';
                foreach ($b as $i) {
                    $bt .=
                        (isset($i['file']) ? $i['file'] : 'Unknown file') . ':' .
                        (isset($i['line']) ? $i['line'] : '?') . ' - ' .
                        ((isset($i['object']) && $i['object'] instanceof Kwf_Component_Data) ? $i['object']->componentId . '->' : '') .
                        (isset($i['function']) ? $i['function'] : '') . '(' .
                        _btArgsString($i['args']) . ')' . "<br />";
                }
            }
            $counter[$name][] = array(
                'value' => $value,
                'bt' => $bt
            );
        } else {
            if (is_array($counter[$name])) {
                throw new Kwf_Exception("no value possible for counter '$name'");
            }
            $counter[$name]++;
        }
    }

    public static function output()
    {
        Kwf_Benchmark::checkpoint('shutDown');

        if (isset($_COOKIE['unitTest'])) return;
        if (!self::$_enabled) return;
        self::disable();
        self::$_logEnabled = false;
        if (PHP_SAPI != 'cli') {
            echo '<div class="outerBenchmarkBox">';
            echo '<div class="innerBenchmarkBox">';
            $t = microtime(true) - self::$startTime;
            if ($t < 1) {
                echo round($t*1000)." msec<br />\n";
            } else {
                echo round($t, 3)." sec<br />\n";
            }
            $load = @file_get_contents('/proc/loadavg');
            $load = explode(' ', $load);
            echo "Load: ". $load[0]."<br />\n";
            if (function_exists('memory_get_peak_usage')) {
                echo "Memory: ".round(memory_get_peak_usage()/1024)." kb<br />\n";
            } else {
                echo "Memory: ".round(memory_get_usage()/1024)." kb<br />\n";
            }
            if (Zend_Registry::get('dao') && Zend_Registry::get('dao')->hasDb() && Zend_Registry::get('db')) {
                if (Zend_Registry::get('db')->getProfiler() && method_exists(Zend_Registry::get('db')->getProfiler(), 'getQueryCount')) {
                    echo "DB-Queries: ".Zend_Registry::get('db')->getProfiler()->getQueryCount()."<br />\n";
                } else {
                    echo "DB-Queries: (no profiler used)<br />\n";
                }
            } else {
                echo "DB-Queries: (none)<br />\n";
            }
        }
        self::_outputCounter(self::$_counter);
        if (self::$benchmarks && PHP_SAPI != 'cli') {
            echo "<br /><b>Benchmarks:</b><br/>";
            foreach (self::$benchmarks as $i) {
                echo "<a style=\"display:block;\" href=\"#\" onclick=\"if(this.nextSibling.nextSibling.style.display=='none') { this.open=true; this.nextSibling.nextSibling.style.display='block'; this.nextSibling.style.display=''; } else { this.open=false; this.nextSibling.nextSibling.style.display='none';this.nextSibling.style.display='none'; } return(false); }\"
                                                            onmouseover=\"if(!this.open) this.nextSibling.style.display=''\"
                                                            onmouseout=\"if(!this.open) this.nextSibling.style.display='none'\">";
                echo "{$i->identifier} (".round($i->duration, 3)." sec)</a>";
                echo "<div style=\"display:none;margin-left:10px\">";
                echo $i->addInfo."<br/>";
                echo implode('<br />', $i->getOutput());
                echo "</div>";
                echo "<div style=\"display:none;margin-left:10px\">";
                self::_outputCounter($i->counter);
                echo "</div>";
            }
        }
        echo "<table style=\"font-size: 10px\">";
        echo "<tr><th>ms</th><th>%</th><th>Checkpoint</th></tr>";
        $sum = 0;
        foreach (self::$_checkpoints as $checkpoint) {
            $sum += $checkpoint[0];
        }
        foreach (self::$_checkpoints as $i=>$checkpoint) {
            echo "<tr>";
            echo "<td>".round($checkpoint[0]*1000)."</td>";
            echo "<td>".round(($checkpoint[0]/$sum)*100)."</td>";
            echo "<td>".$checkpoint[1]."</td>";
            echo "</tr>";
            if (isset(self::$_subCheckpoints[$i])) {
                $subCheckpoints = array();
                foreach (self::$_subCheckpoints[$i] as $cp) {
                    $subCheckpoints[0][] = $cp[0];
                    $subCheckpoints[1][] = $cp[1];
                }
                array_multisort($subCheckpoints[0], SORT_DESC, SORT_NUMERIC, $subCheckpoints[1]);
                foreach (array_keys($subCheckpoints[0]) as $k) {
                    $percent = ($subCheckpoints[0][$k]/$sum)*100;
                    if ($percent > 1) {
                        echo "<tr>";
                        echo "<td>".round($subCheckpoints[0][$k]*1000)."</td>";
                        echo "<td>".round($percent)."</td>";
                        echo "<td>&nbsp;&nbsp;".$subCheckpoints[1][$k]."</td>";
                        echo "</tr>";
                    }
                }
            }
        }
        echo "</table>";
        if (PHP_SAPI != 'cli') {
            echo "</div>";
            echo "</div>";
        }
    }
    private static function _outputCounter($counter)
    {
        echo "\n";
        if (!is_array($counter)) return;
        foreach ($counter as $k=>$i) {
            if (is_array($i)) {
                if (PHP_SAPI != 'cli') {
                    echo "<a style=\"display:block;\" href=\"#\" onclick=\"if(this.nextSibling.style.display=='none') this.nextSibling.style.display='block'; else this.nextSibling.style.display='none';return(false);\">";
                    echo "$k: ".count($i)."</a>";
                    echo "<ul style=\"display:none\">";
                    foreach ($i as $j) {
                        echo "<li>";
                        if ($j['bt']) {
                            echo "<strong style=\"font-weight:bold\">{$j['value']}</strong><br />{$j['bt']}";
                        } else {
                            echo $j['value'];
                        }
                        echo "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "$k: (".count($i).') ';
                    foreach ($i as $j) {
                        echo $j['value'].' ';
                    }
                    echo "\n";
                }
            } else {
                if (PHP_SAPI != 'cli') {
                    echo "$k: $i<br />\n";
                } else {
                    echo "$k: $i\n";
                }
            }
        }
    }

    public static function info($msg)
    {
        if (!self::$_enabled) return;
        if (Kwf_Config::getValue('debug.firephp') && class_exists('FirePHP') && FirePHP::getInstance() && FirePHP::getInstance()->detectClientExtension()) {
            p($msg, 'INFO');
        }
    }

    public static function cacheInfo($msg)
    {
        if (!Kwf_Config::getValue('debug.componentCache.info')) return;
        if (Kwf_Config::getValue('debug.firephp') && class_exists('FirePHP') && FirePHP::getInstance() && FirePHP::getInstance()->detectClientExtension()) {
            p($msg, 'INFO');
        }
    }

    final public static function shutDown()
    {
        Kwf_Benchmark::checkpoint('shutDown');

        if (function_exists('xhprof_disable') && file_exists('/www/public/niko/xhprof')) {
            //TODO irgendwie intelligenter aktivieren/deaktivieren
            $xhprof_data = xhprof_disable();
            if ($xhprof_data) {
                $XHPROF_ROOT = '/www/public/niko/xhprof';
                include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
                include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";
                $xhprof_runs = new XHProfRuns_Default();
                $run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_benchmark");
                echo "http://xhprof.niko.vivid/xhprof_html/index.php?run=$run_id&source=xhprof_benchmark";
            }
        }

        if (!self::$_logEnabled) return;

        static $wasCalled = false;
        if ($wasCalled) return;
        $wasCalled = true;

        self::_getInstance()->_shutDown();
    }

    final public static function getUrlType()
    {
        return self::_getInstance()->_getUrlType();
    }

    protected function _getUrlType()
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            if (php_sapi_name() == 'cli') $urlType = 'cli';
            else $urlType = 'unknown';
        } else if (substr($_SERVER['REQUEST_URI'], 0, 8) == '/assets/') {
            $urlType = 'asset';
        } else if (substr($_SERVER['REQUEST_URI'], 0, 7) == '/media/') {
            $urlType = 'media';
        } else if (substr($_SERVER['REQUEST_URI'], 0, 7) == '/admin/') {
            $urlType = 'admin';
        } else if (substr($_SERVER['REQUEST_URI'], 0, 5) == '/kwf/') {
            $urlType = 'admin';
        } else {
            $urlType = 'content';
        }
        return $urlType;
    }

    protected function _shutDown()
    {
        if ($this->_getUrlType() == 'asset' && !self::$_counter) return;
        $prefix = $this->_getUrlType().'-';
        $this->_memcacheCount($prefix.'requests', 1);
        foreach (self::$_counter as $name=>$value) {
            if (is_array($value)) $value = count($value);
            $this->_memcacheCount($prefix.$name, $value);
        }
        $value = (int)((microtime(true) - self::$startTime)*1000);
        $this->_memcacheCount($prefix.'duration', $value);
    }

    private function _memcacheCount($name, $value)
    {
        Kwf_Benchmark_Counter::getInstance()->increment($name, $value);
    }

    public static function memcacheCount($name, $value = 1)
    {
        self::_getInstance()->_memcacheCount($name, $value);
    }

    public static function subCheckpoint($description, $time)
    {
        if (!self::$_enabled) return;
        if (!isset(self::$_subCheckpoints[count(self::$_checkpoints)])) {
            self::$_subCheckpoints[count(self::$_checkpoints)] = array();
        }
        self::$_subCheckpoints[count(self::$_checkpoints)][] = array(
            $time,
            $description,
        );
    }

    public static function checkpoint($description)
    {
        if (!self::$_enabled) return;
        static $previousTime;
        if (!$previousTime) $previousTime = self::$startTime;
        $time = microtime(true) - $previousTime;
        $previousTime = microtime(true);
        self::$_checkpoints[] = array(
            $time,
            $description,
            array() //sub
        );
    }
}
