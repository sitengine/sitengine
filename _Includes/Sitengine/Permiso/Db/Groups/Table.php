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
 * @package    Sitengine_Permiso
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/Db/TableWithFiles.php';


class Sitengine_Permiso_Db_Groups_Table extends Sitengine_Db_TableWithFiles
{
    
    
    protected $_permiso = null;
    
    
    
    
    public function __construct(array $config = array())
    {
    	if(
    		isset($config['permiso']) &&
    		$config['permiso'] instanceof Sitengine_Permiso
    	) {
    		$this->_permiso = $config['permiso'];
    		$this->_name = $this->_permiso->getGroupsTableName();
    		$this->_primary = 'id';
    		
    		parent::__construct($config);
    	}
    	else {
			require_once 'Sitengine/Permiso/Exception.php';
			throw new Sitengine_Permiso_Exception('groups table class init error');
		}
    }
    
    
    
    public function getPermiso()
    {
    	return $this->_permiso;
    }
    
    
    
    
    public function complementRow(Sitengine_Permiso_Db_Groups_Row $row)
    {
		return $row->toArray();
    }
    
    
    
    
    public function checkModifyException(Zend_Exception $exception)
    {
    	if(preg_match('/Duplicate entry.*for key (\'name\'|2)/i', $exception->getMessage())) {
    		$this->_setError('nameExists');
            return false;
    	}
    	return true;
    }
    
    
    
    
    
	public function delete($where)
    {
    	try {
    		$deleted = 0;
    		$groups = $this->selectRowsAndFiles($where);
    		
    		foreach($groups as $group)
    		{
				$where = $this->getAdapter()->quoteInto('groupId = ?', $group->id);
				$deleted += $this->_permiso->getMembershipsTable()->delete($where);
				$deleted += $this->deleteRowAndFiles($group);
    		}
    		return $deleted;
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Permiso/Exception.php';
        	throw new Sitengine_Permiso_Exception('group delete error', $exception);
		}
    }
    
    
    
    
    /*
    $params = array(
    	'find' => '',
    	'reset' => ''
    );
    */
    public function getFilterInstance(
    	Sitengine_Controller_Request_Http $request,
    	array $params,
    	Zend_Session_Namespace $namespace
    )
    {
    	require_once 'Sitengine/Grid/Search.php';
    	$filter = new Sitengine_Grid_Search();
		$reset = ($request->get($params['reset']));
		
		### filter element ###
		if($reset) { $filter->resetSessionVal($params['find'], $namespace); }
		$filter->registerSessionVal(
			$namespace,
			$request,
			$params['find']
		);
		# set clause
		if($filter->getVal($params['find'])) {
			$value = $this->getAdapter()->quote($filter->getVal($params['find']));
			$value = preg_replace('/\s+/', ' ', $value);
			$value = preg_replace('/.(.*)./', "$1", trim($value)); # remove quote delimiters
			$clause  = "(";
			$clause .= "LOWER({$this->_name}.name) LIKE LOWER('%$value%')";
			$clause .= ")";
			$filter->setClause($params['find'], $clause);
		}
		return $filter;
    }
    
    
    
    public function getSortingInstance($currentRule, $currentOrder)
    {
    	require_once 'Sitengine/Grid/Sorting.php';
    	$sorting = new Sitengine_Grid_Sorting($currentRule, $currentOrder);
		$sorting
			->addRule('cdate', 'desc', "{$this->_name}.cdate asc", "{$this->_name}.cdate desc")
			->addRule('mdate', 'desc', "{$this->_name}.mdate asc", "{$this->_name}.mdate desc")
			->addRule('enabled', 'asc', "{$this->_name}.enabled asc", "{$this->_name}.enabled desc")
			->addRule('locked', 'asc', "{$this->_name}.locked asc", "{$this->_name}.locked desc")
			->addRule('name', 'asc', "{$this->_name}.name asc", "{$this->_name}.name desc")
			->setDefaultRule('name')
		;
		return $sorting;
    }

    
}


?>