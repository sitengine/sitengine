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


class Sitengine_Model_List implements Iterator, Countable
{
	
    
    const OPTION_WHERE = 'where';
    const OPTION_OR_WHERE = 'orWhere';
    const OPTION_HAVING = 'having';
    const OPTION_LIMIT = 'limit';
    const OPTION_OFFSET = 'offset';
    const OPTION_ORDER = 'order';
    
    
	protected $_data = array();
	protected $_started = false;
	protected $_loaded = false;
    
    
    
    public function start(array $options = array(), $force = false)
    {
    	if(!$this->_started || $force)
    	{
    		$this->_started = true;
    		$this->_loaded = $this->_load($options);
    		$this->rewind();
    	}
    	return $this;
    }
    
    
    
    protected function _applySelectOptions(
    	Zend_Db_Table_Select $select,
    	array $options = array()
    )
    {
    	if(isset($options[self::OPTION_WHERE]))
		{
			foreach((array) $options[self::OPTION_WHERE] as $where)
			{
				$select->where($where);
			}
		}
		
		if(isset($options[self::OPTION_OR_WHERE]))
		{
			foreach((array) $options[self::OPTION_OR_WHERE] as $orWhere)
			{
				$select->orWhere($orWhere);
			}
		}
		
		if(isset($options[self::OPTION_HAVING]))
		{
			foreach((array) $options[self::OPTION_HAVING] as $having)
			{
				$select->having($having);
			}
		}
		
		if(isset($options[self::OPTION_ORDER]))
		{
			foreach((array) $options[self::OPTION_ORDER] as $order)
			{
				$select->order($order);
			}
		}
		
		$limit = (isset($options[self::OPTION_LIMIT])) ? $options[self::OPTION_LIMIT] : null;
		$offset = (isset($options[self::OPTION_OFFSET])) ? $options[self::OPTION_OFFSET] : null;
		$select->limit($limit, $offset);
		return $select;
    }
    
    
    
	public function _load(array $options = array())
	{
		$this->_isLoaded = true;
		return true;
	}
    
	
	
	public function isLoaded()
	{
		return $this->_loaded;
	}
	
	
	
	public function count()
    {
        return count($this->_data);
    }
    
    
    
    public function current()
    {
        $key = key($this->_data);
        return (isset($this->_data[$key])) ? $this->_data[$key] : false;
    }
    
    
    
    public function key()
    {
        return key($this->_data);
    }
    
    
    
    public function next()
    {
        return next($this->_data);
    }
    
    
    
    public function rewind()
    {
        return reset($this->_data);
    }
    
    
    
    public function valid()
    {
        return (bool) $this->current();
    }
	
}



?>