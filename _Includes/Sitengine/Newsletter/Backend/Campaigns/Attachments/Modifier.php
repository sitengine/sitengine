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
require_once 'Sitengine/Upload.php';


abstract class Sitengine_Newsletter_Backend_Campaigns_Attachments_Modifier
{
    
    const FIELDS_NORMAL = 'normal';
    const FIELDS_ONOFF = 'onOff';
    
    protected $_controller = null;
    
    
    public function __construct(Sitengine_Newsletter_Backend_Campaigns_Attachments_Controller $controller)
    {
        $this->_controller = $controller;
    }
    
    
    
    protected function _getFields()
    {
    	$fieldsNormal = array(
            'title' => ''
        );
        
        $fieldsOnOff = array();
        
        return array(
        	self::FIELDS_NORMAL => $fieldsNormal,
        	self::FIELDS_ONOFF => $fieldsOnOff
        );
    }
    
    
    
    
    public function uploadToTempDir()
    {
        try {
            $fileId = 'Filedata';
			$upload = new Sitengine_Upload($fileId);
			
			if(!$upload->isFile()) {
				return $this->_controller->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_ERROR);
			}
			else if($upload->getError() > 0)
			{
				switch($upload->getError())
				{
					case 1:
						# uploaded file exceeds the UPLOAD_MAX_FILESIZE directive in php.ini
						return $this->_controller->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_SIZEEXCEEDED);
					case 2:
						# uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
						return $this->_controller->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_SIZEEXCEEDED);
					case 3:
						# uploaded file was only partially uploaded
						return $this->_controller->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_INCOMPLETE);
					case 4:
						return $this->_controller->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_NOFILE);
					default:
						return $this->_controller->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_ERROR);
				}
			}
			
			
			$filename = preg_replace("/[^a-zA-Z0-9._-]/", "_", $upload->getName());
            $path = $this->_controller->getTempDir().'/'.$filename;
			
			if(file_exists($path) )
			{
				return $this->_controller->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_EXISTS);
			}
			else if(is_uploaded_file($upload->getTempName()))
			{
				if(move_uploaded_file($upload->getTempName(), $path))
				{
					@chmod($path, 0666);
					return 'OK';
				}
			}
			return $this->_controller->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_ERROR);
        }
        catch (Exception $exception) {
            return $this->_controller->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_ERROR);
        }
    }
    
    
    
    
    public function insert()
    {
        try {
            $campaignId = $this->_controller->getEntity()->getAncestorId();
        	$fields = $this->_getFields();
        	$input = $this->_controller->getRequest()->getPost(null);
        	$id = Sitengine_String::createId();
            $data  = array();
            
            $name = 'file1Original';
			$upload = new Sitengine_Upload($name);
			
            if(!$upload->isFile()) {
				$message = $this->_controller->getTranslate()->translate('hintsFile1OriginalRequired');
				$this->_controller->getStatus()->addHint($name, $message);
				return null;
			}
            
            if(!$this->_checkInput()) { return null; }
            $this->_controller->getFrontController()->getNewsletterPackage()->getAttachmentsTable()->handleInsertUploads($id);
            
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
            $data['campaignId']= $campaignId;
            
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
			
            $data = array_merge($data, $this->_controller->getFrontController()->getNewsletterPackage()->getAttachmentsTable()->getFileData());
            #Sitengine_Debug::print_r($data);
            $insertId = $this->_controller->getFrontController()->getNewsletterPackage()->getAttachmentsTable()->insertOrRollback($data);
            if(!$insertId)
            {
            	$error = $this->_controller->getFrontController()->getNewsletterPackage()->getAttachmentsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Newsletter/Backend/Campaigns/Attachments/Exception.php';
            throw new Sitengine_Newsletter_Backend_Campaigns_Attachments_Exception('insert error', $exception);
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
            
            $this->_controller->getFrontController()->getNewsletterPackage()->getAttachmentsTable()->handleUpdateUploads($id, $stored);
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
            $data = array_merge($data, $this->_controller->getFrontController()->getNewsletterPackage()->getAttachmentsTable()->getFileData());
            unset($data['id']);
            #Sitengine_Debug::print_r($data);
            
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
            
            $where = $this->_controller->getDatabase()->quoteInto('id = ?', $id);
            $affectedRows = $this->_controller->getFrontController()->getNewsletterPackage()->getAttachmentsTable()->updateOrRollback($data, $where);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getNewsletterPackage()->getAttachmentsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Newsletter/Backend/Campaigns/Attachments/Exception.php';
            throw new Sitengine_Newsletter_Backend_Campaigns_Attachments_Exception('update error', $exception);
        }
    }
    
    
    
    
    
    protected function _checkInput()
    {
		$name = 'title';
		if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
			$message = $this->_controller->getTranslate()->translate('hintsTitleRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		
		$fileId = 'file1Original';
		$upload = new Sitengine_Upload($fileId);
		
		if($upload->isFile())
		{
			$messages = array();
			
			if(!preg_match('/(gif|jpg|jpeg|png|pdf|mpeg|mpg|quicktime|msword|excel)/i', $upload->getMime())) {
				$messages[] = $this->_controller->getTranslate()->translate('hintsFile1OriginalFiletype');
			}
			if($upload->getSize() > (1024 * 1024 * 5)) {
				$messages[] = $this->_controller->getTranslate()->translate('hintsFile1OriginalFilesize');
			}
			if(sizeof($messages)) {
				$this->_controller->getStatus()->addHint($fileId, $messages);
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
            return $this->_controller->getFrontController()->getNewsletterPackage()->getAttachmentsTable()->delete($where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Newsletter/Backend/Campaigns/Attachments/Exception.php';
            throw new Sitengine_Newsletter_Backend_Campaigns_Attachments_Exception('delete error', $exception);
        }
    }
    
    
    
    /*
    public function updateFromList($id, array $data)
    {
        try {
            # sanitize data
            foreach($data as $k => $v) {
                if(!preg_match('/^(sorting|publish|locked|displayThis)$/', $k)) {
                    unset($data[$k]);
                }
            }
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['mdate'] = $date->get(Zend_Date::ISO_8601, Sitengine_Env::LANGUAGE_EN);
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id),
            	$this->_controller->getPermiso()->getDac()->getUpdateAccessSql($this->_controller->getFrontController()->getNewsletterPackage()->getAuthorizedGroups(), '', false)
            );
            require_once 'Sitengine/Sql.php';
    		$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            return $this->_controller->getFrontController()->getNewsletterPackage()->getAttachmentsTable()->updateOrRollback($data, $where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Newsletter/Backend/Campaigns/Attachments/Exception.php';
            throw new Sitengine_Newsletter_Backend_Campaigns_Attachments_Exception('update from list error', $exception);
        }
    }
    */
    
    
    
    
    public function assignFromList($filename, array $data)
    {
        try {
        	# sanitize data
            foreach($data as $k => $v) {
                if(!preg_match('/^(type|cdate|mdate|gid|campaignId)$/', $k)) {
                    unset($data[$k]);
                }
            }
            
            $sourcePath = $this->_controller->getTempDir().'/'.$filename;
			if(!is_writeable($sourcePath) || !is_file($sourcePath)) { return 0; }
            
        	$id = Sitengine_String::createId();
            $data['id'] = $id;
            #$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
            $data[Sitengine_Permiso::FIELD_UID] = $this->_controller->getPermiso()->getAuth()->getId();
            $data[Sitengine_Permiso::FIELD_RAG] = 1;
            $data[Sitengine_Permiso::FIELD_RAW] = 1;
            $data[Sitengine_Permiso::FIELD_UAG] = 1;
            $data[Sitengine_Permiso::FIELD_UAW] = 0;
            $data[Sitengine_Permiso::FIELD_DAG] = 1;
            $data[Sitengine_Permiso::FIELD_DAW] = 0;
            $data['titleLang0']= basename($filename);
            
			$this->_controller->getFrontController()->getNewsletterPackage()->getAttachmentsTable()->handleFileImport($id, $sourcePath);
			$data = array_merge($data, $this->_controller->getFrontController()->getNewsletterPackage()->getAttachmentsTable()->getFileData());
			#Sitengine_Debug::print_r($data);
			return $this->_controller->getFrontController()->getNewsletterPackage()->getAttachmentsTable()->insertFileImport($data);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Newsletter/Backend/Campaigns/Attachments/Exception.php';
            throw new Sitengine_Newsletter_Backend_Campaigns_Attachments_Exception('assign from list error', $exception);
        }
    }
    
}
?>