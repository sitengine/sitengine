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
 
 
require_once 'Zend/Controller/Router/Route.php';


class Sitengine_Controller_Router_Route extends Zend_Controller_Router_Route
{
	
	protected $_representationParam = null;
	protected $_defaultRepresentation = null;
	
	
	
	public function __construct($route, $defaults = array(), $reqs = array())
    {
    	if(preg_match('/\.\w*$/', $route, $matches))
    	{
    		$this->_defaultRepresentation = trim($matches[0], '.');
    		$route = preg_replace('/(\.\w*)?$/', '', $route);
    	}
		parent::__construct($route, $defaults, $reqs);
    }
    
	
	public function match($path)
    {
    	$representation = null;
    	
    	if(preg_match('/\.\w*$/', $path, $matches))
    	{
    		$representation = trim($matches[0], '.');
    		$path = preg_replace('/(\.\w*)?$/', '', $path);
    	}
    	
    	if($this->_representationParam !== null && $this->_defaultRepresentation !== null)
    	{
    		$this->_defaults[$this->_representationParam] = $this->_defaultRepresentation;
    	}
    	
    	$return = parent::match($path);
    	
    	if(
    		is_array($return) &&
    		$this->_representationParam !== null &&
    		!isset($return[$this->_representationParam])
    	)
    	{
    		$return[$this->_representationParam] = $representation;
    	}
    	return $return;
    }
    
    
    public function assemble($data = array(), $reset = false, $encode = false)
    {
		$return = parent::assemble($data, $reset, $encode);
		
		if($this->_representationParam !== null)
    	{
    		if($return && isset($data[$this->_representationParam]))
			{
				$return .= '.'.$data[$this->_representationParam];
			}
    	}
		return $return;
    }
    
    
	public function setRepresentationParam($representationParam)
	{
		$this->_representationParam = $representationParam;
		return $this;
	}
}


?>