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



require_once 'PEAR/Exception.php';


class Sitengine_Exception extends PEAR_Exception
{
	
	protected $_priority = null;
	
	
	public function __construct($message, $p2 = null, $p3 = null, $priority = null)
	{
		$this->_priority = $priority;
		parent::__construct($message, $p2, $p3);
	}
	
	
	public function getPriority()
	{
		return $this->_priority;
	}
	
}

?>