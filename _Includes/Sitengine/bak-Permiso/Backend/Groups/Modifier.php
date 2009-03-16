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


abstract class Sitengine_Permiso_Backend_Groups_Modifier
{
    
    const FIELDS_NORMAL = 'normal';
    const FIELDS_ONOFF = 'onOff';
    
    protected $_controller = null;
    protected $_payloads = null;
    
    
    public function __construct(Sitengine_Permiso_Backend_Groups_Controller $controller)
    {
        $this->_controller = $controller;
        
        require_once 'Sitengine/Form/Payloads.php';
        $this->_payloads = new Sitengine_Form_Payloads();
    }
    
    
    
    protected function _getFields()
    {
    	$fieldsNormal = array(
            'name' => '',
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
    
    
    
    
    protected function _ok2modify($id)
    {
        # system groups can't be updated/deleted
        return (
            $id != Sitengine_Permiso::GID_ADMINISTRATORS &&
            $id != Sitengine_Permiso::GID_LOSTFOUND
        );
    }
    
    
    
    
    public function insert()
    {
        try {
            $this->_payloads->start();
        	$fields = $this->_getFields();
        	$input = $this->_controller->getRequest()->getPost(null);
        	$id = Sitengine_String::createId();
            $data  = array();
            
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
            
            if(!$this->_checkInput()) { return null; }
            
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
    
    
    
    
    
    
    
    protected function _checkInput()
    {
    	if($this->_payloads->isMain())
        {
			$name = 'name';
			$val = $this->_controller->getRequest()->getPost($name);
			# name can't be empty
			if(Sitengine_Validator::nada($val)) {
				$message = $this->_controller->getDictionary()->getFromHints($name.'Required');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			# names must be made up of a-zA-Z0-9
			else if(!Sitengine_Validator::word($val)) {
				$message = $this->_controller->getDictionary()->getFromHints($name.'Invalid');
				$this->_controller->getStatus()->addHint($name, $message);
			}
		}
        return (!$this->_controller->getStatus()->hasHints());
    }
    
    
    
    
    
    
    public function delete(
        $id,
        Sitengine_Permiso_Backend_Groups_Members_Record $memberRecord
    )
    {
        try {
            if(!$this->_ok2modify($id)) {
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_INVALID_ACTION);
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
					'groupId = '.$this->_controller->getDatabase()->quote($id)
				);
				$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
				$deleted += $memberRecord->delete($where);
                /*
                foreach($tables as $table => $locktype)
                {
                    $q = 'SHOW COLUMNS FROM '.$table.' LIKE "'.Sitengine_Permiso::FIELD_GID.'"';
                    $statement = $this->_controller->getDatabase()->prepare($q);
					$statement->execute();
					$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
					#Sitengine_Debug::print_r($result);
					if(sizeof($result)) {
						$where = Sitengine_Permiso::FIELD_GID.' = '.$this->_controller->getDatabase()->quote($id);
						$data = array(#Sitengine_Permiso::FIELD_GID => $this->_controller->getPermiso()->getOrganization()->getLostfoundId());
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
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_INVALID_ACTION);
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