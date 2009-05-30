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


class Sitengine_Permiso_Model_Account
{

	const STATUS_CREATE_ERROR = 'accountStatusCreateError';
	const STATUS_CREATE_OK = 'accountStatusCreateOk';
	const STATUS_UPDATE_ERROR = 'accountStatusUpdateError';
	const STATUS_UPDATE_OK = 'accountStatusUpdateOk';
	
	const HINT_NAME_EXISTS = 'accountHintNameExists';
	const HINT_NICKNAME_EXISTS = 'accountHintNicknameExists';
	const HINT_UNKNOWN_ERROR = 'accountHintUnknownError';
	
	
	protected $_permiso = null;
	protected $_userRow = null;
    protected $_started = false;
    protected $_form = null;
    protected $_loginForm = null;
    
    
    # singleton implementation that allows parent class to not be a singleton
	protected static $_instance = null;
	protected static $_singletoned = false;
	
	
	
	public function __construct(Sitengine_Permiso $permiso)
    {
    	if(!self::$_singletoned)
    	{
    		require_once 'Kompakt/Shop/Exception.php';
			throw new Kompakt_Shop_Exception('singleton - no direct instantiation');
    	}
    	
    	$this->_permiso = $permiso;
    }
    
    
    
    public function __clone()
    {
    	require_once 'Kompakt/Shop/Exception.php';
		throw new Kompakt_Shop_Exception('singleton - no cloning');
    }
    
    
    
    public static function getInstance(Sitengine_Permiso $permiso)
    {
        if(self::$_instance === null)
        {
        	self::$_singletoned = true;
            self::$_instance = new self($permiso);
        }
        return self::$_instance;
    }
    
    
    
    public function startById($id)
    {
    	if(!$this->_started)
    	{
    		$this->_started = true;
    		$this->_loadById($id);
    	}
    	return $this;
    }
    
    
    
    protected function _loadById($id)
    {
    	if(!$this->isLoaded() && $id)
		{
			$usersTable = $this->getPermiso()->getUsersTable();
			$this->_userRow = $usersTable->fetchRow($usersTable->select()->where("id = ?", $id));
		}
		return $this->isLoaded();
    }
    
    
    
    public function isLoaded()
    {
    	return ($this->_getUserRow() instanceof Sitengine_Permiso_Db_Users_Row);
    }
    
    
    
    public function getPermiso()
    {
    	return $this->_permiso;
    }
    
    
    
    public function setTranslator(Zend_Translate $translator)
    {
    	$this->_translator = $translator;
    	return $this;
    }
    
    
	
	protected function _getTranslator()
	{
		return $this->_translator;
	}
    
    
    
    protected function _getUserRow()
    {
		return $this->_userRow;
    }
    
    
    
    
    public function getForm()
    {
    	if($this->_form === null)
    	{
			$options = array(
				'permiso' => $this->getPermiso()
			);
			require_once 'Sitengine/Permiso/Model/Account/Form.php';
			$this->_form = new Sitengine_Permiso_Model_Account_Form($options);
		}
		return $this->_form;
    }
    
    
    
    
    public function getLoginForm()
    {
    	if($this->_loginForm === null)
    	{
			$options = array(
				'permiso' => $this->getPermiso()
			);
			require_once 'Sitengine/Permiso/Model/Account/LoginForm.php';
			$this->_loginForm = new Sitengine_Permiso_Model_Account_LoginForm($options);
		}
		return $this->_loginForm;
    }
    
    
    
    
    public function login(Sitengine_Permiso_Model_Account_LoginForm $loginForm)
    {
    	$loginForm->start();
    	$loginForm->isValid($_POST);
    	$name = $loginForm->getNameVal();
    	$password = $this->_makePasswordHash($loginForm->getPasswordVal());
    	
    	if($name && $password)
    	{
			$this->getPermiso()->getAuthAdapter()->setIdentity($name)->setCredential($password);
			$result = $this->getPermiso()->getAuth()->authenticate($this->getPermiso()->getAuthAdapter());
			return $this->getPermiso()->getAuth()->hasIdentity();
		}
		
		return false;
    }
    
    
    
    
    public function create(Sitengine_Permiso_Model_Account_Form $form)
    {
    	require_once 'Sitengine/Status.php';
		$status = Sitengine_Status::getInstance();
		
    	if(!$this->_save($form))
    	{
    		$status->set(
        		self::STATUS_CREATE_ERROR,
        		$this->_getTranslator()->translate(self::STATUS_CREATE_ERROR),
        		true
        	);
        	$status->save();
			return false;
    	}
    	
    	$status->set(
			self::STATUS_CREATE_OK,
			$this->_getTranslator()->translate(self::STATUS_CREATE_OK),
			false
		);
		
		$status->save();
    	return true;
    }
    
    
    
    
    public function update(Sitengine_Permiso_Model_Account_Form $form)
    {
    	require_once 'Sitengine/Status.php';
		$status = Sitengine_Status::getInstance();
		
    	if(!$this->_save($form))
    	{
    		$status->set(
        		self::STATUS_UPDATE_ERROR,
        		$this->_getTranslator()->translate(self::STATUS_UPDATE_ERROR),
        		true
        	);
        	$status->save();
			return false;
    	}
    	
    	$status->set(
			self::STATUS_UPDATE_OK,
			$this->_getTranslator()->translate(self::STATUS_UPDATE_OK),
			false
		);
		
		$status->save();
    	return true;
    }
    
    
    
    
    protected function _save(Sitengine_Permiso_Model_Account_Form $form)
    {
    	require_once 'Sitengine/Status.php';
		$status = Sitengine_Status::getInstance();
		
    	if(!$form->isValid($_POST))
    	{
    		foreach($form->getMessages() as $key => $messages)
			{
				$status->addHint($key, $messages);
			}
			return false;
    	}
    	
    	if($this->_getUserRow() === null)
    	{
    		$this->_userRow = $this->getPermiso()->getUsersTable()->createRow($form->getValues());
    		$this->_getUserRow()->setId(Sitengine_String::createId());
    		$this->_getUserRow()->setCdate($this->getPermiso()->getUsersTable()->getNow());
    		$this->_getUserRow()->setPassword($this->_makePasswordHash($form->getPasswordVal()));
    	}
    	else {
    		$password
    			= ($form->getPasswordVal())
    			? $this->_makePasswordHash($form->getPasswordVal())
    			: $this->_getUserRow()->getPassword()
    		;
    		
    		$this->_getUserRow()->setFromArray($form->getValues());
    		$this->_getUserRow()->setPassword($password);
    	}
    	
    	try{
    		$this->_getUserRow()->setMdate($this->getPermiso()->getUsersTable()->getNow());
    		$this->_getUserRow()->setEnabled(1); # TODO - a better way for use in other contexts
    		$this->_getUserRow()->save();
    	}
    	catch(Exception $exception)
    	{
    		if($this->getPermiso()->getUsersTable()->checkModifyException($exception))
    		{
    			throw $exception;
    		}
    		
    		switch($this->getPermiso()->getUsersTable()->getError())
    		{
    			case 'nameExists': $hint = self::HINT_NAME_EXISTS; break;
    			case 'nicknameExists': $hint = self::HINT_NICKNAME_EXISTS; break;
    			default: $hint = self::HINT_UNKNOWN_ERROR; break;
    		}
    		
            $message = $this->_getTranslator()->translate($hint);
    		$status->addHint($hint, $message);
    		return false;
    	}
    	return true;
    }
    
    
    
    
    protected function _makePasswordHash($s)
    {
    	return md5($s);
    }
    
	
	
	public function __call($method, array $args)
    {
    	if(!$this->isLoaded())
    	{
    		require_once 'Kompakt/Shop/Exception.php';
    		throw new Kompakt_Shop_Exception("account must load successfully before __call() can handle $method()");
    	}
    	
        if(preg_match('/^get(\w*)/', $method, $matches))
        {
        	// forward simple getters to user row
			return $this->_getUserRow()->__call($method, $args);
        }
        require_once 'Kompakt/Shop/Exception.php';
    	throw new Kompakt_Shop_Exception("Unrecognized method '$method()'");
    }
    
}


?>