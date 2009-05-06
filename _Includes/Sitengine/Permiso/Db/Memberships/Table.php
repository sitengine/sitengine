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


class Sitengine_Permiso_Db_Memberships_Table extends Sitengine_Db_TableWithFiles
{
    
    
    protected $_permiso = null;
    
    
    
    
    public function __construct(array $config = array())
    {
    	if(
    		isset($config['permiso']) &&
    		$config['permiso'] instanceof Sitengine_Permiso
    	) {
    		$this->_permiso = $config['permiso'];
    		$this->_name = $this->_permiso->getMembershipsTableName();
    		$this->_primary = 'id';
    		
    		parent::__construct($config);
    	}
    	else {
			require_once 'Sitengine/Permiso/Exception.php';
			throw new Sitengine_Permiso_Exception('memberships table class init error');
		}
    }
    
    
    
    public function getPermiso()
    {
    	return $this->_permiso;
    }
    
    
    
    
    public function complementRow(Sitengine_Permiso_Db_Memberships_Row $row)
    {
		return $row->toArray();
    }
    
    
    
    
    
    public function checkModifyException(Zend_Exception $exception)
    {
    	if(preg_match('/Duplicate entry.*for key (\'membership\'|2)/i', $exception->getMessage())) {
    		$this->_setError('membershipExists');
            return false;
    	}
    	return true;
    }
    
	
	
	
	
	public function delete($where)
    {
    	try {
    		return $this->getAdapter()->delete($this->_name, $where);
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Permiso/Exception.php';
        	throw new Sitengine_Permiso_Exception('membership delete error', $exception);
		}
    }  
    
    
    
    
    
    /*
    $params = array(
    	'find' => '',
    	'reset' => ''
    );
    */
    public function getGroupJoinFilterInstance(
    	Sitengine_Controller_Request_Http $request,
    	array $params,
    	Zend_Session_Namespace $namespace
    )
    {
    	require_once 'Sitengine/Grid/Search.php';
    	$filter = new Sitengine_Grid_Search();
		$reset = ($request->get($params['reset']));
		$groupsTableName = $this->getPermiso()->getGroupsTableName();
		
		### filter element ###
		if($reset) { $filter->resetSessionVal($params['find'], $namespace); }
		$filter->registerSessionVal(
			$namespace,
			$request,
			$params['find']
		);
		# set clause
		if($filter->getVal($params['find']))
		{
			$value = $this->getAdapter()->quote($filter->getVal($params['find']));
			$value = preg_replace('/\s+/', ' ', $value);
			$value = preg_replace('/.(.*)./', "$1", trim($value)); # remove quote delimiters
			$clause  = "(";
			$clause .= "LOWER({$groupsTableName}.name) LIKE LOWER('%$value%')";
			$clause .= ")";
			$filter->setClause($params['find'], $clause);
		}
		return $filter;
    }
    
    
    
    
    public function getUserJoinFilterInstance(
    	Sitengine_Controller_Request_Http $request,
    	array $params,
    	Zend_Session_Namespace $namespace
    )
    {
    	require_once 'Sitengine/Grid/Search.php';
    	$filter = new Sitengine_Grid_Search();
		$reset = ($request->get($params['reset']));
		$usersTableName = $this->getPermiso()->getUsersTableName();
		
		### filter element ###
		if($reset) { $filter->resetSessionVal($params['find'], $namespace); }
		$filter->registerSessionVal(
			$namespace,
			$request,
			$params['find']
		);
		# set clause
		if($filter->getVal($params['find']))
		{
			$value = $this->getAdapter()->quote($filter->getVal($params['find']));
			$value = preg_replace('/\s+/', ' ', $value);
			$value = preg_replace('/.(.*)./', "$1", trim($value)); # remove quote delimiters
			$clause  = "(";
			$clause .= "LOWER({$usersTableName}.name) LIKE LOWER('%$value%')";
			$clause .= "OR LOWER({$usersTableName}.nickname) LIKE LOWER('%$value%')";
			$clause .= "OR LOWER({$usersTableName}.firstname) LIKE LOWER('%$value%')";
			$clause .= "OR LOWER({$usersTableName}.lastname) LIKE LOWER('%$value%')";
			$clause .= ")";
			$filter->setClause($params['find'], $clause);
		}
		return $filter;
    }
    
    
    
    
    
    public function getGroupJoinSortingInstance($currentRule, $currentOrder)
    {
    	$groupsTableName = $this->getPermiso()->getGroupsTableName();
    	
    	require_once 'Sitengine/Grid/Sorting.php';
    	$sorting = new Sitengine_Grid_Sorting($currentRule, $currentOrder);
		$sorting
			->addRule('cdate', 'desc', "{$this->_name}.cdate asc", "{$this->_name}.cdate desc")
			->addRule('mdate', 'desc', "{$this->_name}.mdate asc", "{$this->_name}.mdate desc")
			->addRule('locked', 'asc', "{$this->_name}.locked asc", "{$this->_name}.locked desc")
			->addRule('publish', 'asc', "{$groupsTableName}.publish asc", "{$groupsTableName}.publish desc")
			->addRule('name', 'asc', "{$groupsTableName}.name asc", "{$groupsTableName}.name desc")
			->setDefaultRule('name')
		;
		return $sorting;
    }
    
    
    
    
    
    public function getUserJoinSortingInstance($currentRule, $currentOrder)
    {
    	$usersTableName = $this->getPermiso()->getUsersTableName();
    	
    	require_once 'Sitengine/Grid/Sorting.php';
    	$sorting = new Sitengine_Grid_Sorting($currentRule, $currentOrder);
		$sorting
			->addRule('cdate', 'desc', "{$this->_name}.cdate asc", "{$this->_name}.cdate desc")
			->addRule('mdate', 'desc', "{$this->_name}.mdate asc", "{$this->_name}.mdate desc")
			->addRule('locked', 'asc', "{$this->_name}.locked asc", "{$this->_name}.locked desc")
			->addRule('publish', 'asc', "{$usersTableName}.publish asc", "{$usersTableName}.publish desc")
			->addRule('name', 'asc', "{$usersTableName}.name asc", "{$usersTableName}.name desc")
			->addRule('nickname', 'asc', "{$usersTableName}.nickname asc", "{$usersTableName}.nickname desc")
			->addRule('firstname', 'asc', "{$usersTableName}.firstname asc", "{$usersTableName}.firstname desc")
			->addRule('lastname', 'asc', "{$usersTableName}.lastname asc", "{$usersTableName}.lastname desc")
			->setDefaultRule('name')
		;
		return $sorting;
    }

    
}


?>