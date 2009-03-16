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


abstract class Sitengine_Permiso_Backend_Groups_Members_Modifier
{
    
    const FIELDS_NORMAL = 'normal';
    const FIELDS_ONOFF = 'onOff';
    
    protected $_controller = null;
    protected $_payloads = null;
    
    
    public function __construct(Sitengine_Permiso_Backend_Groups_Members_Controller $controller)
    {
        $this->_controller = $controller;
        
        require_once 'Sitengine/Form/Payloads.php';
        $this->_payloads = new Sitengine_Form_Payloads();
    }
    
    
    
    protected function _getFields()
    {
    	$fieldsNormal = array(
            'groupId' => '', # aid
            'userId' => ''
        );
        
        $fieldsOnOff = array(
            'locked' => 0
        );
        
        $fields[$this->_payloads->getMainName()] = array(
        	self::FIELDS_NORMAL => $fieldsNormal,
        	self::FIELDS_ONOFF => $fieldsOnOff
        );
        return $fields[$this->_payloads->getName()];
    }
    
    
    
    protected function _ok2modify($id)
    {
        # system memberships can't be updated/deleted
        return (
            $id != Sitengine_Permiso::UID_ROOT &&
            $id != Sitengine_Permiso::UID_LOSTFOUND
        );
    }
    
    
    
    
    public function insert($groupId)
    {
        try {
            $this->_payloads->start();
        	$fields = $this->_getFields();
        	$input = $this->_controller->getRequest()->getPost(null);
        	$id = Sitengine_String::createId();
            $data  = array();
            
            if(!$this->_checkInput($groupId)) { return null; }
            
            foreach($fields[self::FIELDS_ONOFF] as $k => $v) {
                $data[$k] = (array_key_exists($k, $input)) ? 1 : 0;
            }
            foreach($fields[self::FIELDS_NORMAL] as $k => $v) {
                $data[$k] = (array_key_exists($k, $input)) ? $input[$k] : $v;
            }
            $data[Sitengine_Record::FIELD_ID] = $id;
            $data['groupId']= $groupId;
            #$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
            
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['cdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
			$data['mdate'] = $data['cdate'];
			
            #Sitengine_Debug::print_r($data);
            $affectedRows = $this->_controller->getRecord()->insert($data);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getRecord()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getDictionary()->getFromHints($error);
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            throw $this->_controller->getExceptionInstance('insert error', $exception);
        }
    }
    
    
    
    
    
    public function update($id, array $stored)
    {
        try {
            $this->_payloads->start($this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME));
        	$fields = $this->_getFields();
            $input = $this->_controller->getRequest()->getPost(null);
            $data = array();
            
            if(!$this->_ok2modify($id)) {
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            if($this->_controller->getRequest()->getPost('mdate') != $stored['mdate']) {
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_DATA_EXPIRED);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            if(!$this->_checkInput($stored['groupId'])) { return null; }
            
            # sanitize data
            foreach($fields[self::FIELDS_ONOFF] as $k => $v) {
                $data[$k] = (array_key_exists($k, $input)) ? 1 : 0;
            }
            foreach($fields[self::FIELDS_NORMAL] as $k => $v) {
                if(array_key_exists($k, $input)) { $data[$k] = $input[$k]; }
            }
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['mdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            unset($data[Sitengine_Record::FIELD_ID]);
            unset($data['groupId']);
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
            	$message = $this->_controller->getDictionary()->getFromHints($error);
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            throw $this->_controller->getExceptionInstance('update error', $exception);
        }
    }
    
    
    
    
    protected function _checkInput($groupId)
    {
    	if($this->_payloads->isMain())
        {
			$name = 'userId';
			$userId = $this->_controller->getRequest()->getPost($name);
			
			if(Sitengine_Validator::nada($userId, Sitengine_Permiso_Backend_Groups_Members_Controller::VALUE_NONESELECTED)) {
				$message = $this->_controller->getDictionary()->getFromHints($name.'Required');
				$this->_controller->getStatus()->addHint($name, $message);
				return false;
			}
			
			if(
				$userId == Sitengine_Permiso::UID_GUEST ||
				$userId == Sitengine_Permiso::UID_LOSTFOUND
			) {
				# users guest and lostfound can't be made a member of any group
				$message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_INVALID_ACTION);
				$this->_controller->getStatus()->addHint('modifier', $message);
				return false;
			}
			
			if(
				$groupId == Sitengine_Permiso::GID_ADMINISTRATORS &&
				!$this->_controller->getPermiso()->getDirectory()->userIsMember($this->_controller->getPermiso()->getAuth()->getId(), Sitengine_Permiso::GID_ADMINISTRATORS)
			) {
				# only administrators can add users to the administrators group
				$message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_INVALID_ACTION);
				$this->_controller->getStatus()->addHint('modifier', $message);
				return false;
			}
			
			if($groupId == Sitengine_Permiso::UID_LOSTFOUND) {
				# no users can be added to the lostfound group
				$message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_INVALID_ACTION);
				$this->_controller->getStatus()->addHint('modifier', $message);
				return false;
			}
		}
        return true;
    }
    
    
    
    
    public function delete($id)
    {
        try {
            if(!$this->_ok2modify($id)) {
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return 0;
            }
            
            $membership = $this->_controller->getPermiso()->getDirectory()->findMembershipById($id);
            
            if(
                !is_null($membership) &&
                $membership['groupId'] == Sitengine_Permiso::GID_ADMINISTRATORS &&
                !$this->_controller->getPermiso()->getDirectory()->userIsMember($this->_controller->getPermiso()->getAuth()->getId(), Sitengine_Permiso::GID_ADMINISTRATORS)
            ) {
                # only administrators can delete members from the administrators group
                return 0;
            }
            $whereClauses = array(
        		'id = '.$this->_controller->getDatabase()->quote($id)
        	);
        	require_once 'Sitengine/Sql.php';
    		$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            return $this->_controller->getRecord()->delete($where);
        }
        catch (Exception $exception) {
            throw $this->_controller->getExceptionInstance('delete error', $exception);
        }
    }
    
    
    
    
    
    public function updateFromList($id, array $data)
    {
        try {
            if(!$this->_ok2modify($id)) {
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return 0;
            }
            
            # sanitize data
            foreach($data as $k => $v) {
                if(!preg_match('/^(locked)$/', $k)) {
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