<?php
abstract class Vps_Component_Plugin_View_Abstract extends Vps_Component_Plugin_Abstract
    implements Vps_Component_Plugin_Interface_View
{
    protected $_componentId;

    public function __construct($componentId)
    {
        $this->_componentId = $componentId;
        parent::__construct($componentId);
    }

    public function getExecutionPoint()
    {
        return Vps_Component_Plugin_Interface_View::EXECUTE_BEFORE;
    }
}