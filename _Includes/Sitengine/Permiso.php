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


abstract class Sitengine_Permiso
{
    
    
    const UID_ROOT = 'A';
	const UID_GUEST = '_A';
	const UID_LOSTFOUND = '__A';
	const GID_ADMINISTRATORS = 'A';
	const GID_LOSTFOUND = '__A';
	const ID_ROOT_MEMBERSHIP = 'A';
	const ROOT_NAME = 'root';
	const GUEST_NAME = 'guest';
	const LOSTFOUND_NAME = 'lostfound';
	
	const FIELD_UID = 'uid';
	const FIELD_GID = 'gid';
	const FIELD_RAG = 'rag';
	const FIELD_RAW = 'raw';
	const FIELD_UAG = 'uag';
	const FIELD_UAW = 'uaw';
	const FIELD_DAG = 'dag';
	const FIELD_DAW = 'daw';
	
    protected $_env = null;
    protected $_request = null;
    protected $_response = null;
    protected $_config = null;
    
    protected $_database = null;
    protected $_auth = null;
	protected $_authAdapter = null;
    protected $_audit = null;
    protected $_directory = null;
    protected $_acl = null;
    protected $_dac = null;
    
    
    # properties loaded from config
    protected $_usersTableName = 'permiso_users';
    protected $_groupsTableName = 'permiso_groups';
    protected $_membershipsTableName = 'permiso_memberships';
    protected $_auditTableName = 'permiso_audits';
    protected $_downloadHandler = null;
    protected $_userAvatarOriginalDir = null;
    protected $_userAvatarThumbDir = null;
    protected $_userAvatarOriginalRequestDir = null;
    protected $_userAvatarThumbRequestDir = null;
    protected $_minimalPasswordLength = 6;
    protected $_ownerGroup = null;
    protected $_authorizedGroups = null;
    
    
    
    
    public function __construct(
    	Sitengine_Env_Default $env,
    	Sitengine_Controller_Request_Http $request,
    	Zend_Controller_Response_Http $response,
    	Zend_Config $config = null
    )
    {
		$this->_env = $env;
		$this->_request = $request;
		$this->_response = $response;
		$this->_config = $config;
		if($config !== null)
		{
			$this->_mapConfig($config);
		}
    }
    
    
    
    
    public function start(Zend_Db_Adapter_Abstract $database)
    {
    	$this->_database = $database;
    	require_once 'Sitengine/Permiso/Audit.php';
    	$this->_audit = new Sitengine_Permiso_Audit($this, $database);
    	require_once 'Sitengine/Permiso/Directory.php';
		$this->_directory = new Sitengine_Permiso_Directory($this, $database);
		require_once 'Sitengine/Permiso/Acl.php';
		$this->_acl = new Sitengine_Permiso_Acl($this);
		require_once 'Sitengine/Permiso/Dac.php';
		$this->_dac = new Sitengine_Permiso_Dac($this);
		
		require_once 'Sitengine/Auth.php';
		$this->_auth = Sitengine_Auth::getInstance();
		require_once 'Sitengine/Auth/Storage/Session.php';
		$this->_auth->setStorage(new Sitengine_Auth_Storage_Session('Sitengine_Permiso_User', 'username'));
		require_once 'Sitengine/Auth/Adapter/DbTable.php';
		$this->_authAdapter = new Sitengine_Auth_Adapter_DbTable($database);
		return $this;
    }
    
    
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	if(
			isset($config->usersTableName) &&
			isset($config->groupsTableName) &&
			isset($config->membershipsTableName) &&
			isset($config->downloadHandler) &&
			isset($config->userAvatarOriginalDir) &&
    		isset($config->userAvatarThumbDir) &&
    		isset($config->userAvatarOriginalRequestDir) &&
    		isset($config->userAvatarThumbRequestDir) &&
    		isset($config->ownerGroup) &&
    		isset($config->authorizedGroups)
		)
		{
			$this->_usersTableName = $config->usersTableName;
			$this->_groupsTableName = $config->groupsTableName;
			$this->_membershipsTableName = $config->membershipsTableName;
			$this->_downloadHandler = $config->downloadHandler;
			$this->_userAvatarOriginalDir = $config->userAvatarOriginalDir;
    		$this->_userAvatarThumbDir = $config->userAvatarThumbDir;
    		$this->_userAvatarOriginalRequestDir = $config->userAvatarOriginalRequestDir;
    		$this->_userAvatarThumbRequestDir = $config->userAvatarThumbRequestDir;
    		$this->_ownerGroup = $config->ownerGroup;
    		$this->_authorizedGroups = $config->authorizedGroups->toArray();
		}
		else {
			require_once 'Sitengine/Permiso/Exception.php';
        	throw new Sitengine_Permiso_Exception('package config error');
       	}
       	return $this;
    }
    
    
    
    
    
    
    
    
    
    
    public function getRequest()
    {
    	return $this->_request;
    }
    
    
    public function getAuth()
    {
    	return $this->_auth;
    }
    
    
    public function getAuthAdapter()
    {
    	$this->_authAdapter
			->setTableName($this->getUsersTableName())
			->setIdentityColumn('name')
			->setCredentialColumn('password')
		;
		return $this->_authAdapter;
	}
	
	
    public function getAudit()
    {
    	return $this->_audit;
    }
    
    
    public function getDirectory()
    {
    	return $this->_directory;
    }
    
    
    public function getAcl()
    {
    	return $this->_acl;
    }
    
    
    public function getDac()
    {
    	return $this->_dac;
    }
    
    
    public function setUsersTableName($tableName)
    {
    	$this->_usersTableName = $tableName;
    }
    
    
    public function getUsersTableName()
    {
    	return $this->_usersTableName;
    }
    
    
    public function setGroupsTableName($tableName)
    {
    	$this->_groupsTableName = $tableName;
    }
    
    
    public function getGroupsTableName()
    {
    	return $this->_groupsTableName;
    }
    
    
    public function setMembershipsTableName($tableName)
    {
    	$this->_membershipsTableName = $tableName;
    }
    
    
    public function getMembershipsTableName()
    {
    	return $this->_membershipsTableName;
    }
    
    
    public function setAuditTableName($tableName)
    {
    	$this->_auditTableName = $tableName;
    }
    
    
    public function getAuditTableName()
    {
    	return $this->_auditTableName;
    }
    
    
    public function getDownloadHandler()
    {
    	return $this->_downloadHandler;
    }
    
    
    public function getUserAvatarOriginalDir()
    {
    	return $this->_userAvatarOriginalDir;
    }
    
    
    public function getUserAvatarThumbDir()
    {
    	return $this->_userAvatarThumbDir;
    }
    
    
    public function getUserAvatarOriginalRequestDir()
    {
    	return $this->_userAvatarOriginalRequestDir;
    }
    
    
    public function getUserAvatarThumbRequestDir()
    {
    	return $this->_userAvatarThumbRequestDir;
    }
    
    
    public function getMinimalPasswordLength()
    {
    	return $this->_minimalPasswordLength;
    }
    
    
    public function getOwnerGroup()
    {
    	return $this->_ownerGroup;
    }
    
    
    public function getAuthorizedGroups()
    {
    	return $this->_authorizedGroups;
    }
    
    
    
    
    
    
    
    
    
    
    
    protected $_usersTable = null;
    
    public function getUsersTable()
    {
    	if($this->_usersTable === null)
    	{
    		if($this->_database === null)
    		{
    			$message = 'table init error - start() must be called first';
    			require_once 'Sitengine/Permiso/Exception.php';
    			throw new Sitengine_Permiso_Exception($message);
    		}
    		$this->_usersTable = $this->_getUsersTableInstance();
    	}
    	return $this->_usersTable;
    }
    
    
    
    protected function _getUsersTableInstance()
    {
    	require_once 'Sitengine/Permiso/Users/Table.php';
		return new Sitengine_Permiso_Users_Table(
			array(
				'db' => $this->_database,
				'rowClass' => 'Sitengine_Permiso_Users_Row',
				'rowsetClass' => 'Sitengine_Permiso_Users_Rowset',
				'permisoPackage' => $this
			)
		);
    }
    
    
    
    
    
    protected $_groupsTable = null;
    
    public function getGroupsTable()
    {
    	if($this->_groupsTable === null)
    	{
    		if($this->_database === null)
    		{
    			$message = 'table init error - start() must be called first';
    			require_once 'Sitengine/Permiso/Exception.php';
    			throw new Sitengine_Permiso_Exception($message);
    		}
    		$this->_groupsTable = $this->_getGroupsTableInstance();
    	}
    	return $this->_groupsTable;
    }
    
    
    
    protected function _getGroupsTableInstance()
    {
    	require_once 'Sitengine/Permiso/Groups/Table.php';
		return new Sitengine_Permiso_Groups_Table(
			array(
				'db' => $this->_database,
				'rowClass' => 'Sitengine_Permiso_Groups_Row',
				'rowsetClass' => 'Sitengine_Permiso_Groups_Rowset',
				'permisoPackage' => $this
			)
		);
    }
    
    
    
    
    
    
    protected $_membershipsTable = null;
    
    public function getMembershipsTable()
    {
    	if($this->_membershipsTable === null)
    	{
    		if($this->_database === null)
    		{
    			$message = 'table init error - start() must be called first';
    			require_once 'Sitengine/Permiso/Exception.php';
    			throw new Sitengine_Permiso_Exception($message);
    		}
    		$this->_membershipsTable = $this->_getMembershipsTableInstance();
    	}
    	return $this->_membershipsTable;
    }
    
    
    
    protected function _getMembershipsTableInstance()
    {
    	require_once 'Sitengine/Permiso/Memberships/Table.php';
		return new Sitengine_Permiso_Memberships_Table(
			array(
				'db' => $this->_database,
				'rowClass' => 'Sitengine_Permiso_Memberships_Row',
				'rowsetClass' => 'Sitengine_Permiso_Memberships_Rowset',
				'permisoPackage' => $this
			)
		);
    }
    
}
?>