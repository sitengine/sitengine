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


require_once 'Sitengine/Db/TableWithFiles.php';


class Sitengine_Blog_Comments_Table extends Sitengine_Db_TableWithFiles
{
    
    
    const VALUE_NONESELECTED = 'noneSelected';
    
    protected $_blogPackage = null;
	#protected $_transcript = null;
    
    
    
    
    public function __construct(array $config = array())
    {
    	if(
    		isset($config['blogPackage']) &&
    		$config['blogPackage'] instanceof Sitengine_Blog
    	) {
    		$this->_blogPackage = $config['blogPackage'];
    		$this->_name = $this->_blogPackage->getCommentsTableName();
    		$this->_primary = 'id';
    		
    		parent::__construct($config);
    	}
    	else {
			require_once 'Sitengine/Blog/Exception.php';
			throw new Sitengine_Blog_Exception('comments table class init error');
		}
    }
    
    
    
    public function getBlogPackage()
    {
    	return $this->_blogPackage;
    }
    
    
    
    
    public function getDefaultPermissionData(Sitengine_Permiso $permiso, $ownerGroup)
    {
    	$gid = $permiso->getDirectory()->getGroupId($ownerGroup);
		$gid = (!is_null($gid)) ? $gid : Sitengine_Permiso::GID_ADMINISTRATORS;
		
		return array(
			Sitengine_Permiso::FIELD_UID => $permiso->getAuth()->getId(),
			Sitengine_Permiso::FIELD_GID => $gid,
			Sitengine_Permiso::FIELD_RAG => 1,
			Sitengine_Permiso::FIELD_RAW => 1,
			Sitengine_Permiso::FIELD_UAG => 1,
			Sitengine_Permiso::FIELD_UAW => 0,
			Sitengine_Permiso::FIELD_DAG => 1,
			Sitengine_Permiso::FIELD_DAW => 0
		);
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
    	Zend_Session_Namespace $namespace,
    	$usersTable
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
			$params['find'],
			self::VALUE_NONESELECTED
		);
		# set clause
		if($filter->getVal($params['find'])) {
			$value = $this->_database->quote($filter->getVal($params['find']));
			$value = preg_replace('/\s+/', ' ', $value);
			$value = preg_replace('/.(.*)./', "$1", trim($value)); # remove quote delimiters
			$clause  = "(";
			$clause .= " LOWER({$this->_name}.comment) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER($usersTable.name) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER($usersTable.firstname) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER($usersTable.lastname) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER($usersTable.nickname) LIKE LOWER('%$value%')";
			$clause .= ")";
			$filter->setClause($params['find'], $clause);
		}
		return $filter;
    }
    
    
    
    
    public function getSortingInstance($currentRule, $currentOrder, $usersTable)
    {
    	require_once 'Sitengine/Grid/Sorting.php';
    	$sorting = new Sitengine_Grid_Sorting($currentRule, $currentOrder);
		$sorting
			->addRule('cdate', 'desc', "{$this->_name}.cdate asc", "{$this->_name}.cdate desc")
			->addRule('mdate', 'desc', "{$this->_name}.mdate asc", "{$this->_name}.mdate desc")
			->addRule('publish', 'asc', "{$this->_name}.publish asc", "{$this->_name}.publish desc")
			->addRule('comment', 'asc', "{$this->_name}.comment asc", "{$this->_name}.comment desc")
			->addRule('name', 'asc', "$usersTable.name asc", "$usersTable.name desc")
			->addRule('firstname', 'asc', "$usersTable.firstname asc", "$usersTable.firstname desc")
			->addRule('lastname', 'asc', "$usersTable.lastname asc", "$usersTable.lastname desc")
			->addRule('nickname', 'asc', "$usersTable.nickname asc", "$usersTable.nickname desc")
			->setDefaultRule('cdate')
		;
		return $sorting;
    }
    
}


?>