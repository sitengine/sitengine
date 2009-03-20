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



require_once 'Zend/Date.php';
require_once 'Sitengine/Record.php';
require_once 'Sitengine/String.php';
require_once 'Sitengine/Validator.php';


abstract class Sitengine_Permiso_Backend_Users_Modifier
{

    const FIELDS_NORMAL = 'normal';
    const FIELDS_ONOFF = 'onOff';
    
    protected $_controller = null;
    protected $_payloads = null;
    
    
    public function __construct(Sitengine_Permiso_Backend_Users_Controller $controller)
    {
        $this->_controller = $controller;
        
        require_once 'Sitengine/Form/Payloads.php';
        $this->_payloads = new Sitengine_Form_Payloads();
    }
    
    
    
    protected function _getFields()
    {
    	$fieldsNormal = array(
            'changeRequestId' => '',
            'language' => '',
            'timezone' => '',
            'name' => '',
            'nickname' => '',
            'firstname' => '',
            'lastname' => '',
            'password' => '',
            'email' => '',
            'description' => ''
        );
        
        $fieldsOnOff = array(
            'enabled' => 0,
            'locked' => 0
        );
        
        $fields[$this->_payloads->getMainName()] = array(
        	self::FIELDS_NORMAL => $fieldsNormal,
        	self::FIELDS_ONOFF => $fieldsOnOff
        );
        return $fields[$this->_payloads->getName()];
    }
    
    
    
    
    public function insert()
    {
        try {
            $this->_payloads->start();
        	$fields = $this->_getFields();
        	$input = $this->_controller->getRequest()->getPost(null);
        	$id = Sitengine_String::createId();
            $data  = array();
            $notifyNewUser = $this->_controller->getRequest()->getPost('notifyNewUser');
            
            if($notifyNewUser) {
            	$password = Sitengine_String::createId(8);
            }
            else {
				$name = 'password';
				$password = $this->_controller->getRequest()->getPost($name);
				if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
					$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($name).'Required');
					$this->_controller->getStatus()->addHint($name, $message);
				}
			}
            
            if(!$this->_checkInput()) { return null; }
            
            foreach($fields[self::FIELDS_ONOFF] as $k => $v) {
                $data[$k] = (array_key_exists($k, $input)) ? 1 : 0;
            }
            foreach($fields[self::FIELDS_NORMAL] as $k => $v) {
                $data[$k] = (array_key_exists($k, $input)) ? $input[$k] : $v;
            }
            $data[Sitengine_Record::FIELD_ID] = $id;
            #$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
            
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['cdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
			$data['mdate'] = $data['cdate'];
			
            # make names lowercase
            $data['name'] = mb_strtolower($data['name']);
            # encrypt password
            $data['password'] = md5($password);
            #Sitengine_Debug::print_r($data);
            if($this->_controller->getRecord()->insert($data))
            {
            	if(!$notifyNewUser) { return $data; }
            	$this->_sendNotifyMail($data, $password);
            	return $data;
            }
            $error = $this->_controller->getRecord()->getError();
			if($error === null) { return null; }
			$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
			$this->_controller->getStatus()->addHint('record', $message);
            return null;
        }
        catch (Exception $exception) {
            throw $this->_controller->getExceptionInstance('insert error', $exception);
        }
    }
    
    
    
    protected function _sendNotifyMail($data, $password)
    {
		$subject = $this->_controller->getTranslate()->translate('welcomemailSubject');
		$subject = preg_replace('/%site%/', $_SERVER['SERVER_NAME'], $subject);
		$find = array('/%site%/', '/%user%/', '/%password%/');
		$repl = array($_SERVER['SERVER_NAME'], $data['name'], $password);
		$body = $this->_controller->getTranslate()->translate('welcomemailBody');
		$body = preg_replace($find, $repl, $body);
		
		require_once 'Zend/Mail.php';
		$msg = new Zend_Mail();
		
		if(
			$this->_controller->getEnv()->getModeratorSenderMail() === null ||
			sizeof($this->_controller->getEnv()->getModeratorMails()) == 0
		)
		{
			throw $this->_controller->getExceptionInstance('moderator sender/recipients not set in config');
		}
		$msg
			->setSubject($subject)
			->setBodyText($body)
			->setFrom($this->_controller->getEnv()->getModeratorSenderMail(), $_SERVER['SERVER_NAME'])
			->addTo($data['name'])
			->send()
		;
    }
    
    
    
    
    public function update($id, array $stored)
    {
        try {
            $this->_payloads->start($this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME));
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
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['mdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            
            if(
				$id == Sitengine_Permiso::UID_ROOT ||
				$id == Sitengine_Permiso::UID_GUEST ||
				$id == Sitengine_Permiso::UID_LOSTFOUND
			) {
				# system account cannot be unlocked or disabled
				unset($data['enabled']);
				unset($data['locked']);
			}
            
            if(
                $id != Sitengine_Permiso::UID_GUEST &&
                $id != Sitengine_Permiso::UID_LOSTFOUND &&
                $data['password'] != ''
            ) {
                # only use password if not guest or lostfound and password not empty
                $data['password'] = md5($data['password']);
            }
            else { unset($data['password']); }
            unset($data[Sitengine_Record::FIELD_ID]);
            #Sitengine_Debug::print_r($data);
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id)
            );
            require_once 'Sitengine/Sql.php';
    		$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            $affectedRows = $this->_controller->getRecord()->update($data, $where);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getRecord()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            throw $this->_controller->getExceptionInstance('update error', $exception);
        }
    }
    
    
    
    
    protected function _checkInput()
    {
    	if($this->_payloads->isMain())
        {
			$name = 'language';
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name), Sitengine_Permiso_Backend_Users_Controller::VALUE_NONESELECTED)) {
				$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($name).'Required');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			$name = 'timezone';
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name), Sitengine_Permiso_Backend_Users_Controller::VALUE_NONESELECTED)) {
				$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($name).'Required');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			$name = 'name';
			$val = $this->_controller->getRequest()->getPost($name);
			if(
				$val != Sitengine_Permiso::ROOT_NAME &&
				$val != Sitengine_Permiso::GUEST_NAME &&
				$val != Sitengine_Permiso::LOSTFOUND_NAME
			) {
				if(Sitengine_Validator::nada($val)) {
					$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($name).'Required');
					$this->_controller->getStatus()->addHint($name, $message);
				}
				else if(!Sitengine_Validator::emailAddress($val)) {
					$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($name).'ValidEmailRequired');
					$this->_controller->getStatus()->addHint($name, $message);
					return false;
				}
			}
			$name = 'nickname';
			$val = $this->_controller->getRequest()->getPost($name);
			if(Sitengine_Validator::nada($val)) {
				$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($name).'Required');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			$name = 'firstname';
			$val = $this->_controller->getRequest()->getPost($name);
			if(Sitengine_Validator::nada($val)) {
				$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($name).'Required');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			$name = 'lastname';
			$val = $this->_controller->getRequest()->getPost($name);
			if(Sitengine_Validator::nada($val)) {
				$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($name).'Required');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			$name = 'password';
			$val = $this->_controller->getRequest()->getPost($name);
			# passwords must be made up of a-zA-Z0-9
			if(!Sitengine_Validator::nada($val) && !Sitengine_Validator::word($val)) {
				$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($name).'WordCharsOnly');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			if($val!=$this->_controller->getRequest()->getPost('passwordConfirm')) {
				$message = $this->_controller->getTranslate()->translate('hintsPasswordsDontMatch');
				$this->_controller->getStatus()->addHint($name, $message);
			}
		}
		return (!$this->_controller->getStatus()->hasHints());
    }
    
    
    
    
    /*
    protected function _checkPassword()
    {
    	$name = 'password';
		$val = $this->_controller->getRequest()->getPost($name);
		# passwords must be made up of a-zA-Z0-9
		if(Sitengine_Validator::nada($val) || !Sitengine_Validator::word($val)) {
			$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($name).'WordCharsOnly');
			$this->_controller->getStatus()->addHint($name, $message);
			return false;
		}
		if($val != $this->_controller->getRequest()->getPost('passwordConfirm')) {
			$message = $this->_controller->getTranslate()->translate('hintsPasswordsDontMatch');
			$this->_controller->getStatus()->addHint($name, $message);
			return false;
		}
		return true;
    }
    */
    
    
    
    
    protected function _ok2modify($id)
    {
        # system users can't be deleted/modified from list
        return (
            $id != Sitengine_Permiso::UID_ROOT &&
            $id != Sitengine_Permiso::UID_GUEST &&
            $id != Sitengine_Permiso::UID_LOSTFOUND
        );
    }
    
    
    
    
    public function delete(
        $id,
        Sitengine_Permiso_Backend_Users_Memberships_Record $membershipRecord
    )
    {
        try {
            if(!$this->_ok2modify($id)) {
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return 0;
            }
            
            if(
                $this->_controller->getPermiso()->getDirectory()->userIsMember($id, Sitengine_Permiso::GID_ADMINISTRATORS) &&
                !$this->_controller->getPermiso()->getDirectory()->userIsMember($this->_controller->getPermiso()->getAuth()->getId(), Sitengine_Permiso::GID_ADMINISTRATORS)
            ) {
                # only administrators can delete users that are administrators members
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return 0;
            }
            
            $whereClauses = array(
        		'id = '.$this->_controller->getDatabase()->quote($id)
        	);
        	require_once 'Sitengine/Sql.php';
    		$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            $deleted = $this->_controller->getRecord()->delete($where);
            
            if($deleted > 0)
            {
            	$whereClauses = array(
					'userId = '.$this->_controller->getDatabase()->quote($id)
				);
				require_once 'Sitengine/Sql.php';
				$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
				$deleted += $membershipRecord->delete($where);
                /*
                foreach($tables as $table => $val)
                {
                	$q = 'SHOW COLUMNS FROM '.$table.' LIKE "'.Sitengine_Permiso::FIELD_UID.'"';
                    $statement = $this->_controller->getDatabase()->prepare($q);
					$statement->execute();
					$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
					#Sitengine_Debug::print_r($result);
					if(sizeof($result)) {
						$where = Sitengine_Permiso::FIELD_UID.' = '.$this->_controller->getDatabase()->quote($id);
						$data = array(#Sitengine_Permiso::FIELD_UID => $this->_controller->getPermiso()->getOrganization()->getLostfoundId());
						$changed = $this->_controller->getDatabase()->update($table, $data, $where);
						#print $table.' - '.$chenged.'<br />';
                    }
                }
                */
            }
            return $deleted;
        }
        catch (Exception $exception) {
            throw $this->_controller->getExceptionInstance('delete error', $exception);
        }
    }
    
    
    
    
    
    public function updateFromList($id, array $data)
    {
        try {
            if(!$this->_ok2modify($id)) {
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return 0;
            }
            if(
            	$this->_controller->getPermiso()->getDirectory()->userIsMember($id, Sitengine_Permiso::GID_ADMINISTRATORS) &&
            	!$this->_controller->getPermiso()->getDirectory()->userIsMember($this->_controller->getPermiso()->getAuth()->getId(), Sitengine_Permiso::GID_ADMINISTRATORS)
            )
            {
            	$message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return 0;
            }
            
            # sanitize data
            foreach($data as $k => $v) {
                if(!preg_match('/^(enabled|locked)$/', $k)) {
                    unset($data[$k]);
                }
            }
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['mdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            
            $whereClauses = array(
        		'id = '.$this->_controller->getDatabase()->quote($id)
        	);
        	require_once 'Sitengine/Sql.php';
    		$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            return $this->_controller->getRecord()->update($data, $where);
        }
        catch (Exception $exception) {
            throw $this->_controller->getExceptionInstance('update from list error', $exception);
        }
    }
}
?>