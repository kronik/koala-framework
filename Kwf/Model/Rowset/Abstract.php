<?php
/**
 * @package Model
 * @internal
 */
class Kwf_Model_Rowset_Abstract implements Kwf_Model_Rowset_Interface, Serializable
{
    protected $_pointer = 0;
    protected $_dataKeys; //wenn objekt normal erstellt wurde
    protected $_rows; //wenn objekt deseralisiert wurde
    protected $_model;

    public function __construct($config)
    {
        $this->_init();
        $this->_dataKeys = $config['dataKeys'];
        $this->_model = $config['model'];
    }

    public function serialize()
    {
        $rows = array();
        foreach ($this as $row) {
            $rows[] = $row;
        }
        return serialize($rows);
    }

    public function unserialize($str)
    {
        $this->_rows = unserialize($str);
    }

    public function toArray()
    {
        $ret = array();
        foreach ($this as $row) {
            $ret[] = $row->toArray();
        }
        return $ret;
    }

    protected function _init()
    {
    }

    public function rewind()
    {
        $this->_pointer = 0;
        return $this;
    }

    public function current()
    {
        if ($this->valid() === false) {
            return null;
        }
        if (isset($this->_rows)) {
            return $this->_rows[$this->_pointer];
        }
        $key = $this->_dataKeys[$this->_pointer];
        return $this->_getRowByDataKey($key);
    }

    protected function _getRowByDataKey($key)
    {
        return $this->getModel()->getRowByDataKey($key);
    }

    public function key()
    {
        return $this->_pointer;
    }

    public function next()
    {
        ++$this->_pointer;
    }

    public function valid()
    {
        return $this->_pointer < $this->count();
    }

    public function count()
    {
        if (isset($this->_rows)) {
            return count($this->_rows);
        } else {
            return count($this->_dataKeys);
        }
    }

    public function seek($position)
    {
        $position = (int) $position;
        if ($position < 0 || $position > $this->count()) {
            throw new Kwf_Exception("Illegal index $position");
        }
        $this->_pointer = $position;
        return $this;
    }

    public function offsetExists($offset)
    {
        if ($offset < 0 || $offset >= $this->count()) {
            return false;
        }
        return true;
    }

    public function offsetGet($offset)
    {
        $offset = (int) $offset;
        if ($offset < 0 || $offset >= $this->count()) {
            throw new Kwf_Exception("Illegal index $offset");
        }
        $this->_pointer = $offset;

        return $this->current();
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }

    public function getModel()
    {
        return $this->_model;
    }

    public function getTable()
    {
        return $this->getModel()->getTable();
    }

    public function toDebug()
    {
        $i = get_class($this).': '.count($this).' rows';
        $ret = print_r($this->toArray(), true);
        $ret = preg_replace('#^Array#', $i, $ret);
        $ret .= "Model: ".get_class($this->getModel());
        $ret = "<pre>$ret</pre>";
        return $ret;
    }
}
