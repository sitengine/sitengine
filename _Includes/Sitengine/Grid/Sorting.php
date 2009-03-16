<?php

/**
 * Sitengine - An example serving as a pattern
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Sitengine
 * @package    Sitengine
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */




class Sitengine_Grid_Sorting
{

    
    protected $_rules = array();
    protected $_defaultRule = '';
    protected $_currentRule = '';
    protected $_currentOrder = '';
    
    
    public function __construct($currentRule, $currentOrder)
    {
        $this->_currentRule = $currentRule;
        $this->_currentOrder = $currentOrder;
    }
    
    
    
    public function setDefaultRule($name)
    {
        $this->_defaultRule = $name;
        return $this;
    }
    
    
    
    public function addRule($name, $defaultOrder, $ascClause, $descClause)
    {
        $this->_rules[$name] = array(
            'defaultOrder' => $defaultOrder,
            'asc' => $ascClause,
            'desc' => $descClause
        );
        return $this;
    }
    
    
    
    public function getActiveRule()
    {
    	return (isset($this->_rules[$this->_currentRule])) ? $this->_currentRule : $this->_defaultRule;
    }
    
    
    
    public function getActiveOrder()
    {
        $s = $this->getActiveRule();
        return (isset($this->_rules[$s][$this->_currentOrder])) ? $this->_currentOrder : $this->_rules[$s]['defaultOrder'];
    }
    
    
    
    public function getClause($fullClause = false)
    {
        $c = '';
        $s = $this->getActiveRule();
        $o = strtolower($this->getActiveOrder());
        if(isset($this->_rules[$s][$o])) {
            $c = $this->_rules[$s][$o];
        }
        if($fullClause) { return ($c!='') ? ' ORDER BY '.$c : ''; }
        return $c;
    }
    
    
    
    public function getOrdering()
    {
        $u = array();
        $s = $this->getActiveRule();
        $o = $this->getActiveOrder();
        
        foreach($this->_rules as $k => $v) {
            if($s==$k) { $u[$k] = (preg_match('/asc/i', $o)) ? 'desc' : 'asc'; }
            else { $u[$k] = $v['defaultOrder']; }
        }
        return $u;
    }
    
    
    
    public function getColumns()
    {
        $columns = array();
        $s = $this->getActiveRule();
        
        foreach($this->_rules as $k => $v) {
            $columns[$k] = ($s==$k) ? $this->getActiveOrder() : 'none';
        }
        return $columns;
    }

}


?>