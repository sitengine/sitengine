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
 * @package    Sitengine_Newsletter
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Zend/Date.php';
require_once 'Sitengine/String.php';
require_once 'Sitengine/Validator.php';


abstract class Sitengine_Newsletter_Backend_Campaigns_Modifier
{
    
    const FIELDS_NORMAL = 'normal';
    const FIELDS_ONOFF = 'onOff';
    
    protected $_controller = null;
    protected $_payloads = null;
    
    
    public function __construct(Sitengine_Newsletter_Backend_Campaigns_Controller $controller)
    {
        $this->_controller = $controller;
        
        require_once 'Sitengine/Form/Payloads.php';
        $this->_payloads = new Sitengine_Form_Payloads(array('content'));
    }
    
    
    
    protected function _getFields()
    {
    	$fieldsNormal = array(
            'type' => '',
            'title' => '',
            'body' => ''
        );
        
        $fieldsOnOff = array(
            'publish' => 0
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
            $data  = $this->_controller->getFrontController()->getNewsletterPackage()->getCampaignsTable()->getDefaultPermissionData($this->_controller->getPermiso(), $this->_controller->getFrontController()->getNewsletterPackage()->getOwnerGroup());
            
            if(!$this->_checkInput()) { return null; }
            
            foreach($fields[self::FIELDS_ONOFF] as $k => $v) {
                $data[$k] = (array_key_exists($k, $input)) ? 1 : 0;
            }
            foreach($fields[self::FIELDS_NORMAL] as $k => $v) {
                $data[$k] = (array_key_exists($k, $input)) ? $input[$k] : $v;
            }
            
            $date = new Zend_Date();
            $date->setTimezone('UTC');
            $data['cdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            $data['mdate'] = $data['cdate'];
            $data['id'] = $id;
            
            ### statically set permissions ###
			$ownerGroup = $this->_controller->getFrontController()->getNewsletterPackage()->getOwnerGroup();
			$gid = $this->_controller->getPermiso()->getDirectory()->getGroupId($ownerGroup);
			$gid = (!is_null($gid)) ? $gid : Sitengine_Permiso::GID_ADMINISTRATORS;
			$uid = $this->_controller->getPermiso()->getAuth()->getId();
			
			$data[Sitengine_Permiso::FIELD_UID] = $uid;
			$data[Sitengine_Permiso::FIELD_GID] = $gid;
			$data[Sitengine_Permiso::FIELD_RAG] = 1;
			$data[Sitengine_Permiso::FIELD_RAW] = 1;
			$data[Sitengine_Permiso::FIELD_UAG] = 1;
			$data[Sitengine_Permiso::FIELD_UAW] = 0;
			$data[Sitengine_Permiso::FIELD_DAG] = 1;
			$data[Sitengine_Permiso::FIELD_DAW] = 0;
			### statically set permissions ###
			
            #Sitengine_Debug::print_r($data);
            $insertId = $this->_controller->getFrontController()->getNewsletterPackage()->getCampaignsTable()->insert($data);
            if(!$insertId)
            {
            	$error = $this->_controller->getFrontController()->getNewsletterPackage()->getCampaignsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Newsletter/Backend/Campaigns/Exception.php';
            throw new Sitengine_Newsletter_Backend_Campaigns_Exception('insert error', $exception);
        }
    }
    
    
    
    
    public function update()
    {
        try {
        	$this->_payloads->start($this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME));
        	
        	$id = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ID);
            $stored = $this->_controller->getEntity()->getRow()->toArray();
        	$fields = $this->_getFields();
            $input = $this->_controller->getRequest()->getPost(null);
            $data = array();
            
            if(!$this->_controller->getPermiso()->getDac()->updateAccessGranted($stored)) {
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
            
            foreach($fields[self::FIELDS_ONOFF] as $k => $v) {
                $data[$k] = (array_key_exists($k, $input)) ? 1 : 0;
            }
            foreach($fields[self::FIELDS_NORMAL] as $k => $v) {
                if(array_key_exists($k, $input)) { $data[$k] = $input[$k]; }
            }
            $date = new Zend_Date();
            $date->setTimezone('UTC');
            $data['mdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            unset($data['id']);
            
            ### statically set permissions ###
            unset($data[Sitengine_Permiso::FIELD_UID]);
			unset($data[Sitengine_Permiso::FIELD_GID]);
			unset($data[Sitengine_Permiso::FIELD_RAG]);
			unset($data[Sitengine_Permiso::FIELD_RAW]);
			unset($data[Sitengine_Permiso::FIELD_UAG]);
			unset($data[Sitengine_Permiso::FIELD_UAW]);
			unset($data[Sitengine_Permiso::FIELD_DAG]);
			unset($data[Sitengine_Permiso::FIELD_DAW]);
			### statically set permissions ###
			
            #Sitengine_Debug::print_r($data);
    		$where = $this->_controller->getDatabase()->quoteInto('id = ?', $id);
            $affectedRows = $this->_controller->getFrontController()->getNewsletterPackage()->getCampaignsTable()->update($data, $where);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getNewsletterPackage()->getCampaignsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Newsletter/Backend/Campaigns/Exception.php';
            throw new Sitengine_Newsletter_Backend_Campaigns_Exception('update error', $exception);
        }
    }
    
    
    
    
    
    protected function _checkInput()
    {
        if(
        	$this->_payloads->isMain()
        )
        {
        	$name = 'title';
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getTranslate()->translate('hintsTitleRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
        
        }
        return (!$this->_controller->getStatus()->hasHints());
    }
    
    
    
    
    
    public function delete($id)
    {
        try {
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id),
            	$this->_controller->getPermiso()->getDac()->getDeleteAccessSql($this->_controller->getFrontController()->getNewsletterPackage()->getAuthorizedGroups(), '', false)
            );
            require_once 'Sitengine/Sql.php';
			$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            return $this->_controller->getFrontController()->getNewsletterPackage()->getCampaignsTable()->delete($where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Newsletter/Backend/Campaigns/Exception.php';
            throw new Sitengine_Newsletter_Backend_Campaigns_Exception('delete error', $exception);
        }
    }
    
    
    
    
    public function updateFromList($id, array $data)
    {
        try {
            # sanitize data
            foreach($data as $k => $v) {
                if(!preg_match('/^(publish)$/', $k)) {
                    unset($data[$k]);
                }
            }
            $date = new Zend_Date();
            $date->setTimezone('UTC');
            $data['mdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id),
            	$this->_controller->getPermiso()->getDac()->getUpdateAccessSql($this->_controller->getFrontController()->getNewsletterPackage()->getAuthorizedGroups(), '', false)
            );
            require_once 'Sitengine/Sql.php';
    		$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            return $this->_controller->getFrontController()->getNewsletterPackage()->getCampaignsTable()->update($data, $where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Newsletter/Backend/Campaigns/Exception.php';
            throw new Sitengine_Newsletter_Backend_Campaigns_Exception('update from list error', $exception);
        }
    }
    
}
?>