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
 * @package    Sitengine_Blog
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Zend/Db/Table/Row/Abstract.php';

class Sitengine_Blog_Posts_Row extends Zend_Db_Table_Row_Abstract
{
	
	public function toArray()
	{
		return $this->_data;
	}
	
	
    protected function _insert()
    {
    	#print get_class($this).'::_insert()<br />';
    }
    
    
    protected function _postInsert()
    {
    	#print get_class($this).'::_postInsert()<br />';
    }
	
	
    protected function _update()
    {
    	#print get_class($this).'::_update()<br />';
    }
	
	
    protected function _postUpdate()
    {
    	#print get_class($this).'::_postUpdate()<br />';
    }
	
	
    protected function _delete()
    {
    	#print get_class($this).'::_delete()<br />';
    }
	
	
    protected function _postDelete()
    {
    	#print get_class($this).'::_postDelete()<br />';
    }
}

?>
