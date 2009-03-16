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



require_once 'Sitengine/String.php';


class Sitengine_Grid_Search
{

    
    protected $_data = array();
    
    
    public function registerSessionVal(Zend_Session_Namespace $namespace, Sitengine_Controller_Request_Http $request, $param, $ignore='noneSelected')
    {
		if(is_null($request->get($param)))
		{
			$value = (isset($namespace->$param)) ? Sitengine_String::runtimeStripSlashes($namespace->$param) : '';
			#print $param.' = null<br />';
		}
		else {
			$value = $request->get($param);
			$value = ($value==$ignore) ? '' : $value;
			if($value != '') { $namespace->$param = $value; }
			else { unset($namespace->$param); }
			#print $param.' = '.$request->get($param).'<br />';
		}
        $this->_data[$param] = array(
            'value' => $value,
            'clause' => '',
            'element' => ''
        );
    }
    
    
    public function registerVal(Sitengine_Controller_Request_Http $request, $param, $ignore='noneSelected')
    {
        $value = $request->get($param);
        if($value==$ignore) { $value = ''; }
        $this->_data[$param] = array(
            'value' => $value,
            'clause' => '',
            'element' => ''
        );
    }
    
    
    public function resetSessionVals(Zend_Session_Namespace $namespace)
    {
    	foreach($this->_data as $key => $criteria)
    	{
        	unset($namespace->$key);
        	$this->_data[$key]['value'] = '';
        }
    }
    
    
    public function resetSessionVal($param, Zend_Session_Namespace $namespace)
    {
        unset($namespace->$param);
        $this->_data[$param]['value'] = '';
    }
    
    
    public function resetVals()
    {
    	foreach($this->_data as $key => $criteria)
    	{
        	$this->_data[$key]['value'] = '';
        }
    }
    
    
    public function resetVal($param)
    {
        $this->_data[$param]['value'] = '';
    }
    
    
    public function show()
    {
        Sitengine_Debug::print_r($this->_data);
    }
    
    
    public function getVal($param)
    {
        return (isset($this->_data[$param]['value'])) ? $this->_data[$param]['value'] : '';
    }
    
    
    public function setClause($param, $clause)
    {
        $this->_data[$param]['clause'] = $clause;
    }
    
    
    public function setElement($param, $element)
    {
        $this->_data[$param]['element'] = $element;
    }
    
    
    public function getSql($prefix = 'AND')
    {
        $sql = '';
        foreach($this->_data as $k => $v)
        {
            if(isset($v['clause']) && $v['clause'])
            {
                $operator = ($sql) ? ' AND ' : '';
                $sql .= $operator.$v['clause'];
            }
        }
        return ($sql) ? ' '.$prefix.' '.$sql : '';
    }
    
    
    public function isActive()
    {
        foreach($this->_data as $v) {
            if($v['value'] != '') { return true; }
        }
        return false;
    }
    
    
    public function getElements()
    {
        $elements = array();
        foreach($this->_data as $k => $v)
        {
            $elements[$k] = $v['element'];
        }
        return $elements;
    }
    
    
    public function getData()
    {
        return $this->_data;
    }

}


?>