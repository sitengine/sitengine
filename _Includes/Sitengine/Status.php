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


class Sitengine_Status
{
    
    
    /**
     * Singleton instance
     *
     * @var Sitengine_Status
     */
    protected static $_instance = null;
    protected $_code = null;
    protected $_message = null;
    protected $_isError = false;
    protected $_hints = array();
    

    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    private function __construct()
    {}

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    private function __clone()
    {}

    /**
     * Returns an instance of Sitengine_Status
     *
     * Singleton pattern implementation
     *
     * @return Sitengine_Status Provides a fluent interface
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    
    public function set($code, $message, $isError)
    {
        $this->_code = $code;
        $this->_message = $message;
        $this->_isError = $isError;
        return $this;
    }
    
    
    public function getCode()
    {
        return $this->_code;
    }
    
    
    public function getMessage()
    {
        return $this->_message;
    }
    
    
    public function isError()
    {
        return $this->_isError;
    }
    
    
    public function addHint($source, $messages = array(), $errors = array())
    {
        $this->_hints[$source] = array(
        	'messages' => (is_array($messages)) ? $messages : array($messages),
        	'errors' => (is_array($errors)) ? $errors : array($errors)
        );
        return $this;
    }
    
    
    public function getHints()
    {
        return $this->_hints;
    }
    
    
    public function hasHints()
    {
        return (sizeof($this->_hints) > 0);
    }
    
    
    public function hasHint($source)
    {
    	return isset($this->_hints[$source]);
    }
    
    
    public function clearHints()
    {
        $this->_hints = array();
        return $this;
    }
    
    
    public function getData()
    {
        return array(
            'code' => $this->_code,
            'message' => $this->_message,
            'isError' => $this->_isError,
            'HINTS' => $this->_hints
        );
    }
    
    
    public function save()
    {
    	require_once 'Zend/Session/Namespace.php';
    	$namespace = new Zend_Session_Namespace(get_class($this));
    	$namespace->{'code'} = $this->_code;
        $namespace->{'message'} = $this->_message;
        $namespace->{'isError'} = $this->_isError;
        $namespace->{'hints'} = $this->_hints;
        return $this;
    }
    
    
    public function restore()
    {
    	require_once 'Zend/Session/Namespace.php';
    	$namespace = new Zend_Session_Namespace(get_class($this));
    	
    	if(isset($namespace->{'code'})) {
    		$this->_code = $namespace->{'code'};
    		unset($namespace->{'code'});
    	}
    	if(isset($namespace->{'message'})) {
    		$this->_message = $namespace->{'message'};
    		unset($namespace->{'message'});
    	}
    	if(isset($namespace->{'isError'})) {
    		$this->_isError = $namespace->{'isError'};
    		unset($namespace->{'isError'});
    	}
    	if(isset($namespace->{'hints'})) {
    		$this->_hints = $namespace->{'hints'};
    		unset($namespace->{'hints'});
    	}
    	return $this;
    }
    
    
    public function reset()
    {
    	$this->_code = null;
    	$this->_message = null;
    	$this->_isError = false;
    	$this->_hints = array();
    	return $this;
    }
    
    
    public function dump()
    {
    	$line = "=====================================\n";
    	
    	print $line;
    	print ($this->isError()) ? 'Error: ' : '';
    	print $this->getMessage()."\n";
    	print $line;
    	
    	foreach($this->getHints() as $hint)
    	{
    		foreach($hint['messages'] as $message)
    		{
    			print $message."\n";
    		}
    	}
    	
    	if(sizeof($this->getHints())) { print $line; }
    }
    
}

?>