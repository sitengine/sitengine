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


require_once 'Sitengine/Validator.php';


abstract class Sitengine_Permiso_Backend_Users_Modifier
{
    
    protected $_controller = null;
    
    
    public function __construct(Sitengine_Permiso_Backend_Users_Controller $controller)
    {
        $this->_controller = $controller;
    }
    
    
    
    protected function _getFields()
    {
        return array(
			'mdate' => '',
			'locked' => '',
			'enabled' => '',
			'language' => '',
			'timezone' => '',
			'name' => '',
			'nickname' => '',
			'firstname' => '',
			'lastname' => '',
			'country' => '',
			'description' => '',
			'password' => '',
			'newsletter' => ''
        );
    }
    
    
    
    public function insert()
    {
        try {
        	$fields = $this->_getFields();
        	$input = $this->_controller->getRequest()->getPost(null);
        	
        	require_once 'Sitengine/String.php';
        	$id = Sitengine_String::createId();
            $data  = array();
            
            $notifyNewUser = $this->_controller->getRequest()->getPost('notifyNewUser');
            
            if($notifyNewUser)
            {
            	$password = Sitengine_String::createId(8);
            }
            else {
				$name = 'password';
				$password = $this->_controller->getRequest()->getPost($name);
				if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name)))
				{
					$message = $this->_controller->getTranslate()->translate('hintsPasswordRequired');
					$this->_controller->getStatus()->addHint($name, $message);
				}
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
            
            $this->_controller->getFrontController()->getPermiso()->getUsersTable()->handleInsertUploads($id);
            
            foreach($fields as $k => $v)
            {
                $data[$k] = (array_key_exists($k, $input)) ? $input[$k] : $v;
            }
            
            require_once 'Zend/Date.php';
            $date = new Zend_Date();
            $date->setTimezone('UTC');
            $data['cdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            $data['mdate'] = $data['cdate'];
            $data['id'] = $id;
            
            # make names lowercase
            $data['name'] = mb_strtolower($data['name']);
            # encrypt password
            $data['password'] = md5($password);
            
            $data = array_merge($data, $this->_controller->getFrontController()->getPermiso()->getUsersTable()->getFileData());
            #Sitengine_Debug::print_r($data);
            $insertId = $this->_controller->getFrontController()->getPermiso()->getUsersTable()->insertOrRollback($data);
            if(!$insertId)
            {
            	$error = $this->_controller->getFrontController()->getPermiso()->getUsersTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            
            if($notifyNewUser)
            {
				$this->_sendNotifyNewUserMail($data, $password);
			}
			return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Permiso/Backend/Users/Exception.php';
            throw new Sitengine_Permiso_Backend_Users_Exception('insert error', $exception);
        }
    }
    
    
    
    protected function _sendNotifyNewUserMail($data, $password)
    {
		$subject = $this->_controller->getTranslate()->translate('notifynewusermailSubject');
		$subject = preg_replace('/%site%/', $_SERVER['SERVER_NAME'], $subject);
		$find = array('/%site%/', '/%user%/', '/%password%/');
		$repl = array($_SERVER['SERVER_NAME'], $data['name'], $password);
		$body = $this->_controller->getTranslate()->translate('notifynewusermailBody');
		$body = preg_replace($find, $repl, $body);
		
		require_once 'Zend/Mail.php';
		$msg = new Zend_Mail();
		
		if(
			$this->_controller->getEnv()->getModeratorSenderMail() === null ||
			sizeof($this->_controller->getEnv()->getModeratorMails()) == 0
		)
		{
			require_once 'Sitengine/Permiso/Backend/Users/Exception.php';
			throw new Sitengine_Permiso_Backend_Users_Exception('moderator sender/recipients not set in config');
		}
		$msg
			->setSubject($subject)
			->setBodyText($body)
			->setFrom($this->_controller->getEnv()->getModeratorSenderMail(), $_SERVER['SERVER_NAME'])
			->addTo($data['name'])
			->send()
		;
    }
    
    
    
    
    
    
    public function update()
    {
        try {
        	$id = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ID);
            $stored = $this->_controller->getEntity()->getRow()->toArray();
        	$fields = $this->_getFields();
            $input = $this->_controller->getRequest()->getPost(null);
            $data = array();
            
            if(
                $id == Sitengine_Permiso::UID_ROOT &&
                !$this->_controller->getPermiso()->getAuth()->getId() == Sitengine_Permiso::UID_ROOT
            ) {
                # only user root can change the root account
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            
            if(
                $id == Sitengine_Permiso::UID_GUEST ||
                $id == Sitengine_Permiso::UID_LOSTFOUND
            ) {
                # lostfound and guest can't be changed
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            
            if(
                $this->_controller->getPermiso()->getDirectory()->userIsMember($id, Sitengine_Permiso::GID_ADMINISTRATORS) &&
                !$this->_controller->getPermiso()->getDirectory()->userIsMember($this->_controller->getPermiso()->getAuth()->getId(), Sitengine_Permiso::GID_ADMINISTRATORS)
            ) {
                # only administrators can update users that are administrators members
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            
            if($this->_controller->getRequest()->getPost('mdate') != $stored['mdate']) {
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_DATA_EXPIRED);
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
            
            $this->_controller->getFrontController()->getPermiso()->getUsersTable()->handleUpdateUploads($id, $stored);
            
            foreach($fields as $k => $v)
            {
                if(array_key_exists($k, $input)) { $data[$k] = $input[$k]; }
            }
            
            require_once 'Zend/Date.php';
            $date = new Zend_Date();
            $date->setTimezone('UTC');
            $data['mdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            $data = array_merge($data, $this->_controller->getFrontController()->getPermiso()->getUsersTable()->getFileData());
            unset($data['id']);
            
            $data['name'] = mb_strtolower($data['name']);
            
            if($data['password'] != '')
            {
                $data['password'] = md5($data['password']);
            }
            else { unset($data['password']); }
            
            if(
        		$id == Sitengine_Permiso::UID_ROOT ||
        		$id == Sitengine_Permiso::UID_GUEST ||
        		$id == Sitengine_Permiso::UID_LOSTFOUND
        	)
        	{
        		$data['enabled'] = 1;
        		$data['locked'] = 1;
        	}
        	
            #Sitengine_Debug::print_r($data);
    		$where = $this->_controller->getDatabase()->quoteInto('id = ?', $id);
            $affectedRows = $this->_controller->getFrontController()->getPermiso()->getUsersTable()->updateOrRollback($data, $where);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getPermiso()->getUsersTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Permiso/Backend/Users/Exception.php';
            throw new Sitengine_Permiso_Backend_Users_Exception('update error', $exception);
        }
    }
    
    
    
    
    /*
    protected function _checkInput()
    {
		$name = 'name';
		$val = $this->_controller->getRequest()->getPost($name);
		if(Sitengine_Validator::nada($val)) {
			$message = $this->_controller->getTranslate()->translate('hintsNameRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		else if(!Sitengine_Validator::emailAddress($val)) {
			$message = $this->_controller->getTranslate()->translate('hintsNameValidEmailRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		
		$name = 'nickname';
		$val = $this->_controller->getRequest()->getPost($name);
		if(Sitengine_Validator::nada($val)) {
			$message = $this->_controller->getTranslate()->translate('hintsNicknameRequired');
			$this->_controller->getStatus()->addHint($name, $message);
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
		
		$name = 'password';
		$val = $this->_controller->getRequest()->getPost($name);
		if($val != $this->_controller->getRequest()->getPost('passwordConfirm')) {
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
		if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name), Sitengine_Permiso_Backend_Users_Controller::VALUE_NONESELECTED)) {
			$message = $this->_controller->getTranslate()->translate('hintsCountryRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		
		
		$name = 'timezone';
		if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name), Sitengine_Permiso_Backend_Users_Controller::VALUE_NONESELECTED)) {
			$message = $this->_controller->getTranslate()->translate('hintsTimezoneRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
	
		$fileId = 'avatarOriginal';
		
		require_once 'Sitengine/Upload.php';
		$upload = new Sitengine_Upload($fileId);
		
		if($upload->isFile())
		{
			$messages = array();
			
			if(!preg_match('/(gif|jpg|jpeg)/i', $upload->getMime()))
			{
				$messages[] = $this->_controller->getTranslate()->translate('hintsAvatarOriginalFiletype');
			}
			
			if($upload->getSize() > 1024 * 1024)
			{
				$messages[] = $this->_controller->getTranslate()->translate('hintsAvatarOriginalFilesize');
			}
			
			if(sizeof($messages))
			{
				$this->_controller->getStatus()->addHint($fileId, $messages);
			}
		}
        return (!$this->_controller->getStatus()->hasHints());
    }
    */
    
    
    
    
    public function delete($id)
    {
        try {
        	if(
        		$id == Sitengine_Permiso::UID_ROOT ||
        		$id == Sitengine_Permiso::UID_GUEST ||
        		$id == Sitengine_Permiso::UID_LOSTFOUND
        	)
        	{
        		return 0;
        	}
        	
        	if(
                $this->_controller->getPermiso()->getDirectory()->userIsMember($id, Sitengine_Permiso::GID_ADMINISTRATORS) &&
                !$this->_controller->getPermiso()->getDirectory()->userIsMember($this->_controller->getPermiso()->getAuth()->getId(), Sitengine_Permiso::GID_ADMINISTRATORS)
            ) {
                # only administrators can delete users that are administrators members
                return 0;
            }
        	
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id)
            );
            require_once 'Sitengine/Sql.php';
			$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            return $this->_controller->getFrontController()->getPermiso()->getUsersTable()->delete($where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Permiso/Backend/Users/Exception.php';
            throw new Sitengine_Permiso_Backend_Users_Exception('delete error', $exception);
        }
    }
    
    
    
    
    public function updateFromList($id, array $data)
    {
        try {
        	if(
        		$id == Sitengine_Permiso::UID_ROOT ||
        		$id == Sitengine_Permiso::UID_GUEST ||
        		$id == Sitengine_Permiso::UID_LOSTFOUND
        	)
        	{
        		return 0;
        	}
        	
        	if(
                $this->_controller->getPermiso()->getDirectory()->userIsMember($id, Sitengine_Permiso::GID_ADMINISTRATORS) &&
                !$this->_controller->getPermiso()->getDirectory()->userIsMember($this->_controller->getPermiso()->getAuth()->getId(), Sitengine_Permiso::GID_ADMINISTRATORS)
            ) {
                # only administrators can update users that are administrators members
                return 0;
            }
        	
            # sanitize data
            foreach($data as $k => $v) {
                if(!preg_match('/^(enabled|locked)$/', $k)) {
                    unset($data[$k]);
                }
            }
            
            require_once 'Zend/Date.php';
            $date = new Zend_Date();
            $date->setTimezone('UTC');
            $data['mdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id)
            );
            require_once 'Sitengine/Sql.php';
    		$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            return $this->_controller->getFrontController()->getPermiso()->getUsersTable()->updateOrRollback($data, $where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Permiso/Backend/Users/Exception.php';
            throw new Sitengine_Permiso_Backend_Users_Exception('update from list error', $exception);
        }
    }
    
}
?>