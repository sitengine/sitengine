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
    protected $_account = null;
    
    
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
		require_once 'Sitengine/Permiso/Model/Account.php';
		$this->_account = Sitengine_Permiso_Model_Account::getInstance($this);
		$this->_account->setTranslator($this->getModelTranslator());
		
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
    
    
    
    
    
    
    
    public function getDatabase()
    {
    	if($this->_database === null)
		{
			$message = 'start() must be called before calling getDatabase()';
			require_once 'Kompakt/Shop/Exception.php';
			throw new Kompakt_Shop_Exception($message);
		}
		return $this->_database;
    }
    
    
    
    protected $_language = 'en';
    
    
    public function setLanguage($language)
    {
    	$this->_language = $language;
    }
    
    
    public function getLanguage()
    {
    	return $this->_language;
    }
    
    
    
    
    
    
    
    
    public function getEnv()
    {
    	return $this->_env;
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
    
    
    public function getAccount()
    {
    	return $this->_account;
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
    
    
    
    
    
    
    
    protected $_modelTranslator = null;
    
    
    public function getModelTranslator()
	{
		if($this->_modelTranslator === null)
		{
			require_once 'Sitengine/Translate.php';
			$this->_modelTranslator = new Sitengine_Translate(
				Sitengine_Translate::AN_XML,
				dirname(__FILE__).'/Permiso/Model/_Dictionary',
				Sitengine_Env::LANGUAGE_EN,
				array('scan' => Zend_Translate::LOCALE_FILENAME)
			);
		}
		
		if($this->_modelTranslator->isAvailable($this->getLanguage()))
		{
			$this->_modelTranslator->setLocale($this->getLanguage());
		}
		
		return $this->_modelTranslator;
	}
    
    
    
    
    
    
    
    
    
    
    
    protected $_usersTable = null;
    
    public function getUsersTable()
    {
    	if($this->_usersTable === null)
    	{
    		require_once 'Sitengine/Permiso/Db/Users/Table.php';
			$this->_usersTable = new Sitengine_Permiso_Db_Users_Table(
				array(
					'db' => $this->getDatabase(),
					'rowClass' => 'Sitengine_Permiso_Db_Users_Row',
					'rowsetClass' => 'Sitengine_Permiso_Db_Users_Rowset',
					'permiso' => $this
				)
			);
    	}
    	return $this->_usersTable;
    }
    
    
    
    
    
    protected $_groupsTable = null;
    
    public function getGroupsTable()
    {
    	if($this->_groupsTable === null)
    	{
    		require_once 'Sitengine/Permiso/Db/Groups/Table.php';
			$this->_groupsTable = new Sitengine_Permiso_Db_Groups_Table(
				array(
					'db' => $this->getDatabase(),
					'rowClass' => 'Sitengine_Permiso_Db_Groups_Row',
					'rowsetClass' => 'Sitengine_Permiso_Db_Groups_Rowset',
					'permiso' => $this
				)
			);
    	}
    	return $this->_groupsTable;
    }
    
    
    
    
    
    
    protected $_membershipsTable = null;
    
    public function getMembershipsTable()
    {
    	if($this->_membershipsTable === null)
    	{
    		require_once 'Sitengine/Permiso/Db/Memberships/Table.php';
			$this->_membershipsTable = new Sitengine_Permiso_Db_Memberships_Table(
				array(
					'db' => $this->getDatabase(),
					'rowClass' => 'Sitengine_Permiso_Db_Memberships_Row',
					'rowsetClass' => 'Sitengine_Permiso_Db_Memberships_Rowset',
					'permiso' => $this
				)
			);
    	}
    	return $this->_membershipsTable;
    }
    
}
?>