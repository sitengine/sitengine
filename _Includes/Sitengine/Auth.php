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


require_once 'Zend/Auth.php';


class Sitengine_Auth extends Zend_Auth
{
	
	
	protected $_lifetime = 3600;
	
	
	public static function getInstance()
    {
        if(null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    
    
    public function authenticate(Zend_Auth_Adapter_Interface $adapter)
    {
        $result = $adapter->authenticate();
		
        if($result->isValid())
        {
        	require_once 'Zend/Session.php';
        	Zend_Session::regenerateId();
        	$user = $adapter->getResultRowObject();
            $this->getStorage()->write($user->name);
            $this->getStorage()->writeData($user);
            $this->getStorage()->writeExpires(time() + $this->_lifetime);
            $this->getStorage()->writeIpAddress($_SERVER['REMOTE_ADDR']);
            
            require_once 'Zend/Date.php';
        	$date = new Zend_Date();
			$date->setTimezone('UTC');
			$now = $date->get(Zend_Date::ISO_8601);
			
            $data = array(
            	'lastLogin' => $now,
                'lastRequest' => $now
            );
            
            $adapter->update($user->id, $data);
        }

        return $result;
    }
    
    
    
    public function reauthenticate(Zend_Auth_Adapter_Interface $adapter, $id)
    {
        $result = $adapter->reauthenticate($id);
		
        if($result->isValid())
        {
        	require_once 'Zend/Session.php';
        	Zend_Session::regenerateId();
        	$user = $adapter->getResultRowObject();
            $this->getStorage()->write($user->name);
            $this->getStorage()->writeData($user);
            $this->getStorage()->writeExpires(time() + $this->_lifetime);
            $this->getStorage()->writeIpAddress($_SERVER['REMOTE_ADDR']);
            
            require_once 'Zend/Date.php';
        	$date = new Zend_Date();
			$date->setTimezone('UTC');
			$now = $date->get(Zend_Date::ISO_8601);
			
            $data = array(
            	'lastLogin' => $now,
                'lastRequest' => $now
            );
            
            $adapter->update($user->id, $data);
        }

        return $result;
    }
    
    
    
    public function extendValidity()
    {
        if($this->_checkIdentity())
    	{
            $this->getStorage()->writeExpires(time() + $this->_lifetime);
        }
    }
    
    
    
    public function hasIdentity()
    {
    	if($this->_checkIdentity())
    	{
        	return !$this->getStorage()->isEmpty();
        }
        return false;
    }
    
    
    
    public function getData()
    {
		if($this->getStorage()->isEmpty())
		{
			return new stdClass();;
		}

		return $this->getStorage()->getData();
    }
    
    
    
    public function getId()
    {
		if($this->getStorage()->isEmpty())
		{
			return null;
		}

		return $this->getStorage()->getData()->id;
    }
    
    
    
    public function getFirstname()
    {
		if($this->getStorage()->isEmpty())
		{
			return null;
		}

		return $this->getStorage()->getData()->firstname;
    }
    
    
    
    public function getLastname()
    {
		if($this->getStorage()->isEmpty())
		{
			return null;
		}

		return $this->getStorage()->getData()->lastname;
    }
    
    
    
    public function getNickname()
    {
		if($this->getStorage()->isEmpty())
		{
			return null;
		}

		return $this->getStorage()->getData()->nickname;
    }
    
    
    
    public function getLastLogin()
    {
		if($this->getStorage()->isEmpty())
		{
			return null;
		}

		return $this->getStorage()->getData()->lastLogin;
    }
    
    
    
    protected function _checkIdentity()
    {
		if($this->getStorage()->isEmpty())
		{
			$this->getStorage()->clear();
			return false;
		}
		
		if($this->getStorage()->getExpires() < time())
		{
			$this->getStorage()->clear();
			return false;
		}
		
		if($this->getStorage()->getIpAddress() != $_SERVER['REMOTE_ADDR'])
		{
			$this->getStorage()->clear();
			return false;
		}
		
    	return true;
    }
}

?>