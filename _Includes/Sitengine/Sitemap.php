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
 * @package    Sitengine_Sitemap
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


abstract class Sitengine_Sitemap
{
	
	const ITEMTYPE_FILE = 'file';
    const ITEMTYPE_PAGE = 'page';
    const ITEMTYPE_MASK = 'mask';
    const ITEMTYPE_LAYER = 'layer';
    const ITEMTYPE_SNIPPET = 'snippet';
    
    
	protected $_env = null;
    protected $_request = null;
    protected $_response = null;
    protected $_config = null;
    
    
    # properties loaded from config
    protected $_tableSitemap = null;
    protected $_file1OriginalDir = null;
    protected $_file1ThumbnailDir = null;
    protected $_file1OriginalRequestDir = null;
    protected $_file1ThumbnailRequestDir = null;
    
    
    public function getFile1OriginalDir() { return $this->_file1OriginalDir; }
    public function getFile1ThumbnailDir() { return $this->_file1ThumbnailDir; }
    public function getFile1OriginalRequestDir() { return $this->_file1OriginalRequestDir; }
    public function getFile1ThumbnailRequestDir() { return $this->_file1ThumbnailRequestDir; }
    
    
    public function __construct(
    	Sitengine_Env_Default $env,
    	Sitengine_Controller_Request_Http $request,
    	Zend_Controller_Response_Http $response,
    	Zend_Config $config
    )
    {
		$this->_env = $env;
		#$this->_request = $request;
		#$this->_response = $response;
		$this->_config = $config;
		$this->_mapConfig($config);
    }
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	if(
			isset($config->tableSitemap) &&
			isset($config->file1OriginalDir) &&
			isset($config->file1ThumbnailDir) &&
			isset($config->file1OriginalRequestDir) &&
			isset($config->file1ThumbnailRequestDir)
		)
		{
			$this->_tableSitemap = $config->tableSitemap;
			$this->_file1OriginalDir = $config->file1OriginalDir;
			$this->_file1ThumbnailDir = $config->file1ThumbnailDir;
			$this->_file1OriginalRequestDir = $config->file1OriginalRequestDir;
			$this->_file1ThumbnailRequestDir = $config->file1ThumbnailRequestDir;
		}
		else {
			require_once 'Sitengine/Sitemap/Exception.php';
			throw new Sitengine_Sitemap_Exception('package config error');
		}
    }
    
    
    public function getTableSitemap()
    {
    	return $this->_tableSitemap;
    }
    
}
?>