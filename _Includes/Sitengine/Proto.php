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
 * @package    Sitengine_Proto
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


abstract class Sitengine_Proto
{
    
    protected $_env = null;
    protected $_request = null;
    protected $_response = null;
    protected $_config = null;
    
    
    # properties loaded from config
    protected $_goodiesTableName = null;
    protected $_shouldiesTableName = null;
    protected $_couldiesTableName = null;
    protected $_goodyTempDir = null;
    protected $_shouldyTempDir = null;
    protected $_couldyTempDir = null;
    protected $_tempDirPerUser = true;
    protected $_downloadHandler = null;
    
    protected $_goodyFile1OriginalDir = null;
    protected $_goodyFile1ThumbnailDir = null;
    protected $_goodyFile1FittedDir = null;
    protected $_goodyFile1OriginalRequestDir = null;
    protected $_goodyFile1ThumbnailRequestDir = null;
    protected $_goodyFile1FittedRequestDir = null;
    protected $_goodyFile2OriginalDir = null;
    protected $_goodyFile2ThumbnailDir = null;
    protected $_goodyFile2FittedDir = null;
    protected $_goodyFile2OriginalRequestDir = null;
    protected $_goodyFile2ThumbnailRequestDir = null;
    protected $_goodyFile2FittedRequestDir = null;
    
    protected $_shouldyFile1OriginalDir = null;
    protected $_shouldyFile1ThumbnailDir = null;
    protected $_shouldyFile1FittedDir = null;
    protected $_shouldyFile1OriginalRequestDir = null;
    protected $_shouldyFile1ThumbnailRequestDir = null;
    protected $_shouldyFile1FittedRequestDir = null;
    protected $_shouldyFile2OriginalDir = null;
    protected $_shouldyFile2ThumbnailDir = null;
    protected $_shouldyFile2FittedDir = null;
    protected $_shouldyFile2OriginalRequestDir = null;
    protected $_shouldyFile2ThumbnailRequestDir = null;
    protected $_shouldyFile2FittedRequestDir = null;
    
    protected $_couldyFile1OriginalDir = null;
    protected $_couldyFile1ThumbnailDir = null;
    protected $_couldyFile1FittedDir = null;
    protected $_couldyFile1OriginalRequestDir = null;
    protected $_couldyFile1ThumbnailRequestDir = null;
    protected $_couldyFile1FittedRequestDir = null;
    protected $_couldyFile2OriginalDir = null;
    protected $_couldyFile2ThumbnailDir = null;
    protected $_couldyFile2FittedDir = null;
    protected $_couldyFile2OriginalRequestDir = null;
    protected $_couldyFile2ThumbnailRequestDir = null;
    protected $_couldyFile2FittedRequestDir = null;
    
    protected $_ownerGroup = null;
    protected $_authorizedGroups = null;
    
    
    
    
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
		$this->_mapConfig($config);
    }
    
    
    
    public function getRequest()
    {
    	return $this->_request;
    }
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	if(
			isset($config->goodiesTableName) &&
			isset($config->shouldiesTableName) &&
			isset($config->couldiesTableName) &&
			isset($config->goodyTempDir) &&
			isset($config->shouldyTempDir) &&
			isset($config->couldyTempDir) &&
			isset($config->tempDirPerUser) &&
			isset($config->downloadHandler) &&
			
			isset($config->goodyFile1OriginalDir) &&
    		isset($config->goodyFile1ThumbnailDir) &&
    		isset($config->goodyFile1FittedDir) &&
    		isset($config->goodyFile1OriginalRequestDir) &&
    		isset($config->goodyFile1ThumbnailRequestDir) &&
    		isset($config->goodyFile1FittedRequestDir) &&
    		isset($config->goodyFile2OriginalDir) &&
    		isset($config->goodyFile2ThumbnailDir) &&
    		isset($config->goodyFile2FittedDir) &&
    		isset($config->goodyFile2OriginalRequestDir) &&
    		isset($config->goodyFile2ThumbnailRequestDir) &&
    		isset($config->goodyFile2FittedRequestDir) &&
    		
    		isset($config->shouldyFile1OriginalDir) &&
    		isset($config->shouldyFile1ThumbnailDir) &&
    		isset($config->shouldyFile1FittedDir) &&
    		isset($config->shouldyFile1OriginalRequestDir) &&
    		isset($config->shouldyFile1ThumbnailRequestDir) &&
    		isset($config->shouldyFile1FittedRequestDir) &&
    		isset($config->shouldyFile2OriginalDir) &&
    		isset($config->shouldyFile2ThumbnailDir) &&
    		isset($config->shouldyFile2FittedDir) &&
    		isset($config->shouldyFile2OriginalRequestDir) &&
    		isset($config->shouldyFile2ThumbnailRequestDir) &&
    		isset($config->shouldyFile2FittedRequestDir) &&
    		
    		isset($config->couldyFile1OriginalDir) &&
    		isset($config->couldyFile1ThumbnailDir) &&
    		isset($config->couldyFile1FittedDir) &&
    		isset($config->couldyFile1OriginalRequestDir) &&
    		isset($config->couldyFile1ThumbnailRequestDir) &&
    		isset($config->couldyFile1FittedRequestDir) &&
    		isset($config->couldyFile2OriginalDir) &&
    		isset($config->couldyFile2ThumbnailDir) &&
    		isset($config->couldyFile2FittedDir) &&
    		isset($config->couldyFile2OriginalRequestDir) &&
    		isset($config->couldyFile2ThumbnailRequestDir) &&
    		isset($config->couldyFile2FittedRequestDir) &&
    		
    		isset($config->ownerGroup) &&
    		isset($config->authorizedGroups)
		)
		{
			$this->_goodiesTableName = $config->goodiesTableName;
			$this->_shouldiesTableName = $config->shouldiesTableName;
			$this->_couldiesTableName = $config->couldiesTableName;
			$this->_goodyTempDir = $config->goodyTempDir;
			$this->_shouldyTempDir = $config->shouldyTempDir;
			$this->_couldyTempDir = $config->couldyTempDir;
			$this->_tempDirPerUser = $config->tempDirPerUser;
			$this->_downloadHandler = $config->downloadHandler;
			
			$this->_goodyFile1OriginalDir = $config->goodyFile1OriginalDir;
    		$this->_goodyFile1ThumbnailDir = $config->goodyFile1ThumbnailDir;
    		$this->_goodyFile1FittedDir = $config->goodyFile1FittedDir;
    		$this->_goodyFile1OriginalRequestDir = $config->goodyFile1OriginalRequestDir;
    		$this->_goodyFile1ThumbnailRequestDir = $config->goodyFile1ThumbnailRequestDir;
    		$this->_goodyFile1FittedRequestDir = $config->goodyFile1FittedRequestDir;
    		$this->_goodyFile2OriginalDir = $config->goodyFile2OriginalDir;
    		$this->_goodyFile2ThumbnailDir = $config->goodyFile2ThumbnailDir;
    		$this->_goodyFile2FittedDir = $config->goodyFile2FittedDir;
    		$this->_goodyFile2OriginalRequestDir = $config->goodyFile2OriginalRequestDir;
    		$this->_goodyFile2ThumbnailRequestDir = $config->goodyFile2ThumbnailRequestDir;
    		$this->_goodyFile2FittedRequestDir = $config->goodyFile2FittedRequestDir;
    		
    		$this->_shouldyFile1OriginalDir = $config->shouldyFile1OriginalDir;
    		$this->_shouldyFile1ThumbnailDir = $config->shouldyFile1ThumbnailDir;
    		$this->_shouldyFile1FittedDir = $config->shouldyFile1FittedDir;
    		$this->_shouldyFile1OriginalRequestDir = $config->shouldyFile1OriginalRequestDir;
    		$this->_shouldyFile1ThumbnailRequestDir = $config->shouldyFile1ThumbnailRequestDir;
    		$this->_shouldyFile1FittedRequestDir = $config->shouldyFile1FittedRequestDir;
    		$this->_shouldyFile2OriginalDir = $config->shouldyFile2OriginalDir;
    		$this->_shouldyFile2ThumbnailDir = $config->shouldyFile2ThumbnailDir;
    		$this->_shouldyFile2FittedDir = $config->shouldyFile2FittedDir;
    		$this->_shouldyFile2OriginalRequestDir = $config->shouldyFile2OriginalRequestDir;
    		$this->_shouldyFile2ThumbnailRequestDir = $config->shouldyFile2ThumbnailRequestDir;
    		$this->_shouldyFile2FittedRequestDir = $config->shouldyFile2FittedRequestDir;
    		
    		$this->_couldyFile1OriginalDir = $config->couldyFile1OriginalDir;
    		$this->_couldyFile1ThumbnailDir = $config->couldyFile1ThumbnailDir;
    		$this->_couldyFile1FittedDir = $config->couldyFile1FittedDir;
    		$this->_couldyFile1OriginalRequestDir = $config->couldyFile1OriginalRequestDir;
    		$this->_couldyFile1ThumbnailRequestDir = $config->couldyFile1ThumbnailRequestDir;
    		$this->_couldyFile1FittedRequestDir = $config->couldyFile1FittedRequestDir;
    		$this->_couldyFile2OriginalDir = $config->couldyFile2OriginalDir;
    		$this->_couldyFile2ThumbnailDir = $config->couldyFile2ThumbnailDir;
    		$this->_couldyFile2FittedDir = $config->couldyFile2FittedDir;
    		$this->_couldyFile2OriginalRequestDir = $config->couldyFile2OriginalRequestDir;
    		$this->_couldyFile2ThumbnailRequestDir = $config->couldyFile2ThumbnailRequestDir;
    		$this->_couldyFile2FittedRequestDir = $config->couldyFile2FittedRequestDir;
    		
    		$this->_ownerGroup = $config->ownerGroup;
    		$this->_authorizedGroups = $config->authorizedGroups->toArray();
		}
		else {
			require_once 'Sitengine/Proto/Exception.php';
        	throw new Sitengine_Proto_Exception('package config error');
       	}
    }
    
    
    public function getGoodiesTableName()
    {
    	return $this->_goodiesTableName;
    }
    
    
    public function getShouldiesTableName()
    {
    	return $this->_shouldiesTableName;
    }
    
    
    public function getCouldiesTableName()
    {
    	return $this->_couldiesTableName;
    }
    
    
    public function getGoodyTempDir()
    {
    	return $this->_goodyTempDir;
    }
    
    
    public function getShouldyTempDir()
    {
    	return $this->_shouldyTempDir;
    }
    
    
    public function getCouldyTempDir()
    {
    	return $this->_couldyTempDir;
    }
    
    
    public function tempDirPerUser()
    {
    	return $this->_tempDirPerUser;
    }
    
    
    public function getDownloadHandler()
    {
    	return $this->_downloadHandler;
    }
    
    
    
    
    
    public function getGoodyFile1OriginalDir() { return $this->_goodyFile1OriginalDir; }
    public function getGoodyFile1ThumbnailDir() { return $this->_goodyFile1ThumbnailDir; }
    public function getGoodyFile1FittedDir() { return $this->_goodyFile1FittedDir; }
    public function getGoodyFile1OriginalRequestDir() { return $this->_goodyFile1OriginalRequestDir; }
    public function getGoodyFile1ThumbnailRequestDir() { return $this->_goodyFile1ThumbnailRequestDir; }
    public function getGoodyFile1FittedRequestDir() { return $this->_goodyFile1FittedRequestDir; }
    public function getGoodyFile2OriginalDir() { return $this->_goodyFile2OriginalDir; }
    public function getGoodyFile2ThumbnailDir() { return $this->_goodyFile2ThumbnailDir; }
    public function getGoodyFile2FittedDir() { return $this->_goodyFile2FittedDir; }
    public function getGoodyFile2OriginalRequestDir() { return $this->_goodyFile2OriginalRequestDir; }
    public function getGoodyFile2ThumbnailRequestDir() { return $this->_goodyFile2ThumbnailRequestDir; }
    public function getGoodyFile2FittedRequestDir() { return $this->_goodyFile2FittedRequestDir; }
    
    public function getShouldyFile1OriginalDir() { return $this->_shouldyFile1OriginalDir; }
    public function getShouldyFile1ThumbnailDir() { return $this->_shouldyFile1ThumbnailDir; }
    public function getShouldyFile1FittedDir() { return $this->_shouldyFile1FittedDir; }
    public function getShouldyFile1OriginalRequestDir() { return $this->_shouldyFile1OriginalRequestDir; }
    public function getShouldyFile1ThumbnailRequestDir() { return $this->_shouldyFile1ThumbnailRequestDir; }
    public function getShouldyFile1FittedRequestDir() { return $this->_shouldyFile1FittedRequestDir; }
    public function getShouldyFile2OriginalDir() { return $this->_shouldyFile2OriginalDir; }
    public function getShouldyFile2ThumbnailDir() { return $this->_shouldyFile2ThumbnailDir; }
    public function getShouldyFile2FittedDir() { return $this->_shouldyFile2FittedDir; }
    public function getShouldyFile2OriginalRequestDir() { return $this->_shouldyFile2OriginalRequestDir; }
    public function getShouldyFile2ThumbnailRequestDir() { return $this->_shouldyFile2ThumbnailRequestDir; }
    public function getShouldyFile2FittedRequestDir() { return $this->_shouldyFile2FittedRequestDir; }
    
    public function getCouldyFile1OriginalDir() { return $this->_couldyFile1OriginalDir; }
    public function getCouldyFile1ThumbnailDir() { return $this->_couldyFile1ThumbnailDir; }
    public function getCouldyFile1FittedDir() { return $this->_couldyFile1FittedDir; }
    public function getCouldyFile1OriginalRequestDir() { return $this->_couldyFile1OriginalRequestDir; }
    public function getCouldyFile1ThumbnailRequestDir() { return $this->_couldyFile1ThumbnailRequestDir; }
    public function getCouldyFile1FittedRequestDir() { return $this->_couldyFile1FittedRequestDir; }
    public function getCouldyFile2OriginalDir() { return $this->_couldyFile2OriginalDir; }
    public function getCouldyFile2ThumbnailDir() { return $this->_couldyFile2ThumbnailDir; }
    public function getCouldyFile2FittedDir() { return $this->_couldyFile2FittedDir; }
    public function getCouldyFile2OriginalRequestDir() { return $this->_couldyFile2OriginalRequestDir; }
    public function getCouldyFile2ThumbnailRequestDir() { return $this->_couldyFile2ThumbnailRequestDir; }
    public function getCouldyFile2FittedRequestDir() { return $this->_couldyFile2FittedRequestDir; }
    
    public function getOwnerGroup() { return $this->_ownerGroup; }
    public function getAuthorizedGroups() { return $this->_authorizedGroups; }
    
    
    
    
    
    
    protected $_database = null;
    
    
    public function start(Zend_Db_Adapter_Abstract $database)
    {
    	$this->_database = $database;
    	return $this;
    }
    
    
    
    
    protected $_goodiesTable = null;
    
    public function getGoodiesTable()
    {
    	if($this->_goodiesTable === null)
    	{
    		if($this->_database === null)
    		{
    			$message = 'table init error - start() must be called first';
    			require_once 'Sitengine/Proto/Exception.php';
    			throw new Sitengine_Proto_Exception($message);
    		}
    		$this->_goodiesTable = $this->_getGoodiesTableInstance();
    	}
    	return $this->_goodiesTable;
    }
    
    
    
    protected function _getGoodiesTableInstance()
    {
    	require_once 'Sitengine/Proto/Goodies/Table.php';
		return new Sitengine_Proto_Goodies_Table(
			array(
				'db' => $this->_database,
				'rowClass' => 'Sitengine_Proto_Goodies_Row',
				'rowsetClass' => 'Sitengine_Proto_Goodies_Rowset',
				'protoPackage' => $this
			)
		);
    }
    
    
    
    
    
    
    protected $_shouldiesTable = null;
    
    public function getShouldiesTable()
    {
    	if($this->_shouldiesTable === null)
    	{
    		if($this->_database === null)
    		{
    			$message = 'table init error - start() must be called first';
    			require_once 'Sitengine/Proto/Exception.php';
    			throw new Sitengine_Proto_Exception($message);
    		}
    		$this->_shouldiesTable = $this->_getShouldiesTableInstance();
    	}
    	return $this->_shouldiesTable;
    }
    
    
    
    protected function _getShouldiesTableInstance()
    {
    	require_once 'Sitengine/Proto/Shouldies/Table.php';
		return new Sitengine_Proto_Shouldies_Table(
			array(
				'db' => $this->_database,
				'rowClass' => 'Sitengine_Proto_Shouldies_Row',
				'rowsetClass' => 'Sitengine_Proto_Shouldies_Rowset',
				'protoPackage' => $this
			)
		);
    }
    
    
    
    
    
    
    
    
    protected $_couldiesTable = null;
    
    public function getCouldiesTable()
    {
    	if($this->_couldiesTable === null)
    	{
    		if($this->_database === null)
    		{
    			$message = 'table init error - start() must be called first';
    			require_once 'Sitengine/Proto/Exception.php';
    			throw new Sitengine_Proto_Exception($message);
    		}
    		$this->_couldiesTable = $this->_getCouldiesTableInstance();
    	}
    	return $this->_couldiesTable;
    }
    
    
    
    protected function _getCouldiesTableInstance()
    {
    	require_once 'Sitengine/Proto/Couldies/Table.php';
		return new Sitengine_Proto_Couldies_Table(
			array(
				'db' => $this->_database,
				'rowClass' => 'Sitengine_Proto_Couldies_Row',
				'rowsetClass' => 'Sitengine_Proto_Couldies_Rowset',
				'protoPackage' => $this
			)
		);
    }
    
}
?>