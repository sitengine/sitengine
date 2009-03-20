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
 * @package    Sitengine_Permiso_Frontend_User
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */
 
 
class Sitengine_Permiso_Frontend_User_Modifier
{
	
	protected $_controller = null;
    protected $_started = false;
    protected $_data = null;
    
    
    public function __construct(
    	Sitengine_Permiso_Frontend_User_Controller $controller
    )
    {
        $this->_controller = $controller;
    }
    
    
    
    public function refreshData(array $updatedData)
    {
        $this->_data = array_merge($this->_data, $updatedData);
    }
    
    
    
    protected function _getFields()
    {
    	return array(
            'name' => '',
            'firstname' => '',
            'lastname' => '',
            'nickname' => '',
            'password' => '',
            'country' => '',
            'timezone' => '',
            'newsletter' => ''
        );
    }
    
    
    
    
    public function insert()
    {
        try {
        	require_once 'Sitengine/Validator.php';
        	
        	$fields = $this->_getFields();
        	$input = $this->_controller->getRequest()->getPost(null);
        	$id = Sitengine_String::createId();
            $data  = array();
            
            $name = 'password';
            if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name)))
            {
                $message = $this->_controller->getTranslate()->translate('hintsPasswordRequired');
                $this->_controller->getStatus()->addHint($name, $message);
            }
            
            if(!$this->_controller->getPermiso()->getUsersTable()->checkUserModifyData(
					$this->_controller->getStatus(),
					$this->_controller->getRequest(),
					$this->_controller->getTranslate()
				)
			)
            {
            	return null;
            }
            
            foreach($fields as $k => $v)
            {
                $data[$k] = (array_key_exists($k, $input)) ? $input[$k] : $v;
            }
            
            $data['id'] = $id;
            $data['enabled'] = 1;
            
            require_once 'Zend/Date.php';
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['cdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
			$data['mdate'] = $data['cdate'];
			
            # make names lowercase
            $data['name'] = mb_strtolower($data['name']);
            # encrypt password
            $data['password'] = md5($data['password']);
            #Sitengine_Debug::print_r($data);
            
            $insertId = $this->_controller->getFrontController()->getPermisoPackage()->getUsersTable()->insert($data);
            
            if(!$insertId)
            {
            	$error = $this->_controller->getFrontController()->getPermisoPackage()->getUsersTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Permiso/Frontend/User/Exception.php';
            throw new Sitengine_Permiso_Frontend_User_Exception('insert error', $exception);
        }
    }
    
    
    
    
    public function update()
    {
        try {
        	$id = $this->_controller->getPermiso()->getAuth()->getId();
            $fields = $this->_getFields();
            $input = $this->_controller->getRequest()->getPost(null);
            $data = array();
            
            if(
            	#$id == Sitengine_Permiso::UID_ROOT ||
                $id == Sitengine_Permiso::UID_GUEST ||
                $id == Sitengine_Permiso::UID_LOSTFOUND
            ) {
                # root, lostfound and guest can't be changed
                $message = $this->_controller->getTranslate()->translate('hintsInvalidAction');
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            
            if(!$this->_controller->getPermiso()->getUsersTable()->checkUserModifyData(
					$this->_controller->getStatus(),
					$this->_controller->getRequest(),
					$this->_controller->getTranslate()
				)
			)
            {
            	return null;
            }
            
            foreach($fields as $k => $v) {
                if(array_key_exists($k, $input)) { $data[$k] = $input[$k]; }
            }
            # make names lowercase
            $data['name'] = mb_strtolower($data['name']);
            
            require_once 'Zend/Date.php';
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['mdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            
            if($data['password'] != '')
            {
                $data['password'] = md5($data['password']);
            }
            else { unset($data['password']); }
            
            
            $where = $this->_controller->getDatabase()->quoteInto('id = ?', $id);
            $affectedRows = $this->_controller->getFrontController()->getPermisoPackage()->getUsersTable()->update($data, $where);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getPermisoPackage()->getUsersTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Permiso/Frontend/User/Exception.php';
            throw new Sitengine_Permiso_Frontend_User_Exception('update error', $exception);
        }
    }
    
    
    
    /*
    protected function _checkInput()
    {
    	require_once 'Sitengine/Validator.php';
    	
		$name = 'name';
		$val = $this->_controller->getRequest()->getPost($name);
		if(Sitengine_Validator::nada($val)) {
			$message = $this->_controller->getTranslate()->translate('hintsNameRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		else if(!Sitengine_Validator::emailAddress($val)) {
			$message = $this->_controller->getTranslate()->translate('hintsNameValidEmailRequired');
			$this->_controller->getStatus()->addHint($name, $message);
			return false;
		}
		$name = 'firstname';
		$val = $this->_controller->getRequest()->getPost($name);
		if(Sitengine_Validator::nada($val)) {
			$message = $this->_controller->getTranslate()->translate('hintsFirstnameRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		$name = 'lastname';
		$val = $this->_controller->getRequest()->getPost($name);
		if(Sitengine_Validator::nada($val)) {
			$message = $this->_controller->getTranslate()->translate('hintsLastnameRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		$name = 'nickname';
		$val = $this->_controller->getRequest()->getPost($name);
		if(Sitengine_Validator::nada($val)) {
			$message = $this->_controller->getTranslate()->translate('hintsNicknameRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		$name = 'password';
		$val = $this->_controller->getRequest()->getPost($name);
		# passwords must be made up of a-zA-Z0-9
		if(!Sitengine_Validator::nada($val) && !Sitengine_Validator::word($val)) {
			$message = $this->_controller->getTranslate()->translate('hintsPasswordWordCharsOnly');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		if($val!=$this->_controller->getRequest()->getPost('passwordConfirm')) {
			$message = $this->_controller->getTranslate()->translate('hintsPasswordsDontMatch');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		
		if($val != '')
		{
			require_once 'Zend/Validate/StringLength.php';
			$validator = new Zend_Validate_StringLength(
				$this->_controller->getPermiso()->getMinimalPasswordLength()
			);
			
			$validator->setMessage(
				$this->_controller->getTranslate()->translate('hintsPasswordTooShort'),
				Zend_Validate_StringLength::TOO_SHORT)
			;
			
			if(!$validator->isValid($val))
			{
				$messages = $validator->getMessages();
				$this->_controller->getStatus()->addHint($name, $messages);
			}
		}
		
		$name = 'country';
		if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name), 'noneSelected')) {
			$message = $this->_controller->getTranslate()->translate('hintsCountryRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		
		return (!$this->_controller->getStatus()->hasHints());
    }
    */
}


?>