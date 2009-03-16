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
 * @package    Sitengine_Form
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


class Sitengine_Form_Payloads
{
	
	const NAME_MAIN = 'main';
    
    protected $_name = null;
    protected $_names = array();
	
	
	public function getMainName() { return self::NAME_MAIN; }
	
	
	public function __construct(array $names = array())
	{
		$this->_names = array_merge(array(self::NAME_MAIN), $names);
	}
	
	
	public function start($name = null)
	{
		if($name === null) { $this->_name = self::NAME_MAIN; }
		else {
			$this->_name = (array_search($name, $this->_names) === false) ? self::NAME_MAIN : $name; # verify, set default
		}
	}
	
	
	public function getNames()
    {
		return $this->_names;
    }
    
    
    public function getName()
    {
    	return $this->_name;
    }
    
    
    public function isMain()
    {
    	return ($this->_name == self::NAME_MAIN);
    }
    
}


?>