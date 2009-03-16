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
 * @package    Sitengine_Newsletter
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


abstract class Sitengine_Newsletter
{
    
    protected $_env = null;
    protected $_request = null;
    protected $_response = null;
    protected $_config = null;
    
    
    # properties loaded from config
    protected $_campaignsTableName = null;
    protected $_attachmentsTableName = null;
    #protected $_attachmentTempDir = null;
    #protected $_tempDirPerUser = true;
    
    #protected $_attachmentFile1OriginalDir = null;
    #protected $_attachmentFile1OriginalRequestDir = null;
    protected $_attachmentFile1OriginalPrefix = null;
    protected $_attachmentFile1OriginalAmzHeaders = null;
    protected $_attachmentFile1OriginalSsl = null;
    
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
    
    
    
    public function getEnv()
    {
    	return $this->_env;
    }
    
    
    
    public function getRequest()
    {
    	return $this->_request;
    }
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	if(
			isset($config->campaignsTableName) &&
			isset($config->attachmentsTableName) &&
			#isset($config->attachmentTempDir) &&
			#isset($config->tempDirPerUser) &&
    		
    		#isset($config->attachmentFile1OriginalDir) &&
    		#isset($config->attachmentFile1OriginalRequestDir) &&
    		isset($config->attachmentFile1OriginalPrefix) &&
    		isset($config->attachmentFile1OriginalAmzHeaders) &&
    		isset($config->attachmentFile1OriginalSsl) &&
    		
    		isset($config->ownerGroup) &&
    		isset($config->authorizedGroups)
		)
		{
			$this->_campaignsTableName = $config->campaignsTableName;
			$this->_attachmentsTableName = $config->attachmentsTableName;
			#$this->_attachmentTempDir = $config->attachmentTempDir;
			#$this->_tempDirPerUser = $config->tempDirPerUser;
    		
    		#$this->_attachmentFile1OriginalDir = $config->attachmentFile1OriginalDir;
    		#$this->_attachmentFile1OriginalRequestDir = $config->attachmentFile1OriginalRequestDir;
    		$this->_attachmentFile1OriginalPrefix = $config->attachmentFile1OriginalPrefix;
    		$this->_attachmentFile1OriginalAmzHeaders = $config->attachmentFile1OriginalAmzHeaders->toArray();
    		$this->_attachmentFile1OriginalSsl = $config->attachmentFile1OriginalSsl;
    		
    		$this->_ownerGroup = $config->ownerGroup;
    		$this->_authorizedGroups = $config->authorizedGroups->toArray();
		}
		else {
			require_once 'Sitengine/Newsletter/Exception.php';
        	throw new Sitengine_Newsletter_Exception('package config error');
       	}
    }
    
    
    public function getCampaignsTableName()
    {
    	return $this->_campaignsTableName;
    }
    
    
    public function getAttachmentsTableName()
    {
    	return $this->_attachmentsTableName;
    }
    
    /*
    public function getAttachmentTempDir()
    {
    	return $this->_attachmentTempDir;
    }
    
    
    public function tempDirPerUser()
    {
    	return $this->_tempDirPerUser;
    }
    */
    #public function getAttachmentFile1OriginalDir() { return $this->_attachmentFile1OriginalDir; }
    #public function getAttachmentFile1OriginalRequestDir() { return $this->_attachmentFile1OriginalRequestDir; }
    public function getAttachmentFile1OriginalPrefix() { return $this->_attachmentFile1OriginalPrefix; }
    public function getAttachmentFile1OriginalAmzHeaders() { return $this->_attachmentFile1OriginalAmzHeaders; }
    public function getAttachmentFile1OriginalSsl() { return $this->_attachmentFile1OriginalSsl; }
    
    public function getOwnerGroup() { return $this->_ownerGroup; }
    public function getAuthorizedGroups() { return $this->_authorizedGroups; }
    
    
    
    
    
    
    protected $_database = null;
    
    
    public function start(Zend_Db_Adapter_Abstract $database)
    {
    	$this->_database = $database;
    }
    
    
    
    
    protected $_campaignsTable = null;
    
    public function getCampaignsTable()
    {
    	if($this->_campaignsTable === null)
    	{
    		if($this->_database === null)
    		{
    			$message = 'table init error - start() must be called first';
    			require_once 'Sitengine/Newsletter/Exception.php';
    			throw new Sitengine_Newsletter_Exception($message);
    		}
    		$this->_campaignsTable = $this->_getCampaignsTableInstance();
    	}
    	return $this->_campaignsTable;
    }
    
    
    
    protected function _getCampaignsTableInstance()
    {
    	require_once 'Sitengine/Newsletter/Campaigns/Table.php';
		return new Sitengine_Newsletter_Campaigns_Table(
			array(
				'db' => $this->_database,
				'rowClass' => 'Sitengine_Newsletter_Campaigns_Row',
				'rowsetClass' => 'Sitengine_Newsletter_Campaigns_Rowset',
				'newsletterPackage' => $this
			)
		);
    }
    
    
    
    
    
    
    protected $_attachmentsTable = null;
    
    public function getAttachmentsTable()
    {
    	if($this->_attachmentsTable === null)
    	{
    		if($this->_database === null)
    		{
    			$message = 'table init error - start() must be called first';
    			require_once 'Sitengine/Newsletter/Exception.php';
    			throw new Sitengine_Newsletter_Exception($message);
    		}
    		$this->_attachmentsTable = $this->_getAttachmentsTableInstance();
    	}
    	return $this->_attachmentsTable;
    }
    
    
    
    protected function _getAttachmentsTableInstance()
    {
    	require_once 'Sitengine/Newsletter/Attachments/Table.php';
		return new Sitengine_Newsletter_Attachments_Table(
			array(
				'db' => $this->_database,
				'rowClass' => 'Sitengine_Newsletter_Attachments_Row',
				'rowsetClass' => 'Sitengine_Newsletter_Attachments_Rowset',
				'newsletterPackage' => $this
			)
		);
    }
    
}
?>