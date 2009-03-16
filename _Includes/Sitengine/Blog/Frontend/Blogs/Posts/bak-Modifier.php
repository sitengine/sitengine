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
require_once 'Sitengine/Blog/Post.php';


abstract class Sitengine_Blog_Frontend_Blogs_Posts_Modifier
{
    
    const FIELDS_NORMAL = 'normal';
    const FIELDS_ONOFF = 'onOff';
    
    protected $_controller = null;
    protected $_payloads = null;
    
    
    public function __construct(Sitengine_Blog_Frontend_Blogs_Posts_Controller $controller)
    {
        $this->_controller = $controller;
        
        require_once 'Sitengine/Form/TranslationPayloads.php';
        $this->_payloads = new Sitengine_Form_TranslationPayloads($this->_controller->getRecord()->getTranslations());
    }
    
    
    
    
    public function insert($blogId)
    {
        try {
        	$type = $this->_controller->getRequest()->getPost(Sitengine_Blog::PARAM_TYPE);
            if($type == Sitengine_Blog_Posts_Table::TYPE_PHOTO) {
            	return $this->_insertPhotoPost($blogId);
            }
            if($type == Sitengine_Blog_Posts_Table::TYPE_GALLERY) {
            	return $this->_insertGalleryPost($blogId);
            }
            if($type == Sitengine_Blog_Posts_Table::TYPE_QUOTE) {
            	return $this->_insertQuotePost($blogId);
            }
            if($type == Sitengine_Blog_Posts_Table::TYPE_LINK) {
            	return $this->_insertLinkPost($blogId);
            }
            if($type == Sitengine_Blog_Posts_Table::TYPE_AUDIO) {
            	return $this->_insertAudioPost($blogId);
            }
            if($type == Sitengine_Blog_Posts_Table::TYPE_VIDEO) {
            	return $this->_insertVideoPost($blogId);
            }
            return $this->_insertTextPost($blogId);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('insert error', $exception);
        }
    }
    
    
    
    
    public function update($id, array $stored)
    {
        try {
            if($stored['type'] == Sitengine_Blog_Posts_Table::TYPE_PHOTO) {
            	return $this->_updatePhotoPost($id, $stored);
            }
            if($stored['type'] == Sitengine_Blog_Posts_Table::TYPE_GALLERY) {
            	return $this->_updateGalleryPost($id, $stored);
            }
            if($stored['type'] == Sitengine_Blog_Posts_Table::TYPE_QUOTE) {
            	return $this->_updateQuotePost($id, $stored);
            }
            if($stored['type'] == Sitengine_Blog_Posts_Table::TYPE_LINK) {
            	return $this->_updateLinkPost($id, $stored);
            }
            if($stored['type'] == Sitengine_Blog_Posts_Table::TYPE_AUDIO) {
            	return $this->_updateAudioPost($id, $stored);
            }
            if($stored['type'] == Sitengine_Blog_Posts_Table::TYPE_VIDEO) {
            	return $this->_updateVideoPost($id, $stored);
            }
            return $this->_updateTextPost($id, $stored);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('update error', $exception);
        }
    }
    
    
    
    ########################################################################
    ### TEXT POSTS
    ########################################################################
    protected function _getTextPostFields()
    {
    	$fieldsNormal = array(
            #Sitengine_Permiso::FIELD_UID => '',
            #Sitengine_Permiso::FIELD_GID => '',
            'titleLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex() => '',
            'markupLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex() => ''
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
        
        foreach($this->_controller->getRecord()->getTranslations()->get() as $index => $symbol)
        {
        	$payloadName = $this->_payloads->makeTranslationName($symbol);
        	$fields[$payloadName] = array(
        		self::FIELDS_NORMAL => array(
        			'titleLang'.$index => '',
        			'markupLang'.$index => ''
        		),
        		self::FIELDS_ONOFF => array()
        	);
        }
        #Sitengine_Debug::print_r($fields);
        return $fields[$this->_payloads->getName()];
    }
    
    
    
    protected function _checkTextPostInput()
    {
        if(
        	$this->_payloads->isMain() ||
        	$this->_payloads->isDefaultTranslation()
        )
        {
        	$name = 'titleLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex();
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getDictionary()->getFromTextPostHints('titleRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			$name = 'markupLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex();
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getDictionary()->getFromTextPostHints('markupRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
        
        }
        
        if($this->_payloads->isMain())
        {
			$name = 'gid';
			if($this->_controller->getRequest()->getPost($name)==Sitengine_Blog_Frontend_Blogs_Posts_Controller::VALUE_NONESELECTED) {
				$message = $this->_controller->getDictionary()->getFromTextPostHints('gidRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
        }
        return (!$this->_controller->getStatus()->hasHints());
    }
    
    
    
    
    protected function _insertTextPost($blogId)
    {
        try {
            $this->_payloads->start();
            $input = $this->_controller->getRequest()->getPost(null);
        	$fields = $this->_getTextPostFields();
        	$id = Sitengine_String::createId();
            $data  = array();
            
            if(!$this->_checkTextPostInput()) { return null; }
            
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
            $data['blogId'] = $blogId;
            $data['type'] = Sitengine_Blog_Posts_Table::TYPE_TEXT;
            #$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
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
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('insert error', $exception);
        }
    }
    
    
    
    protected function _updateTextPost($id, array $stored)
    {
        try {
            $this->_payloads->start($this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME));
            $input = $this->_controller->getRequest()->getPost(null);
        	$fields = $this->_getTextPostFields();
            $data = array();
            
            if(!$this->_controller->getPermiso()->getDac()->updateAccessGranted($stored)) {
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            if($this->_controller->getRequest()->getPost('mdate') != $stored['mdate']) {
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_DATA_EXPIRED);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            
            if(!$this->_checkTextPostInput()) { return null; }
            
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
            	$message = $this->_controller->getDictionary()->getFromHints($error);
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('update error', $exception);
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    ########################################################################
    ### PHOTO POSTS
    ########################################################################
    protected function _getPhotoPostFields()
    {
    	$fieldsNormal = array(
            #Sitengine_Permiso::FIELD_UID => '',
            #Sitengine_Permiso::FIELD_GID => '',
            'markupLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex() => '',
            'url' => ''
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
        
        foreach($this->_controller->getRecord()->getTranslations()->get() as $index => $symbol)
        {
        	$payloadName = $this->_payloads->makeTranslationName($symbol);
        	$fields[$payloadName] = array(
        		self::FIELDS_NORMAL => array(
        			'markupLang'.$index => ''
        		),
        		self::FIELDS_ONOFF => array()
        	);
        }
        #Sitengine_Debug::print_r($fields);
        return $fields[$this->_payloads->getName()];
    }
    
    
    
    protected function _checkPhotoPostInput()
    {
        if($this->_payloads->isMain())
        {
			$name = 'gid';
			if($this->_controller->getRequest()->getPost($name)==Sitengine_Blog_Frontend_Blogs_Posts_Controller::VALUE_NONESELECTED) {
				$message = $this->_controller->getDictionary()->getFromPhotoPostHints('gidRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			
			$fileId = 'file1Original';
			$upload = new Sitengine_Upload($fileId);
			
			if($upload->isFile())
			{
				$messages = array();
				
				if(!preg_match('/(gif|jpg|jpeg|png)/i', $upload->getMime())) {
					$messages[] = $this->_controller->getDictionary()->getFromPhotoPostHints('file1OriginalFiletype');
				}
				if($upload->getSize() > 1024*1024*2) {
					$messages[] = $this->_controller->getDictionary()->getFromPhotoPostHints('file1OriginalFilesize');
				}
				if(sizeof($messages)) {
					$this->_controller->getStatus()->addHint($fileId, $messages);
				}
			}
        }
        return (!$this->_controller->getStatus()->hasHints());
    }
    
    
    
    
    protected function _insertPhotoPost($blogId)
    {
        try {
            $this->_payloads->start();
            $input = $this->_controller->getRequest()->getPost(null);
        	$fields = $this->_getPhotoPostFields();
        	$id = Sitengine_String::createId();
            $data  = array();
            
            $name = 'file1Original';
			$upload = new Sitengine_Upload($name);
			
            if(!$upload->isFile()) {
				$name = 'file1Original';
				if(!$upload->isFile()) {
					$message = $this->_controller->getDictionary()->getFromPhotoPostHints('file1OriginalRequired');
					$this->_controller->getStatus()->addHint($name, $message);
					return null;
				}
			}
            
            if(!$this->_checkPhotoPostInput()) { return null; }
            
            $this->_controller->getRecord()->handleInsertUploads($id);
            
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
            $data['blogId'] = $blogId;
            $data['type'] = Sitengine_Blog_Posts_Table::TYPE_PHOTO;
            #$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
            $data = array_merge($data, $this->_controller->getRecord()->getFileData());
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
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('insert error', $exception);
        }
    }
    
    
    
    protected function _updatePhotoPost($id, array $stored)
    {
        try {
            $this->_payloads->start($this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME));
            $input = $this->_controller->getRequest()->getPost(null);
        	$fields = $this->_getPhotoPostFields();
            $data = array();
            
            if(!$this->_controller->getPermiso()->getDac()->updateAccessGranted($stored)) {
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            if($this->_controller->getRequest()->getPost('mdate') != $stored['mdate']) {
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_DATA_EXPIRED);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            
            if(!$this->_checkPhotoPostInput()) { return null; }
            
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
            	$message = $this->_controller->getDictionary()->getFromHints($error);
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('update error', $exception);
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    ########################################################################
    ### GALLERY POSTS
    ########################################################################
    protected function _getGalleryPostFields()
    {
    	$fieldsNormal = array(
            #Sitengine_Permiso::FIELD_UID => '',
            #Sitengine_Permiso::FIELD_GID => '',
            'titleLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex() => '',
            'markupLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex() => ''
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
        
        foreach($this->_controller->getRecord()->getTranslations()->get() as $index => $symbol)
        {
        	$payloadName = $this->_payloads->makeTranslationName($symbol);
        	$fields[$payloadName] = array(
        		self::FIELDS_NORMAL => array(
        			'titleLang'.$index => '',
        			'markupLang'.$index => ''
        		),
        		self::FIELDS_ONOFF => array()
        	);
        }
        #Sitengine_Debug::print_r($fields);
        return $fields[$this->_payloads->getName()];
    }
    
    
    
    protected function _checkGalleryPostInput()
    {
        if($this->_payloads->isMain())
        {
			$name = 'gid';
			if($this->_controller->getRequest()->getPost($name)==Sitengine_Blog_Frontend_Blogs_Posts_Controller::VALUE_NONESELECTED) {
				$message = $this->_controller->getDictionary()->getFromGalleryPostHints('gidRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			/*
			$fileId = 'file1Original';
			$upload = new Sitengine_Upload($fileId);
			
			if($upload->isFile())
			{
				$messages = array();
				
				if(!preg_match('/(gif|jpg|jpeg|png)/i', $upload->getMime())) {
					$messages[] = $this->_controller->getDictionary()->getFromGalleryPostHints('file1OriginalFiletype');
				}
				if($upload->getSize() > 1024*1024*2) {
					$messages[] = $this->_controller->getDictionary()->getFromGalleryPostHints('file1OriginalFilesize');
				}
				if(sizeof($messages)) {
					$this->_controller->getStatus()->addHint($fileId, $messages);
				}
			}
			*/
        }
        if(
        	$this->_payloads->isMain() ||
        	$this->_payloads->isDefaultTranslation()
        )
        {
        	$name = 'titleLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex();
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getDictionary()->getFromGalleryPostHints('titleRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
        }
        return (!$this->_controller->getStatus()->hasHints());
    }
    
    
    
    
    protected function _insertGalleryPost($blogId)
    {
        try {
            $this->_payloads->start();
            $input = $this->_controller->getRequest()->getPost(null);
        	$fields = $this->_getGalleryPostFields();
        	$id = Sitengine_String::createId();
            $data  = array();
            /*
            $name = 'file1Original';
			$upload = new Sitengine_Upload($name);
			
            if(!$upload->isFile()) {
				$name = 'file1Original';
				if(!$upload->isFile()) {
					$message = $this->_controller->getDictionary()->getFromGalleryPostHints('file1OriginalRequired');
					$this->_controller->getStatus()->addHint($name, $message);
					return null;
				}
			}
            */
            if(!$this->_checkGalleryPostInput()) { return null; }
            
            $this->_controller->getRecord()->handleInsertUploads($id);
            
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
            $data['blogId'] = $blogId;
            $data['type'] = Sitengine_Blog_Posts_Table::TYPE_GALLERY;
            #$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
            $data = array_merge($data, $this->_controller->getRecord()->getFileData());
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
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('insert error', $exception);
        }
    }
    
    
    
    protected function _updateGalleryPost($id, array $stored)
    {
        try {
            $this->_payloads->start($this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME));
            $input = $this->_controller->getRequest()->getPost(null);
        	$fields = $this->_getGalleryPostFields();
            $data = array();
            
            if(!$this->_controller->getPermiso()->getDac()->updateAccessGranted($stored)) {
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            if($this->_controller->getRequest()->getPost('mdate') != $stored['mdate']) {
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_DATA_EXPIRED);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            
            if(!$this->_checkGalleryPostInput()) { return null; }
            
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
            	$message = $this->_controller->getDictionary()->getFromHints($error);
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('update error', $exception);
        }
    }
    
    
    
    
    
    
    
    
    
    ########################################################################
    ### QUOTE POSTS
    ########################################################################
    protected function _getQuotePostFields()
    {
    	$fieldsNormal = array(
            #Sitengine_Permiso::FIELD_UID => '',
            #Sitengine_Permiso::FIELD_GID => '',
            'titleLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex() => '',
            'markupLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex() => ''
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
        
        foreach($this->_controller->getRecord()->getTranslations()->get() as $index => $symbol)
        {
        	$payloadName = $this->_payloads->makeTranslationName($symbol);
        	$fields[$payloadName] = array(
        		self::FIELDS_NORMAL => array(
        			'titleLang'.$index => '',
        			'markupLang'.$index => ''
        		),
        		self::FIELDS_ONOFF => array()
        	);
        }
        #Sitengine_Debug::print_r($fields);
        return $fields[$this->_payloads->getName()];
    }
    
    
    
    protected function _checkQuotePostInput()
    {
        if($this->_payloads->isMain())
        {
			$name = 'gid';
			if($this->_controller->getRequest()->getPost($name)==Sitengine_Blog_Frontend_Blogs_Posts_Controller::VALUE_NONESELECTED) {
				$message = $this->_controller->getDictionary()->getFromQuotePostHints('gidRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
        }
        if(
        	$this->_payloads->isMain() ||
        	$this->_payloads->isDefaultTranslation()
        )
        {
        	$name = 'markupLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex();
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getDictionary()->getFromQuotePostHints('markupRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
        }
        return (!$this->_controller->getStatus()->hasHints());
    }
    
    
    
    
    protected function _insertQuotePost($blogId)
    {
        try {
            $this->_payloads->start();
            $input = $this->_controller->getRequest()->getPost(null);
        	$fields = $this->_getQuotePostFields();
        	$id = Sitengine_String::createId();
            $data  = array();
            
            if(!$this->_checkQuotePostInput()) { return null; }
            
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
            $data['blogId'] = $blogId;
            $data['type'] = Sitengine_Blog_Posts_Table::TYPE_QUOTE;
            #$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
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
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('insert error', $exception);
        }
    }
    
    
    
    protected function _updateQuotePost($id, array $stored)
    {
        try {
            $this->_payloads->start($this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME));
            $input = $this->_controller->getRequest()->getPost(null);
        	$fields = $this->_getQuotePostFields();
            $data = array();
            
            if(!$this->_controller->getPermiso()->getDac()->updateAccessGranted($stored)) {
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            if($this->_controller->getRequest()->getPost('mdate') != $stored['mdate']) {
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_DATA_EXPIRED);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            
            if(!$this->_checkQuotePostInput()) { return null; }
            
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
            	$message = $this->_controller->getDictionary()->getFromHints($error);
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('update error', $exception);
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    ########################################################################
    ### LINK POSTS
    ########################################################################
    protected function _getLinkPostFields()
    {
    	$fieldsNormal = array(
            #Sitengine_Permiso::FIELD_UID => '',
            #Sitengine_Permiso::FIELD_GID => '',
            'titleLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex() => '',
            'markupLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex() => '',
            'url' => ''
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
        
        foreach($this->_controller->getRecord()->getTranslations()->get() as $index => $symbol)
        {
        	$payloadName = $this->_payloads->makeTranslationName($symbol);
        	$fields[$payloadName] = array(
        		self::FIELDS_NORMAL => array(
        			'titleLang'.$index => '',
        			'markupLang'.$index => ''
        		),
        		self::FIELDS_ONOFF => array()
        	);
        }
        #Sitengine_Debug::print_r($fields);
        return $fields[$this->_payloads->getName()];
    }
    
    
    
    protected function _checkLinkPostInput()
    {
        if($this->_payloads->isMain())
        {
			$name = 'gid';
			if($this->_controller->getRequest()->getPost($name)==Sitengine_Blog_Frontend_Blogs_Posts_Controller::VALUE_NONESELECTED) {
				$message = $this->_controller->getDictionary()->getFromLinkPostHints('gidRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
        }
        if(
        	$this->_payloads->isMain() ||
        	$this->_payloads->isDefaultTranslation()
        )
        {
        	$name = 'titleLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex();
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getDictionary()->getFromLinkPostHints('titleRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			
			$name = 'url';
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getDictionary()->getFromLinkPostHints('urlRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
        }
        return (!$this->_controller->getStatus()->hasHints());
    }
    
    
    
    
    protected function _insertLinkPost($blogId)
    {
        try {
            $this->_payloads->start();
            $input = $this->_controller->getRequest()->getPost(null);
        	$fields = $this->_getLinkPostFields();
        	$id = Sitengine_String::createId();
            $data  = array();
            
            if(!$this->_checkLinkPostInput()) { return null; }
            
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
            $data['blogId'] = $blogId;
            $data['type'] = Sitengine_Blog_Posts_Table::TYPE_LINK;
            #$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
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
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('insert error', $exception);
        }
    }
    
    
    
    protected function _updateLinkPost($id, array $stored)
    {
        try {
            $this->_payloads->start($this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME));
            $input = $this->_controller->getRequest()->getPost(null);
        	$fields = $this->_getLinkPostFields();
            $data = array();
            
            if(!$this->_controller->getPermiso()->getDac()->updateAccessGranted($stored)) {
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            if($this->_controller->getRequest()->getPost('mdate') != $stored['mdate']) {
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_DATA_EXPIRED);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            
            if(!$this->_checkLinkPostInput()) { return null; }
            
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
            	$message = $this->_controller->getDictionary()->getFromHints($error);
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('update error', $exception);
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    ########################################################################
    ### AUDIO POSTS
    ########################################################################
    protected function _getAudioPostFields()
    {
    	$fieldsNormal = array(
            #Sitengine_Permiso::FIELD_UID => '',
            #Sitengine_Permiso::FIELD_GID => '',
            'markupLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex() => ''
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
        
        foreach($this->_controller->getRecord()->getTranslations()->get() as $index => $symbol)
        {
        	$payloadName = $this->_payloads->makeTranslationName($symbol);
        	$fields[$payloadName] = array(
        		self::FIELDS_NORMAL => array(
        			'markupLang'.$index => ''
        		),
        		self::FIELDS_ONOFF => array()
        	);
        }
        #Sitengine_Debug::print_r($fields);
        return $fields[$this->_payloads->getName()];
    }
    
    
    
    protected function _checkAudioPostInput()
    {
        if($this->_payloads->isMain())
        {
			$name = 'gid';
			if($this->_controller->getRequest()->getPost($name)==Sitengine_Blog_Frontend_Blogs_Posts_Controller::VALUE_NONESELECTED) {
				$message = $this->_controller->getDictionary()->getFromAudioPostHints('gidRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			
			$fileId = 'file1Original';
			$upload = new Sitengine_Upload($fileId);
			
			if($upload->isFile())
			{
				$messages = array();
				
				if(!preg_match('/(mp3|mpg|mpeg)/i', $upload->getMime())) {
					$messages[] = $this->_controller->getDictionary()->getFromAudioPostHints('file1OriginalFiletype');
				}
				if($upload->getSize() > 1024*1024*15) {
					$messages[] = $this->_controller->getDictionary()->getFromAudioPostHints('file1OriginalFilesize');
				}
				if(sizeof($messages)) {
					$this->_controller->getStatus()->addHint($fileId, $messages);
				}
			}
        }
        return (!$this->_controller->getStatus()->hasHints());
    }
    
    
    
    
    protected function _insertAudioPost($blogId)
    {
        try {
            $this->_payloads->start();
            $input = $this->_controller->getRequest()->getPost(null);
        	$fields = $this->_getAudioPostFields();
        	$id = Sitengine_String::createId();
            $data  = array();
            
            $name = 'file1Original';
			$upload = new Sitengine_Upload($name);
			
            if(!$upload->isFile()) {
				$message = $this->_controller->getDictionary()->getFromAudioPostHints('file1OriginalRequired');
				$this->_controller->getStatus()->addHint($name, $message);
				return null;
			}
            
            if(!$this->_checkAudioPostInput()) { return null; }
            
            $this->_controller->getRecord()->handleInsertUploads($id);
            
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
            $data['blogId'] = $blogId;
            $data['type'] = Sitengine_Blog_Posts_Table::TYPE_AUDIO;
            #$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
            $data = array_merge($data, $this->_controller->getRecord()->getFileData());
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
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('insert error', $exception);
        }
    }
    
    
    
    protected function _updateAudioPost($id, array $stored)
    {
        try {
            $this->_payloads->start($this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME));
            $input = $this->_controller->getRequest()->getPost(null);
        	$fields = $this->_getAudioPostFields();
            $data = array();
            
            if(!$this->_controller->getPermiso()->getDac()->updateAccessGranted($stored)) {
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            if($this->_controller->getRequest()->getPost('mdate') != $stored['mdate']) {
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_DATA_EXPIRED);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            
            if(!$this->_checkAudioPostInput()) { return null; }
            
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
            	$message = $this->_controller->getDictionary()->getFromHints($error);
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('update error', $exception);
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    ########################################################################
    ### VIDEO POSTS
    ########################################################################
    protected function _getVideoPostFields()
    {
    	$fieldsNormal = array(
            #Sitengine_Permiso::FIELD_UID => '',
            #Sitengine_Permiso::FIELD_GID => '',
            'embedTag' => '',
            'titleLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex() => '',
            'markupLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex() => ''
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
        
        foreach($this->_controller->getRecord()->getTranslations()->get() as $index => $symbol)
        {
        	$payloadName = $this->_payloads->makeTranslationName($symbol);
        	$fields[$payloadName] = array(
        		self::FIELDS_NORMAL => array(
        			'titleLang'.$index => '',
        			'markupLang'.$index => ''
        		),
        		self::FIELDS_ONOFF => array()
        	);
        }
        #Sitengine_Debug::print_r($fields);
        return $fields[$this->_payloads->getName()];
    }
    
    
    
    protected function _checkVideoPostInput()
    {
        if($this->_payloads->isMain())
        {
			$name = 'gid';
			if($this->_controller->getRequest()->getPost($name)==Sitengine_Blog_Frontend_Blogs_Posts_Controller::VALUE_NONESELECTED) {
				$message = $this->_controller->getDictionary()->getFromVideoPostHints('gidRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			
			$name = 'embedTag';
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getDictionary()->getFromVideoPostHints('embedTagRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
        }
        if(
        	$this->_payloads->isMain() ||
        	$this->_payloads->isDefaultTranslation()
        )
        {
        	$name = 'titleLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex();
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getDictionary()->getFromVideoPostHints('titleRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
        }
        return (!$this->_controller->getStatus()->hasHints());
    }
    
    
    
    
    protected function _insertVideoPost($blogId)
    {
        try {
            $this->_payloads->start();
            $input = $this->_controller->getRequest()->getPost(null);
        	$fields = $this->_getVideoPostFields();
        	$id = Sitengine_String::createId();
            $data  = array();
            
            if(!$this->_checkVideoPostInput()) { return null; }
            
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
            $data['blogId'] = $blogId;
            $data['type'] = Sitengine_Blog_Posts_Table::TYPE_VIDEO;
            #$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
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
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('insert error', $exception);
        }
    }
    
    
    
    protected function _updateVideoPost($id, array $stored)
    {
        try {
            $this->_payloads->start($this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME));
            $input = $this->_controller->getRequest()->getPost(null);
        	$fields = $this->_getVideoPostFields();
            $data = array();
            
            if(!$this->_controller->getPermiso()->getDac()->updateAccessGranted($stored)) {
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            if($this->_controller->getRequest()->getPost('mdate') != $stored['mdate']) {
                $message = $this->_controller->getDictionary()->getFromHints(Sitengine_Env::HINT_DATA_EXPIRED);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            
            if(!$this->_checkVideoPostInput()) { return null; }
            
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
            	$message = $this->_controller->getDictionary()->getFromHints($error);
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('update error', $exception);
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    public function delete(
        $id,
        Sitengine_Blog_Frontend_Blogs_Posts_Comments_Record $commentRecord,
        Sitengine_Blog_Frontend_Blogs_Posts_Files_Record $fileRecord
    )
    {
        try {
            $deleted = 0;
            
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id),
            	$this->_controller->getPermiso()->getDac()->getDeleteAccessSql($this->_controller->getFrontController()->getBlogPackage()->getAuthorizedGroups(), '', false)
            );
            require_once 'Sitengine/Sql.php';
			$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            $post = $this->_controller->getRecord()->selectRowAndFiles($where);
            $deleted += $this->_controller->getRecord()->deleteRowAndFiles($post);
            
            $whereClauses = array(
            	'parentId = '.$this->_controller->getDatabase()->quote($id)
            );
            require_once 'Sitengine/Sql.php';
			$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            $comments = $commentRecord->selectRowsAndFiles($where);
            foreach($comments as $commentRow)
            {
                $deleted += $commentRecord->deleteRowAndFiles($commentRow);
            }
            
            $whereClauses = array(
            	'parentId = '.$this->_controller->getDatabase()->quote($id)
            );
            require_once 'Sitengine/Sql.php';
			$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            $files = $fileRecord->selectRowsAndFiles($where);
            foreach($files as $fileRow)
            {
                $deleted += $fileRecord->deleteRowAndFiles($fileRow);
            }
            return $deleted;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('delete error', $exception);
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
			$data['mdate'] = $date->get(Zend_Date::ISO_8601, Sitengine_Env::LANGUAGE_EN);
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id),
            	$this->_controller->getPermiso()->getDac()->getUpdateAccessSql($this->_controller->getFrontController()->getBlogPackage()->getAuthorizedGroups(), '', false)
            );
            require_once 'Sitengine/Sql.php';
    		$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            return $this->_controller->getRecord()->update($data, $where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('update from list error', $exception);
        }
    }
    
}
?>