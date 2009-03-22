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


require_once 'Zend/View.php';


abstract class Sitengine_View extends Zend_View
{
    
    /*
    protected $_sections = array();
    
    
    public function setSection($name, array $data)
    {
        $this->_sections[$name] = $data;
    }
   	*/
    
    
    
    public function __call($name, $args)
    {
        if(!preg_match('/^get/', $name))
        {
        	return parent::__call($name, $args);
        }
        
		$property = preg_replace('/^get/', '', $name);
		$first = mb_convert_case(mb_substr($property, 0, 1), MB_CASE_LOWER);
		$property = '_'.preg_replace('/^./', $first, $property);
		return (isset($this->$property)) ? $this->$property : parent::__call($name, $args);
    }
    
    
    
    public function batchAssign(array $data)
    {
    	require_once 'Sitengine/DataObject.php';
    	
    	foreach($data as $n => $v)
    	{
    		if(is_object($v)) { $this->assign($n, $v); }
    		else { $this->assign($n, new Sitengine_DataObject($v)); }
    	}
    	return $this;
    }
    
    
    
    public function html($val)
    {
    	return Sitengine_String::html($val);
    }
    
    
    
    public function truncate(
    	$string,
    	$length = 80,
    	$etc = '...',
    	$breakWords = false,
    	$middle = false
    )
	{
		require_once 'Sitengine/String.php';
		return Sitengine_String::truncate($string, $length, $etc, $breakWords, $middle);
	}
}


?>