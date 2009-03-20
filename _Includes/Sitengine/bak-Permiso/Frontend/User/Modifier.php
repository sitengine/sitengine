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
	
	const FIELDS_NORMAL = 'normal';
    const FIELDS_ONOFF = 'onOff';
    
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
    
    
    
    
    public function getData()
    {
    	try {
			if($this->_started) { return $this->_data; }
			
			$this->_started = true;
			$id = $this->_controller->getPermiso()->getAuth()->getId();
			$q  = 'SELECT * FROM '.$this->_controller->getPermiso()->getUsersTableName();
			$q .= ' WHERE id = '.$this->_controller->getDatabase()->quote($id);
			#$q .= ' AND '.Sitengine_Permiso::FIELD_OID.' = "'.$this->_controller->getPermiso()->getOrganization()->getId().'"';
			$statement = $this->_controller->getDatabase()->prepare($q);
			$statement->execute();
			$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
			if(!sizeof($result)) {
				throw $this->_controller->getExceptionInstance('account not found');
			}
			$this->_data = $result[0];
			return $result[0];
		}
        catch (Exception $exception) {
            throw $this->_controller->getExceptionInstance('start entity error', $exception);
        }
    }
    
    
    
    
    protected function _getFields()
    {
    	$fieldsNormal = array(
            'name' => '',
            'firstname' => '',
            'lastname' => '',
            'nickname' => '',
            'password' => '',
            'timezone' => ''
        );
        
        $fieldsOnOff = array();
        
        return array(
        	self::FIELDS_NORMAL => $fieldsNormal,
        	self::FIELDS_ONOFF => $fieldsOnOff
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
            if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
                $message = $this->_controller->getTranslate()->translate('hintsPasswordRequired');
                $this->_controller->getStatus()->addHint($name, $message);
            }
            
            if(!$this->_checkInput()) { return null; }
            
            foreach($fields[self::FIELDS_ONOFF] as $k => $v) {
                $data[$k] = (array_key_exists($k, $input)) ? 1 : 0;
            }
            foreach($fields[self::FIELDS_NORMAL] as $k => $v) {
                $data[$k] = (array_key_exists($k, $input)) ? $input[$k] : $v;
            }
            $data['id'] = $id;
            $data['enabled'] = 1;
            #$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
            
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
            
            try {
            	$table = $this->_controller->getPermiso()->getUsersTableName();
				$affectedRows = $this->_controller->getDatabase()->insert($table, $data);
            	return ($affectedRows) ? $data : null;
            }
            catch(Exception $exception) {
            	if($this->_isExceptionCorrectable($exception)) { return null; }
            	else { throw $exception; }
            }
        }
        catch (Exception $exception) {
            throw $this->_controller->getExceptionInstance('insert error', $exception);
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
            	$id == Sitengine_Permiso::UID_ROOT ||
                $id == Sitengine_Permiso::UID_GUEST ||
                $id == Sitengine_Permiso::UID_LOSTFOUND
            ) {
                # root, lostfound and guest can't be changed
                $message = $this->_controller->getTranslate()->translate('hintsInvalidAction');
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            
            if(!$this->_checkInput()) { return null; }
            
            # sanitize data
            foreach($fields[self::FIELDS_ONOFF] as $k => $v) {
                $data[$k] = (array_key_exists($k, $input)) ? 1 : 0;
            }
            foreach($fields[self::FIELDS_NORMAL] as $k => $v) {
                if(array_key_exists($k, $input)) { $data[$k] = $input[$k]; }
            }
            # make names lowercase
            $data['name'] = mb_strtolower($data['name']);
            
            require_once 'Zend/Date.php';
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['mdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            
            if($data['password'] != '') {
                $data['password'] = md5($data['password']);
            }
            else { unset($data['password']); }
            
            try {
            	$where = 'id = '.$this->_controller->getDatabase()->quote($id);
            	$table = $this->_controller->getPermiso()->getUsersTableName();
				$affectedRows = $this->_controller->getDatabase()->update($table, $data, $where);
            	return ($affectedRows) ? $data : null;
            }
            catch(Exception $exception) {
            	if($this->_isExceptionCorrectable($exception)) { return null; }
            	else { throw $exception; }
            }
        }
        catch (Exception $exception) {
            throw $this->_controller->getExceptionInstance('update error', $exception);
        }
    }
    
    
    
    protected function _isExceptionCorrectable(Zend_Exception $exception)
    {
    	if(preg_match('/Duplicate entry.*for key (3|\'name\')/i', $exception->getMessage())) {
    		$message = $this->_controller->getTranslate()->translate('hintsNameExists');
    		$this->_controller->getStatus()->addHint('record', $message);
            return true;
    	}
    	if(preg_match('/Duplicate entry.*for key (2|\'nickname\')/i', $exception->getMessage())) {
    		$message = $this->_controller->getTranslate()->translate('hintsNicknameExists');
    		$this->_controller->getStatus()->addHint('record', $message);
            return true;
    	}
    	return false;
    }
    
    
    
    
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
		return (!$this->_controller->getStatus()->hasHints());
    }
    
}


?>