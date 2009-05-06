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


require_once 'Sitengine/Env.php';


class Sitengine_Env_Default extends Sitengine_Env
{
	
    protected $_request = null;
    protected $_response = null;
    protected $_logger = null;
    protected $_database = null;
    protected $_locale = null;
    protected $_namespace = null;
    protected $_sessionStarted = false;
    
    
    private function __construct() {}
    private function __clone() {}
    
    
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    
    public function getDatabase()
    {
    	if($this->_database === null)
		{
			require_once 'Sitengine/Env/Exception.php';
			throw new Sitengine_Env_Exception('startDatabase() must be called before using '.__METHOD__);
		}
		return $this->_database;
    }
    
    
    
    public function startDatabase($adapterName, array $config=array(), $debugControl=false)
    {
    	$this->getDatabaseInstance($adapterName, $config, $debugControl);
    }
    
    
    # deprecated
    public function getDatabaseInstance($adapterName, array $config=array(), $debugControl=false)
	{
		try {
			if($this->_database === null)
			{
				require_once 'Zend/Db.php';
				$this->_database = Zend_Db::factory($adapterName, $config);
				$this->_database->setFetchMode(Zend_Db::FETCH_ASSOC);
				if($debugControl) {
					$this->_database->getProfiler()->setEnabled(true);
				}
				$this->_database->query('SET NAMES "utf8"');
				$this->_databaseStarted = true;
				return $this->_database;
			}
			return $this->_database;
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Env/Exception.php';
			throw new Sitengine_Env_Exception('get database instance error', $exception);
		}
	}
    
    
    public function getLocaleInstance()
    {
    	if($this->_locale === null)
    	{
    		require_once 'Zend/Locale.php';
    		Zend_Locale::setDefault(self::LANGUAGE_EN);
    		$this->_locale = new Zend_Locale();
    	}
    	return $this->_locale;
    }
    
    
    public function getNamespaceInstance($name)
    {
    	if($this->_namespace === null)
    	{
    		require_once 'Zend/Session/Namespace.php';
    		$this->_namespace = new Zend_Session_Namespace($name);
    	}
    	return $this->_namespace;
    }
    
    
    public function startSession(Zend_Db_Adapter_Abstract $database, array $options = array())
    {
    	try {
    		if(!$this->_sessionStarted) {
    			$this->_sessionStarted = true;
				require_once 'Zend/Session.php';
				require_once 'Sitengine/Session/SaveHandler.php';
				Zend_Session::setSaveHandler(new Sitengine_Session_SaveHandler($database));
				Zend_Session::setOptions(array_merge(array('name' => self::PARAM_SESSIONID), $options));
				Zend_Session::start();
			}
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Env/Exception.php';
			throw new Sitengine_Env_Exception('start session error', $exception);
		}
    }
    
    
    
    public function getLogger()
    {
    	if($this->_logger === null)
		{
			require_once 'Sitengine/Env/Exception.php';
			throw new Sitengine_Env_Exception('startLogger() must be called before using '.__METHOD__);
		}
		return $this->_logger;
    }
    
    
    
    public function startLogger($logsDir, $filename = null, $logFilterPriority = null, $source = null)
    {
    	$this->getLoggerInstance($logsDir, $filename, $logFilterPriority, $source);
    }
    
    
	
	# deprecated
	public function getLoggerInstance($logsDir, $filename = null, $logFilterPriority = null, $source = null)
	{
		if($this->_logger === null)
		{
			$filename = ($filename !== null) ? $filename : gmdate('Ymd').'-sitengine.log';
			require_once 'Zend/Log.php';
			require_once 'Zend/Log/Writer/Stream.php';
			$writer = new Zend_Log_Writer_Stream($logsDir.'/'.$filename);
			$this->_logger = new Zend_Log($writer);
			
			if($logFilterPriority !== null)
			{
				$filter = new Zend_Log_Filter_Priority($logFilterPriority);
				$this->_logger->addFilter($filter);
			}
			
			if($source !== null)
			{
				$format = "%timestamp% %priorityName% %source%: %message%\n";
				$formatter = new Zend_Log_Formatter_Simple($format);
				$writer->setFormatter($formatter);
				$this->_logger->setEventItem('source', $source);
			}
			require_once 'Sitengine/Exception.php';
			Sitengine_Exception::addObserver(array($this, 'observeExceptions'));
		}
		return $this->_logger;
	}
	
	
	public function observeExceptions(Exception $exception)
	{
		if($this->_logger !== null)
		{
			$priority = ($exception instanceof Sitengine_Exception) ? $exception->getPriority() : Zend_Log::ERR;
			$priority = ($priority !== null) ? $priority : Zend_Log::ERR;
			$this->_logger->log($exception->getMessage(), $priority);
		}
	}
	
	
	
	
	protected $_amazon = array();
	
	
	public function addAmazonConfig($name, array $config)
	{
		$this->_amazon[$name] = $config;
		return $this;
	}
	
	public function getAmazonConfig($name, $throwException = true)
	{
		if(isset($this->_amazon[$name])) {
			return $this->_amazon[$name];
		}
		
		if($throwException)
		{
			$msg = 'amazon config "'.$name.'" has not been set';
			throw new Sitengine_Exception($msg);
		}
		return null;
	}
	
	
	
	
	
	
	
	protected $_googleAnalyticsTracker = null;
	
	
	public function setGoogleAnalyticsTracker($tracker)
	{
		$this->_googleAnalyticsTracker = $tracker;
		return $this;
	}
	
	public function getGoogleAnalyticsTracker()
	{
		return $this->_googleAnalyticsTracker;
	}
	
	
	
	
	
	
	
	
	protected $_paypal = '';
    
    public function setPaypalConfig(array $config)
    {
    	$this->_paypal = $config;
    	return $this;
    }
    
    
    public function getPaypalConfig()
    {
    	return $this->_paypal;
    }
	
}
?>