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


require_once 'Zend/Db/Table/Row/Abstract.php';


class Sitengine_Db_Table_Row extends Zend_Db_Table_Row_Abstract
{
	
    public function __call($method, array $args)
    {
        if(preg_match('/^set(\w*)/', $method, $matches))
        {
        	$first = mb_convert_case(mb_substr($matches[1], 0, 1), MB_CASE_LOWER);
			$column = preg_replace('/^./', $first, $matches[1]);
        	
        	if(count($args))
        	{
        		$this->__set($column, $args[0]);
        		return $this;
        	}
        }
        else if(preg_match('/^get(\w*)/', $method, $matches))
        {
        	$first = mb_convert_case(mb_substr($matches[1], 0, 1), MB_CASE_LOWER);
			$column = preg_replace('/^./', $first, $matches[1]);
			return $this->__get($column);
        }
        
        parent::__call($method, $args);
    }
    
}

?>
