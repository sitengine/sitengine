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


abstract class Sitengine_Permiso_Backend_Groups_Members_Modifier
{
    
    protected $_controller = null;
    
    
    public function __construct(Sitengine_Permiso_Backend_Groups_Members_Controller $controller)
    {
        $this->_controller = $controller;
    }
    
    
    
    protected function _getFields()
    {
        return array(
            'userId' => '',
            'locked' => 0
        );
    }
    
    
    
    public function insert()
    {
        try {
            $groupId = $this->_controller->getEntity()->getAncestorId();
        	$fields = $this->_getFields();
        	$input = $this->_controller->getRequest()->getPost(null);
        	
        	require_once 'Sitengine/String.php';
        	$id = Sitengine_String::createId();
            $data  = array();
            
            if(!$this->_checkInput()) { return null; }
            
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
            $data['groupId']= $groupId;
            
            #Sitengine_Debug::print_r($data);
            $insertId = $this->_controller->getFrontController()->getPermisoPackage()->getMembershipsTable()->insert($data);
            if(!$insertId)
            {
            	$error = $this->_controller->getFrontController()->getPermisoPackage()->getMembershipsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getDictionary()->getFromHints($error);
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Permiso/Backend/Groups/Members/Exception.php';
            throw new Sitengine_Permiso_Backend_Groups_Members_Exception('insert error', $exception);
        }
    }
    
    
    
    
    public function update()
    {
        try {
            $id = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ID);
            $stored = $this->_controller->getEntity()->getRow()->toArray();
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
            
            if(!$this->_checkInput()) { return null; }
            
            foreach($fields as $k => $v)
            {
                if(array_key_exists($k, $input)) { $data[$k] = $input[$k]; }
            }
            
            require_once 'Zend/Date.php';
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['mdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            unset($data['id']);
            #Sitengine_Debug::print_r($data);
            
            $where = $this->_controller->getDatabase()->quoteInto('id = ?', $id);
            $affectedRows = $this->_controller->getFrontController()->getPermisoPackage()->getMembershipsTable()->update($data, $where);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getPermisoPackage()->getMembershipsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getDictionary()->getFromHints($error);
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Permiso/Backend/Groups/Members/Exception.php';
            throw new Sitengine_Permiso_Backend_Groups_Members_Exception('update error', $exception);
        }
    }
    
    
    
    
    
    protected function _ok2modify($id)
    {
        # system memberships can't be updated/deleted
        return (
            $id != Sitengine_Permiso::UID_ROOT &&
            $id != Sitengine_Permiso::UID_LOSTFOUND
        );
    }
    
    
    
    
    
    protected function _checkInput()
    {
    	require_once 'Sitengine/Validator.php';
    	
    	$name = 'userId';
		$userId = $this->_controller->getRequest()->getPost($name);
		$groupId = $this->_controller->getEntity()->getAncestorId();
		
		if(Sitengine_Validator::nada($userId, Sitengine_Permiso_Backend_Groups_Members_Controller::VALUE_NONESELECTED)) {
			$message = $this->_controller->getDictionary()->getFromHints('userIdRequired');
			$this->_controller->getStatus()->addHint($name, $message);
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
        return (!$this->_controller->getStatus()->hasHints());
    }
    
    
    
    
    
    public function delete($id)
    {
        try {
        	if(!$this->_ok2modify($id))
        	{
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
            return $this->_controller->getFrontController()->getPermisoPackage()->getMembershipsTable()->delete($where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Permiso/Backend/Groups/Members/Exception.php';
            throw new Sitengine_Permiso_Backend_Groups_Members_Exception('delete error', $exception);
        }
    }
    
    
    
    
    public function updateFromList($id, array $data)
    {
        try {
        	if(!$this->_ok2modify($id))
        	{
                return 0;
            }
            
            foreach($data as $k => $v) {
                if(!preg_match('/^(locked)$/', $k)) {
                    unset($data[$k]);
                }
            }
            
            require_once 'Zend/Date.php';
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['mdate'] = $date->get(Zend_Date::ISO_8601, Sitengine_Env::LANGUAGE_EN);
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id)
            );
            require_once 'Sitengine/Sql.php';
    		$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            return $this->_controller->getFrontController()->getPermisoPackage()->getMembershipsTable()->update($data, $where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Permiso/Backend/Groups/Members/Exception.php';
            throw new Sitengine_Permiso_Backend_Groups_Members_Exception('update from list error', $exception);
        }
    }
    
}
?>