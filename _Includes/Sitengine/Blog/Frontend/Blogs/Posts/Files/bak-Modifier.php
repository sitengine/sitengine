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
 * @package    Sitengine_Blog
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */



require_once 'Zend/Date.php';
require_once 'Sitengine/String.php';
require_once 'Sitengine/Validator.php';
require_once 'Sitengine/Upload.php';
require_once 'Sitengine/Blog/File.php';


abstract class Sitengine_Blog_Frontend_Blogs_Posts_Files_Modifier
{
    
    const FIELDS_NORMAL = 'normal';
    const FIELDS_ONOFF = 'onOff';
    
    protected $_controller = null;
    protected $_payloads = null;
    
    
    public function __construct(Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller $controller)
    {
        $this->_controller = $controller;
        
        require_once 'Sitengine/Form/TranscriptsPayloads.php';
        $this->_payloads = new Sitengine_Form_TranscriptsPayloads($this->_controller->getRecord()->getTranscripts());
    }
    
    
    
    protected function _getFields()
    {
    	$fieldsNormal = array(
            #Sitengine_Permiso::FIELD_UID => '',
            #Sitengine_Permiso::FIELD_GID => '',
            'sorting' => '',
            'titleLang'.$this->_controller->getRecord()->getTranscripts()->getDefaultIndex() => '',
            'markupLang'.$this->_controller->getRecord()->getTranscripts()->getDefaultIndex() => ''
        );
        
        $fieldsOnOff = array(
            #Sitengine_Permiso::FIELD_RAG => 0,
            #Sitengine_Permiso::FIELD_RAW => 0,
            #Sitengine_Permiso::FIELD_UAG => 0,
            #Sitengine_Permiso::FIELD_UAW => 0,
            #Sitengine_Permiso::FIELD_DAG => 0,
            #Sitengine_Permiso::FIELD_DAW => 0,
            'publish' => 0
        );
        
        $fields[$this->_payloads->getMainName()] = array(
        	self::FIELDS_NORMAL => $fieldsNormal,
        	self::FIELDS_ONOFF => $fieldsOnOff
        );
        
        foreach($this->_controller->getRecord()->getTranscripts()->get() as $index => $symbol)
        {
        	$payloadName = $this->_payloads->makeTranscriptName($symbol);
        	$fields[$payloadName] = array(
        		self::FIELDS_NORMAL => array(
        			'titleLang'.$index => '',
        			'markupLang'.$index => ''
        		),
        		self::FIELDS_ONOFF => array()
        	);
        }
        return $fields[$this->_payloads->getName()];
    }
    
    
    
    
    public function insert($parentId)
    {
        try {
            $this->_payloads->start();
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
            $this->_controller->getRecord()->handleInsertUploads($id);
            
            foreach($fields[self::FIELDS_ONOFF] as $k => $v) {
                $data[$k] = (array_key_exists($k, $input)) ? 1 : 0;
            }
            foreach($fields[self::FIELDS_NORMAL] as $k => $v) {
                $data[$k] = (array_key_exists($k, $input)) ? $input[$k] : $v;
            }
            $data['id'] = $id;
            $data['parentId']= $parentId;
            #$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
            
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['cdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
			$data['mdate'] = $data['cdate'];
			
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
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('insert error', $exception);
        }
    }
    
    
    
    
    public function update($id, array $stored)
    {
        try {
            $this->_payloads->start($this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME));
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
            
            $this->_controller->getRecord()->handleUpdateUploads($id, $stored);
            # sanitize data
            foreach($fields[self::FIELDS_ONOFF] as $k => $v) {
                $data[$k] = (array_key_exists($k, $input)) ? 1 : 0;
            }
            foreach($fields[self::FIELDS_NORMAL] as $k => $v) {
                if(array_key_exists($k, $input)) { $data[$k] = $input[$k]; }
            }
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
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['mdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            $data = array_merge($data, $this->_controller->getRecord()->getFileData());
            unset($data['id']);
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
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('update error', $exception);
        }
    }
    
    
    
    
    protected function _checkInput()
    {
        if($this->_payloads->isMain())
        {
        	$name = 'titleLang'.$this->_controller->getRecord()->getTranscripts()->getDefaultIndex();
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getTranslate()->translate('hintsTitleRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			
			$name = 'gid';
			if($this->_controller->getRequest()->getPost($name)==Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller::VALUE_NONESELECTED) {
				$message = $this->_controller->getTranslate()->translate('hintsGidRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
        
			$fileId = 'file1Original';
			$upload = new Sitengine_Upload($fileId);
			
			if($upload->isFile())
			{
				$type = $this->_controller->getEntity()->getAncestorType();
				if($type == Sitengine_Blog_Posts_Table::TYPE_GALLERY) {
					$typesPattern = '/(gif|jpg|jpeg|png)/i';
				}
				else {
					$typesPattern = '/(gif|jpg|jpeg|png|pdf|mpeg|quicktime|msword|excel)/i';
				}
				
				$messages = array();
				
				if(!preg_match($typesPattern, $upload->getMime())) {
					$messages[] = $this->_controller->getTranslate()->translate('hintsFile1OriginalFiletype');
				}
				if($upload->getSize() > 1024*1024*15) {
					$messages[] = $this->_controller->getTranslate()->translate('hintsFile1OriginalFilesize');
				}
				if(sizeof($messages)) {
					$this->_controller->getStatus()->addHint($fileId, $messages);
				}
			}
        }
        return (!$this->_controller->getStatus()->hasHints());
    }
    
    
    
    public function delete($id)
    {
        try {
            $deleted = 0;
            
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id),
            	$this->_controller->getPermiso()->getDac()->getDeleteAccessSql($this->_controller->getFrontController()->getBlogPackage()->getAuthorizedGroups(), '', false)
            );
            require_once 'Sitengine/Sql.php';
			$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            $file = $this->_controller->getRecord()->selectRowAndFiles($where);
            return $this->_controller->getRecord()->deleteRowAndFiles($file);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('delete error', $exception);
        }
    }
    
    
    
    
    
    public function updateFromList($id, array $data)
    {
        try {
            # sanitize data
            foreach($data as $k => $v) {
                if(!preg_match('/^(sorting|publish)$/', $k)) {
                    unset($data[$k]);
                }
            }
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['mdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id),
            	$this->_controller->getPermiso()->getDac()->getUpdateAccessSql($this->_controller->getFrontController()->getBlogPackage()->getAuthorizedGroups(), '', false)
            );
            require_once 'Sitengine/Sql.php';
    		$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            return $this->_controller->getRecord()->update($data, $where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('update from list error', $exception);
        }
    }
    
    
    
    
    
    public function assignFromList($filename, array $data)
    {
        try {
        	# sanitize data
            foreach($data as $k => $v) {
                if(!preg_match('/^(cdate|mdate|gid|parentId)$/', $k)) {
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
            $data['publish']= 1;
            $data['titleLang0']= basename($filename);
            
			$this->_controller->getRecord()->handleFileImport($id, $sourcePath);
			$data = array_merge($data, $this->_controller->getRecord()->getFileData());
			#Sitengine_Debug::print_r($data);
			return $this->_controller->getRecord()->insertFileImport($data);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('assign from list error', $exception);
        }
    }
    
}
?>