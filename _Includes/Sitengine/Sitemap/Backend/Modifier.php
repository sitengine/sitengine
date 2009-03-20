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
 * @package    Sitengine_Sitemap
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */



require_once 'Zend/Date.php';
require_once 'Sitengine/Record.php';
require_once 'Sitengine/String.php';
require_once 'Sitengine/Validator.php';
require_once 'Sitengine/Upload.php';


abstract class Sitengine_Sitemap_Backend_Modifier
{
    
    const FIELDS_NORMAL = 'normal';
    const FIELDS_ONOFF = 'onOff';
    
    protected $_controller = null;
    protected $_payloads = null;
    protected $_allowedTypes = '/(gif|jpg|jpeg|png|pdf|mpeg|quicktime|msword|excel|flash|javascript|xml)/i';
    protected $_maxSize = 2097152; # 2M
    
    
    public function __construct(Sitengine_Sitemap_Backend_Controller $controller)
    {
        $this->_controller = $controller;
        
        require_once 'Sitengine/Form/TranslationPayloads.php';
        $this->_payloads = new Sitengine_Form_TranslationPayloads($this->_controller->getTranslations());
    }
    
    
    
    protected function _getFields($type)
    {
    	$fields = array();
    	
    	$fieldsNormalDefault = array(
			#'pid' => '',
			#Sitengine_Permiso::FIELD_UID => '',
			#Sitengine_Permiso::FIELD_GID => '',
			'keyword' => '',
			'description' => ''
		);
		
		$fieldsOnOffDefault = array(
			#Sitengine_Permiso::FIELD_RAG => 0,
			#Sitengine_Permiso::FIELD_RAW => 0,
			#Sitengine_Permiso::FIELD_UAG => 0,
			#Sitengine_Permiso::FIELD_UAW => 0,
			#Sitengine_Permiso::FIELD_DAG => 0,
			#Sitengine_Permiso::FIELD_DAW => 0,
			'locked' => 0,
			#'enabled' => 0
		);
		
    	if($type == Sitengine_Sitemap::ITEMTYPE_SNIPPET)
		{
			$fields[$this->_payloads->getMainName()] = array(
				self::FIELDS_NORMAL => $fieldsNormalDefault,
				self::FIELDS_ONOFF => $fieldsOnOffDefault
			);
			
			foreach($this->_controller->getTranslations()->get() as $index => $symbol)
			{
				$payloadName = $this->_payloads->makeTranslationName($symbol);
				$fields[$payloadName] = array(
					self::FIELDS_NORMAL => array(
						#'titleLang'.$index => '',
						'htmlLang'.$index => ''
					),
					self::FIELDS_ONOFF => array()
				);
			}
			return $fields[$this->_payloads->getName()];
		}
		else if($type == Sitengine_Sitemap::ITEMTYPE_FILE)
		{
			$fields[$this->_payloads->getMainName()] = array(
				self::FIELDS_NORMAL => $fieldsNormalDefault,
				self::FIELDS_ONOFF => $fieldsOnOffDefault
			);
			return $fields[$this->_payloads->getName()];
		}
		else if($type == Sitengine_Sitemap::ITEMTYPE_PAGE)
		{
			$fields[$this->_payloads->getMainName()] = array(
				self::FIELDS_NORMAL => $fieldsNormalDefault,
				self::FIELDS_ONOFF => $fieldsOnOffDefault
			);
			
			foreach($this->_controller->getTranslations()->get() as $index => $symbol)
			{
				$payloadName = $this->_payloads->makeTranslationName($symbol);
				$fields[$payloadName] = array(
					self::FIELDS_NORMAL => array(
						'titleLang'.$index => '',
						'metaKeywordsLang'.$index => '',
						'metaDescriptionLang'.$index => ''
					),
					self::FIELDS_ONOFF => array()
				);
			}
			return $fields[$this->_payloads->getName()];
		}
		else if($type == Sitengine_Sitemap::ITEMTYPE_LAYER)
		{
			$fields[$this->_payloads->getMainName()] = array(
				self::FIELDS_NORMAL => $fieldsNormalDefault,
				self::FIELDS_ONOFF => $fieldsOnOffDefault
			);
			return $fields[$this->_payloads->getName()];
		}
    	return $fields;
    }
    
    
    
    public function setAllowedTypes($types)
    {
        $this->_allowedTypes = $types;
    }
    
    
    
    public function setMaxSize($size)
    {
        $this->_maxSize = $size;
    }
    
    
    
    
    public function insert($type, $parentId)
    {
        try {
        	$this->_payloads->start();
        	$fields = $this->_getFields($type);
        	$input = $this->_controller->getRequest()->getPost(null);
        	$id = Sitengine_String::createId();
            $data  = array();
            
            if($type==Sitengine_Sitemap::ITEMTYPE_FILE) {
            	$this->_checkUpload(true);
			}
            
            if(!$this->_checkInput($type)) { return null; }
            $this->_controller->getRecord()->handleInsertUploads($id);
            
            foreach($fields[self::FIELDS_ONOFF] as $k => $v) {
                $data[$k] = (array_key_exists($k, $input)) ? 1 : 0;
            }
            foreach($fields[self::FIELDS_NORMAL] as $k => $v) {
                $data[$k] = (array_key_exists($k, $input)) ? $input[$k] : $v;
            }
            $data[Sitengine_Record::FIELD_ID] = $id;
            $data['pid'] = $parentId;
            $data['type'] = $type;
            #$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
            
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['cdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
			$data['mdate'] = $data['cdate'];
			
			### statically set permissions ###
			$ownerGroup = $this->_controller->getOwnerGroup();
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
			
			$data = array_merge($data, $this->_controller->getRecord()->getFileData());
            #Sitengine_Debug::print_r($data);
            $affectedRows = $this->_controller->getRecord()->insert($data);
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
            require_once 'Sitengine/Sitemap/Backend/Exception.php';
            throw new Sitengine_Sitemap_Backend_Exception('insert error', $exception);
        }
    }
    
    
    
    
    public function update($type, $id, array $stored)
    {
        try {
            $this->_payloads->start($this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME));
        	$fields = $this->_getFields($type);
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
            
            if($this->_payloads->isMain())
			{
				$name = 'keyword';
				$val = $this->_controller->getRequest()->getPost($name);
				if($id && $val!=$stored[$name] && 
				$stored[Sitengine_Permiso::FIELD_UID] != $this->_controller->getPermiso()->getAuth()->getId() && !$this->_controller->getPermiso()->getDirectory()->userIsMember($this->_controller->getPermiso()->getAuth()->getId(), Sitengine_Permiso::GID_ADMINISTRATORS)) {
					$message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_INVALID_ACTION);
					$this->_controller->getStatus()->addHint('modifier', $message);
					return null;
				}
            }
            
            if(!$this->_checkInput($type)) { return null; }
            
            $this->_controller->getRecord()->handleUpdateUploads($id, $stored);
            
            # sanitize data
            foreach($fields[self::FIELDS_ONOFF] as $k => $v) {
                $data[$k] = (array_key_exists($k, $input)) ? 1 : 0;
            }
            foreach($fields[self::FIELDS_NORMAL] as $k => $v) {
                if(array_key_exists($k, $input)) { $data[$k] = $input[$k]; }
            }
            /*
            if(
                $stored[Sitengine_Permiso::FIELD_UID]!=$this->_controller->getPermiso()->getAuth()->getId() &&
                #!$this->_controller->getPermiso()->getUser()->hasSupervisorRights() &&
                #!$this->_controller->getPermiso()->getUser()->hasModeratorRights()
                !$this->_controller->getPermiso()->getDirectory()->userIsMember($this->_controller->getPermiso()->getAuth()->getId(), Sitengine_Permiso::GID_ADMINISTRATORS)
            ) {
                unset($data[Sitengine_Permiso::FIELD_UID]);
                unset($data[Sitengine_Permiso::FIELD_GID]);
                unset($data[Sitengine_Permiso::FIELD_RAG]);
                unset($data[Sitengine_Permiso::FIELD_RAW]);
                unset($data[Sitengine_Permiso::FIELD_UAG]);
                unset($data[Sitengine_Permiso::FIELD_UAW]);
                unset($data[Sitengine_Permiso::FIELD_DAG]);
                unset($data[Sitengine_Permiso::FIELD_DAW]);
            }
            */
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
			
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['mdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            
            $data = array_merge($data, $this->_controller->getRecord()->getFileData());
            
            unset($data[Sitengine_Record::FIELD_ID]);
            unset($data['pid']);
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
            require_once 'Sitengine/Sitemap/Backend/Exception.php';
            throw new Sitengine_Sitemap_Backend_Exception('update error', $exception);
        }
    }
    
    
    
    protected function _checkInput($type)
    {
		if($type==Sitengine_Sitemap::ITEMTYPE_FILE)
		{
			$this->_checkGid();
			$this->_checkKeyword();
			$this->_checkUpload();
		}
		else if($type==Sitengine_Sitemap::ITEMTYPE_SNIPPET)
		{
			if($this->_payloads->isMain())
			{
				$this->_checkGid();
				$this->_checkKeyword();
			}
		}
		else if($type==Sitengine_Sitemap::ITEMTYPE_PAGE)
		{
			if($this->_payloads->isMain())
			{
				$this->_checkGid();
				$this->_checkKeyword();
			}
		}
		else if($type==Sitengine_Sitemap::ITEMTYPE_LAYER)
		{
			$this->_checkGid();
			$this->_checkKeyword();
		}
        return (!$this->_controller->getStatus()->hasHints());
    }
    
    
    protected function _checkGid()
    {
    	$name = 'gid';
		if($this->_controller->getRequest()->getPost($name)==Sitengine_Sitemap_Backend_Controller::VALUE_NONESELECTED) {
			$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($name).'Required');
			$this->_controller->getStatus()->addHint($name, $message);
		}
    }
    
    
    protected function _checkKeyword()
    {
    	$name = 'keyword';
		$val = $this->_controller->getRequest()->getPost($name);
		if(Sitengine_Validator::nada($val)) {
			$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($name).'Required');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		else if(!Sitengine_Validator::word($val)) {
			$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($name).'Invalid');
			$this->_controller->getStatus()->addHint($name, $message);
		}
    }
    
    
    protected function _checkUpload($required = false)
    {
    	$name = 'file1Original';
		$upload = new Sitengine_Upload($name);
		
		if($required && !$upload->isFile()) {
			$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($name).'Required');
			$this->_controller->getStatus()->addHint($name, $message);
			return null;
		}
		else if($upload->isFile()) {
			$n = 'transColor';
			$v = $this->_controller->getRequest()->getPost($n);
			if($v && !Sitengine_Validator::rgbColor($v)) {
				$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($n).'Invalid');
				$this->_controller->getStatus()->addHint($n, $message);
			}
			#print $upload->getMime();
			if(!preg_match($this->_allowedTypes, $upload->getMime())) {
				$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($name).'Filetype');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			if($upload->getSize() > $this->_maxSize) {
				$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($name).'Filesize');
				$this->_controller->getStatus()->addHint($name, $message);
			}
		}
    }
    
    
    
    
    public function delete($id)
    {
        try {
            $deleted = 0;
            
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id),
            	$this->_controller->getPermiso()->getDac()->getDeleteAccessSql($this->_controller->getAuthorizedGroups(), '', false)
            );
            require_once 'Sitengine/Sql.php';
			$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            $element = $this->_controller->getRecord()->selectRowAndFiles($where);
            
            #Sitengine_Debug::print_r($element);
            #exit;
            if(is_null($element)) {
            	return 0;
            	#throw $this->_controller->getExceptionInstance('delete error');
            }
            $deleted += $this->_controller->getRecord()->deleteRowAndFiles($element);
            $deleted += $this->_controller->getRecord()->deleteRowsAndFilesRecursively('pid', $element['id']);
            return $deleted;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Sitemap/Backend/Exception.php';
            throw new Sitengine_Sitemap_Backend_Exception('delete error', $exception);
        }
    }
    
    
    
    
    public function updateFromList($id, array $data)
    {
        try {
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
            	'id = '.$this->_controller->getDatabase()->quote($id),
            	$this->_controller->getPermiso()->getDac()->getUpdateAccessSql($this->_controller->getAuthorizedGroups(), '', false)
            );
            require_once 'Sitengine/Sql.php';
    		$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            return $this->_controller->getRecord()->update($data, $where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Sitemap/Backend/Exception.php';
            throw new Sitengine_Sitemap_Backend_Exception('update from list error', $exception);
        }
    }
    
}
?>