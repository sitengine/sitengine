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


abstract class Sitengine_Permiso_Backend_Groups_Modifier
{
    
    protected $_controller = null;
    
    
    public function __construct(Sitengine_Permiso_Backend_Groups_Controller $controller)
    {
        $this->_controller = $controller;
    }
    
    
    
    protected function _getFields()
    {
        return array(
            'name' => '',
            'enabled' => 1,
            'locked' => 0,
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
            
            #Sitengine_Debug::print_r($data);
            $insertId = $this->_controller->getFrontController()->getPermisoPackage()->getGroupsTable()->insert($data);
            if(!$insertId)
            {
            	$error = $this->_controller->getFrontController()->getPermisoPackage()->getGroupsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Permiso/Backend/Groups/Exception.php';
            throw new Sitengine_Permiso_Backend_Groups_Exception('insert error', $exception);
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
            $affectedRows = $this->_controller->getFrontController()->getPermisoPackage()->getGroupsTable()->update($data, $where);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getPermisoPackage()->getGroupsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Permiso/Backend/Groups/Exception.php';
            throw new Sitengine_Permiso_Backend_Groups_Exception('update error', $exception);
        }
    }
    
    
    
    
    protected function _ok2modify($id)
    {
        # system groups can't be updated/deleted
        return (
            $id != Sitengine_Permiso::GID_ADMINISTRATORS &&
            $id != Sitengine_Permiso::GID_LOSTFOUND
        );
    }
    
    
    
    
    protected function _checkInput()
    {
    	require_once 'Sitengine/Validator.php';
    	
    	$name = 'name';
		$val = $this->_controller->getRequest()->getPost($name);
		# name can't be empty
		if(Sitengine_Validator::nada($val)) {
			$message = $this->_controller->getTranslate()->translate('hintsNameRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		# names must be made up of a-zA-Z0-9
		else if(!Sitengine_Validator::word($val)) {
			$message = $this->_controller->getTranslate()->translate('hintsNameInvalid');
			$this->_controller->getStatus()->addHint($name, $message);
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
            
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id)
            );
            require_once 'Sitengine/Sql.php';
			$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            return $this->_controller->getFrontController()->getPermisoPackage()->getGroupsTable()->delete($where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Permiso/Backend/Groups/Exception.php';
            throw new Sitengine_Permiso_Backend_Groups_Exception('delete error', $exception);
        }
    }
    
    
    
    
    public function updateFromList($id, array $data)
    {
        try {
        	if(!$this->_ok2modify($id))
        	{
                return 0;
            }
            
            # sanitize data
            foreach($data as $k => $v)
            {
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
            return $this->_controller->getFrontController()->getPermisoPackage()->getGroupsTable()->update($data, $where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Permiso/Backend/Groups/Exception.php';
            throw new Sitengine_Permiso_Backend_Groups_Exception('update from list error', $exception);
        }
    }
    
}
?>