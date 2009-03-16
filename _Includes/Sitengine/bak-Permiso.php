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



class Sitengine_Permiso
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
	
	protected $_auth = null;
	protected $_authAdapter = null;
    protected $_audit = null;
    protected $_directory = null;
    protected $_user = null;
    protected $_acl = null;
    protected $_dac = null;
    
    protected $_usersTableName = 'permiso_users';
    protected $_groupsTableName = 'permiso_groups';
    protected $_membershipsTableName = 'permiso_memberships';
    protected $_auditTableName = 'permiso_audits';
    
    public function getAuth() { return $this->_auth; }
    
    public function getAuthAdapter()
    {
    	$this->_authAdapter
			->setTableName($this->getUsersTableName())
			->setIdentityColumn('name')
			->setCredentialColumn('password')
		;
		return $this->_authAdapter;
	}
	
    public function getAudit() { return $this->_audit; }
    public function getDirectory() { return $this->_directory; }
    public function getAcl() { return $this->_acl; }
    public function getDac() { return $this->_dac; }
    
    
    
    
    
    public function __construct(
    	Sitengine_Env_Default $env,
    	Sitengine_Controller_Request_Http $request,
    	Zend_Controller_Response_Http $response,
    	Zend_Config $config
    )
    {
		$this->_env = $env;
		$this->_request = $request;
		$this->_response = $response;
		$this->_config = $config;
		#$this->_mapConfig($config);
    }
    
    
    
    public function start(Zend_Db_Adapter_Abstract $database)
    {
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
    
    
    
    /*
    protected static $_instance = null;
    

    private function __construct(Zend_Db_Adapter_Abstract $database)
    {
    	$this->_init($database);
    }

    private function __clone() {}
    
    
    
    public static function getInstance(Zend_Db_Adapter_Abstract $database)
    {
        if (self::$_instance === null) {
        	self::$_instance = new self($database);
        }
        return self::$_instance;
    }
    
    
    
    protected function _init(Zend_Db_Adapter_Abstract $database)
    {
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
    }
    */
    
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
    
    
    
}

?>