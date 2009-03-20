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


abstract class Sitengine_Blog_Backend_Blogs_Posts_Modifier
{
    
    const FIELDS_NORMAL = 'normal';
    const FIELDS_ONOFF = 'onOff';
    
    protected $_controller = null;
    protected $_payloads = null;
    
    
    public function __construct(Sitengine_Blog_Backend_Blogs_Posts_Controller $controller)
    {
        $this->_controller = $controller;
        
        $table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
        $translations = $table->getTranslations();
        require_once 'Sitengine/Form/TranslationPayloads.php';
        $this->_payloads = new Sitengine_Form_TranslationPayloads($translations);
    }
    
    
    
    
    public function insert()
    {
        try {
        	$blogId = $this->_controller->getEntity()->getAncestorId();
        	
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
            require_once 'Sitengine/Blog/Backend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Posts_Exception('insert error', $exception);
        }
    }
    
    
    
    
    public function update()
    {
        try {
        	$id = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ID);
            $stored = $this->_controller->getEntity()->getRow()->toArray();
            
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
            require_once 'Sitengine/Blog/Backend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Posts_Exception('update error', $exception);
        }
    }
    
    
    
    ########################################################################
    ### TEXT POSTS
    ########################################################################
    protected function _getTextPostFields()
    {
    	$table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
        $translations = $table->getTranslations();
        
    	$fieldsNormal = array(
            ##Sitengine_Permiso::FIELD_UID => '',
            ##Sitengine_Permiso::FIELD_GID => '',
            'titleLang'.$translations->getDefaultIndex() => '',
            'markupLang'.$translations->getDefaultIndex() => '',
            'teaserLang'.$translations->getDefaultIndex() => ''
        );
        
        $fieldsOnOff = array(
            /*
            #Sitengine_Permiso::FIELD_RAG => 0,
            #Sitengine_Permiso::FIELD_RAW => 0,
            #Sitengine_Permiso::FIELD_UAG => 0,
            #Sitengine_Permiso::FIELD_UAW => 0,
            #Sitengine_Permiso::FIELD_DAG => 0,
            #Sitengine_Permiso::FIELD_DAW => 0,
            */
            'publish' => 0
        );
        
        $fields[$this->_payloads->getMainName()] = array(
        	self::FIELDS_NORMAL => $fieldsNormal,
        	self::FIELDS_ONOFF => $fieldsOnOff
        );
        
        foreach($translations->get() as $index => $symbol)
        {
        	$payloadName = $this->_payloads->makeTranslationName($symbol);
        	$fields[$payloadName] = array(
        		self::FIELDS_NORMAL => array(
        			'titleLang'.$index => '',
        			'markupLang'.$index => '',
        			'teaserLang'.$index => ''
        		),
        		self::FIELDS_ONOFF => array()
        	);
        }
        #Sitengine_Debug::print_r($fields);
        return $fields[$this->_payloads->getName()];
    }
    
    
    
    protected function _checkTextPostInput()
    {
    	$table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
        $translations = $table->getTranslations();
        
        if(
        	$this->_payloads->isMain() ||
        	$this->_payloads->isDefaultTranslation()
        )
        {
        	$name = 'titleLang'.$translations->getDefaultIndex();
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getTranslate()->translate('textposthintsTitleRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			$name = 'markupLang'.$translations->getDefaultIndex();
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getTranslate()->translate('textposthintsMarkupRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
        
        }
        
        if($this->_payloads->isMain())
        {
			$name = 'gid';
			if($this->_controller->getRequest()->getPost($name)==Sitengine_Blog_Backend_Blogs_Posts_Controller::VALUE_NONESELECTED) {
				$message = $this->_controller->getTranslate()->translate('textposthintsGidRequired');
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
            
            $data = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getDefaultPermissionData(
        		$this->_controller->getPermiso(),
        		$this->_controller->getFrontController()->getBlogPackage()->getOwnerGroup()
        	);
            
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
            ##$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
            #Sitengine_Debug::print_r($data);
            $affectedRows = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->insert($data);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Posts_Exception('insert error', $exception);
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
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            if($this->_controller->getRequest()->getPost('mdate') != $stored['mdate']) {
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_DATA_EXPIRED);
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
            $affectedRows = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->update($data, $where);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Posts_Exception('update error', $exception);
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    ########################################################################
    ### PHOTO POSTS
    ########################################################################
    protected function _getPhotoPostFields()
    {
    	$table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
        $translations = $table->getTranslations();
        
    	$fieldsNormal = array(
            ##Sitengine_Permiso::FIELD_UID => '',
            ##Sitengine_Permiso::FIELD_GID => '',
            'titleLang'.$translations->getDefaultIndex() => '',
            'markupLang'.$translations->getDefaultIndex() => '',
            'url' => ''
        );
        
        $fieldsOnOff = array(
            /*
            #Sitengine_Permiso::FIELD_RAG => 0,
            #Sitengine_Permiso::FIELD_RAW => 0,
            #Sitengine_Permiso::FIELD_UAG => 0,
            #Sitengine_Permiso::FIELD_UAW => 0,
            #Sitengine_Permiso::FIELD_DAG => 0,
            #Sitengine_Permiso::FIELD_DAW => 0,
            */
            'publish' => 0
        );
        
        $fields[$this->_payloads->getMainName()] = array(
        	self::FIELDS_NORMAL => $fieldsNormal,
        	self::FIELDS_ONOFF => $fieldsOnOff
        );
        
        foreach($translations->get() as $index => $symbol)
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
    
			
			
			
    
    protected function _checkPhotoPostInput()
    {
    	$table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
        $translations = $table->getTranslations();
        
        if($this->_payloads->isMain())
        {
			$name = 'gid';
			if($this->_controller->getRequest()->getPost($name)==Sitengine_Blog_Backend_Blogs_Posts_Controller::VALUE_NONESELECTED) {
				$message = $this->_controller->getTranslate()->translate('photoposthintsGidRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			
			$name = 'titleLang'.$translations->getDefaultIndex();
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getTranslate()->translate('photoposthintsTitleRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			
			$fileId = 'file1Original';
			$upload = new Sitengine_Upload($fileId);
			
			if($upload->isFile())
			{
				$messages = array();
				
				if(!preg_match('/(gif|jpg|jpeg|png)/i', $upload->getMime())) {
					$messages[] = $this->_controller->getTranslate()->translate('photoposthintsFile1OriginalFiletype');
				}
				if($upload->getSize() > 1024*1024*2) {
					$messages[] = $this->_controller->getTranslate()->translate('photoposthintsFile1OriginalFilesize');
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
            
            $data = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getDefaultPermissionData(
        		$this->_controller->getPermiso(),
        		$this->_controller->getFrontController()->getBlogPackage()->getOwnerGroup()
        	);
            
            $name = 'file1Original';
			$upload = new Sitengine_Upload($name);
			
            if(!$upload->isFile()) {
				$name = 'file1Original';
				if(!$upload->isFile()) {
					$message = $this->_controller->getTranslate()->translate('photoposthintsFile1OriginalRequired');
					$this->_controller->getStatus()->addHint($name, $message);
					return null;
				}
			}
            
            if(!$this->_checkPhotoPostInput()) { return null; }
            
            $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->handleInsertUploads($id);
            
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
            ##$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
            $data = array_merge($data, $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getFileData());
            #Sitengine_Debug::print_r($data);
            $affectedRows = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->insert($data);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Posts_Exception('insert error', $exception);
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
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            if($this->_controller->getRequest()->getPost('mdate') != $stored['mdate']) {
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_DATA_EXPIRED);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            
            if(!$this->_checkPhotoPostInput()) { return null; }
            
            $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->handleUpdateUploads($id, $stored);
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
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['mdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            $data = array_merge($data, $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getFileData());
            unset($data['id']);
            #Sitengine_Debug::print_r($data);
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id)
            );
            require_once 'Sitengine/Sql.php';
    		$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            $affectedRows = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->update($data, $where);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Posts_Exception('update error', $exception);
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    ########################################################################
    ### GALLERY POSTS
    ########################################################################
    protected function _getGalleryPostFields()
    {
    	$table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
        $translations = $table->getTranslations();
        
    	$fieldsNormal = array(
            ##Sitengine_Permiso::FIELD_UID => '',
            ##Sitengine_Permiso::FIELD_GID => '',
            'titleLang'.$translations->getDefaultIndex() => '',
            'markupLang'.$translations->getDefaultIndex() => '',
            'teaserLang'.$translations->getDefaultIndex() => ''
        );
        
        $fieldsOnOff = array(
            /*
            #Sitengine_Permiso::FIELD_RAG => 0,
            #Sitengine_Permiso::FIELD_RAW => 0,
            #Sitengine_Permiso::FIELD_UAG => 0,
            #Sitengine_Permiso::FIELD_UAW => 0,
            #Sitengine_Permiso::FIELD_DAG => 0,
            #Sitengine_Permiso::FIELD_DAW => 0,
            */
            'publish' => 0
        );
        
        $fields[$this->_payloads->getMainName()] = array(
        	self::FIELDS_NORMAL => $fieldsNormal,
        	self::FIELDS_ONOFF => $fieldsOnOff
        );
        
        foreach($translations->get() as $index => $symbol)
        {
        	$payloadName = $this->_payloads->makeTranslationName($symbol);
        	$fields[$payloadName] = array(
        		self::FIELDS_NORMAL => array(
        			'titleLang'.$index => '',
        			'markupLang'.$index => '',
        			'teaserLang'.$index => ''
        		),
        		self::FIELDS_ONOFF => array()
        	);
        }
        #Sitengine_Debug::print_r($fields);
        return $fields[$this->_payloads->getName()];
    }
    
    
    
    protected function _checkGalleryPostInput()
    {
    	$table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
        $translations = $table->getTranslations();
        
        if($this->_payloads->isMain())
        {
			$name = 'gid';
			if($this->_controller->getRequest()->getPost($name)==Sitengine_Blog_Backend_Blogs_Posts_Controller::VALUE_NONESELECTED) {
				$message = $this->_controller->getTranslate()->translate('galleryposthintsGidRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			/*
			$fileId = 'file1Original';
			$upload = new Sitengine_Upload($fileId);
			
			if($upload->isFile())
			{
				$messages = array();
				
				if(!preg_match('/(gif|jpg|jpeg|png)/i', $upload->getMime())) {
					$messages[] = $this->_controller->getTranslate()->translate('galleryposthintsFile1OriginalFiletype');
				}
				if($upload->getSize() > 1024*1024*2) {
					$messages[] = $this->_controller->getTranslate()->translate('galleryposthintsFile1OriginalFilesize');
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
        	$name = 'titleLang'.$translations->getDefaultIndex();
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getTranslate()->translate('galleryposthintsTitleRequired');
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
            
            $data = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getDefaultPermissionData(
        		$this->_controller->getPermiso(),
        		$this->_controller->getFrontController()->getBlogPackage()->getOwnerGroup()
        	);
            /*
            $name = 'file1Original';
			$upload = new Sitengine_Upload($name);
			
            if(!$upload->isFile()) {
				$name = 'file1Original';
				if(!$upload->isFile()) {
					$message = $this->_controller->getTranslate()->translate('galleryposthintsFile1OriginalRequired');
					$this->_controller->getStatus()->addHint($name, $message);
					return null;
				}
			}
            */
            if(!$this->_checkGalleryPostInput()) { return null; }
            
            $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->handleInsertUploads($id);
            
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
            ##$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
            $data = array_merge($data, $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getFileData());
            #Sitengine_Debug::print_r($data);
            $affectedRows = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->insert($data);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Posts_Exception('insert error', $exception);
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
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            if($this->_controller->getRequest()->getPost('mdate') != $stored['mdate']) {
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_DATA_EXPIRED);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            
            if(!$this->_checkGalleryPostInput()) { return null; }
            
            $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->handleUpdateUploads($id, $stored);
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
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['mdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            $data = array_merge($data, $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getFileData());
            unset($data['id']);
            #Sitengine_Debug::print_r($data);
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id)
            );
            require_once 'Sitengine/Sql.php';
    		$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            $affectedRows = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->update($data, $where);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Posts_Exception('update error', $exception);
        }
    }
    
    
    
    
    
    
    
    
    
    ########################################################################
    ### QUOTE POSTS
    ########################################################################
    protected function _getQuotePostFields()
    {
    	$table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
        $translations = $table->getTranslations();
        
    	$fieldsNormal = array(
            ##Sitengine_Permiso::FIELD_UID => '',
            ##Sitengine_Permiso::FIELD_GID => '',
            'titleLang'.$translations->getDefaultIndex() => '',
            'teaserLang'.$translations->getDefaultIndex() => ''
        );
        
        $fieldsOnOff = array(
            /*
            #Sitengine_Permiso::FIELD_RAG => 0,
            #Sitengine_Permiso::FIELD_RAW => 0,
            #Sitengine_Permiso::FIELD_UAG => 0,
            #Sitengine_Permiso::FIELD_UAW => 0,
            #Sitengine_Permiso::FIELD_DAG => 0,
            #Sitengine_Permiso::FIELD_DAW => 0,
            */
            'publish' => 0
        );
        
        $fields[$this->_payloads->getMainName()] = array(
        	self::FIELDS_NORMAL => $fieldsNormal,
        	self::FIELDS_ONOFF => $fieldsOnOff
        );
        
        foreach($translations->get() as $index => $symbol)
        {
        	$payloadName = $this->_payloads->makeTranslationName($symbol);
        	$fields[$payloadName] = array(
        		self::FIELDS_NORMAL => array(
        			'titleLang'.$index => '',
        			'teaserLang'.$index => ''
        		),
        		self::FIELDS_ONOFF => array()
        	);
        }
        #Sitengine_Debug::print_r($fields);
        return $fields[$this->_payloads->getName()];
    }
    
    
    
    protected function _checkQuotePostInput()
    {
    	$table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
        $translations = $table->getTranslations();
        
        if($this->_payloads->isMain())
        {
			$name = 'gid';
			if($this->_controller->getRequest()->getPost($name)==Sitengine_Blog_Backend_Blogs_Posts_Controller::VALUE_NONESELECTED) {
				$message = $this->_controller->getTranslate()->translate('quoteposthintsGidRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
        }
        if(
        	$this->_payloads->isMain() ||
        	$this->_payloads->isDefaultTranslation()
        )
        {
        	$name = 'teaserLang'.$translations->getDefaultIndex();
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getTranslate()->translate('quoteposthintsTeaserRequired');
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
            
            $data = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getDefaultPermissionData(
        		$this->_controller->getPermiso(),
        		$this->_controller->getFrontController()->getBlogPackage()->getOwnerGroup()
        	);
            
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
            ##$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
            #Sitengine_Debug::print_r($data);
            $affectedRows = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->insert($data);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Posts_Exception('insert error', $exception);
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
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            if($this->_controller->getRequest()->getPost('mdate') != $stored['mdate']) {
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_DATA_EXPIRED);
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
            $affectedRows = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->update($data, $where);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Posts_Exception('update error', $exception);
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    ########################################################################
    ### LINK POSTS
    ########################################################################
    protected function _getLinkPostFields()
    {
    	$table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
        $translations = $table->getTranslations();
        
    	$fieldsNormal = array(
            ##Sitengine_Permiso::FIELD_UID => '',
            ##Sitengine_Permiso::FIELD_GID => '',
            'titleLang'.$translations->getDefaultIndex() => '',
            'teaserLang'.$translations->getDefaultIndex() => '',
            'url' => ''
        );
        
        $fieldsOnOff = array(
            /*
            #Sitengine_Permiso::FIELD_RAG => 0,
            #Sitengine_Permiso::FIELD_RAW => 0,
            #Sitengine_Permiso::FIELD_UAG => 0,
            #Sitengine_Permiso::FIELD_UAW => 0,
            #Sitengine_Permiso::FIELD_DAG => 0,
            #Sitengine_Permiso::FIELD_DAW => 0,
            */
            'publish' => 0
        );
        
        $fields[$this->_payloads->getMainName()] = array(
        	self::FIELDS_NORMAL => $fieldsNormal,
        	self::FIELDS_ONOFF => $fieldsOnOff
        );
        
        foreach($translations->get() as $index => $symbol)
        {
        	$payloadName = $this->_payloads->makeTranslationName($symbol);
        	$fields[$payloadName] = array(
        		self::FIELDS_NORMAL => array(
        			'titleLang'.$index => '',
        			'teaserLang'.$index => ''
        		),
        		self::FIELDS_ONOFF => array()
        	);
        }
        #Sitengine_Debug::print_r($fields);
        return $fields[$this->_payloads->getName()];
    }
    
    
    
    protected function _checkLinkPostInput()
    {
    	$table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
        $translations = $table->getTranslations();
        
        if($this->_payloads->isMain())
        {
			$name = 'gid';
			if($this->_controller->getRequest()->getPost($name)==Sitengine_Blog_Backend_Blogs_Posts_Controller::VALUE_NONESELECTED) {
				$message = $this->_controller->getTranslate()->translate('linkposthintsGidRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
        }
        if(
        	$this->_payloads->isMain() ||
        	$this->_payloads->isDefaultTranslation()
        )
        {
        	$name = 'titleLang'.$translations->getDefaultIndex();
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getTranslate()->translate('linkposthintsTitleRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			
			$name = 'url';
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getTranslate()->translate('linkposthintsUrlRequired');
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
            
            $data = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getDefaultPermissionData(
        		$this->_controller->getPermiso(),
        		$this->_controller->getFrontController()->getBlogPackage()->getOwnerGroup()
        	);
            
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
            ##$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
            #Sitengine_Debug::print_r($data);
            $affectedRows = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->insert($data);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Posts_Exception('insert error', $exception);
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
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            if($this->_controller->getRequest()->getPost('mdate') != $stored['mdate']) {
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_DATA_EXPIRED);
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
            $affectedRows = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->update($data, $where);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Posts_Exception('update error', $exception);
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    ########################################################################
    ### AUDIO POSTS
    ########################################################################
    protected function _getAudioPostFields()
    {
    	$table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
        $translations = $table->getTranslations();
        
    	$fieldsNormal = array(
            ##Sitengine_Permiso::FIELD_UID => '',
            ##Sitengine_Permiso::FIELD_GID => '',
            'titleLang'.$translations->getDefaultIndex() => '',
            'markupLang'.$translations->getDefaultIndex() => ''
        );
        
        $fieldsOnOff = array(
            /*
            #Sitengine_Permiso::FIELD_RAG => 0,
            #Sitengine_Permiso::FIELD_RAW => 0,
            #Sitengine_Permiso::FIELD_UAG => 0,
            #Sitengine_Permiso::FIELD_UAW => 0,
            #Sitengine_Permiso::FIELD_DAG => 0,
            #Sitengine_Permiso::FIELD_DAW => 0,
            */
            'publish' => 0
        );
        
        $fields[$this->_payloads->getMainName()] = array(
        	self::FIELDS_NORMAL => $fieldsNormal,
        	self::FIELDS_ONOFF => $fieldsOnOff
        );
        
        foreach($translations->get() as $index => $symbol)
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
    
    
    
    protected function _checkAudioPostInput()
    {
    	$table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
        $translations = $table->getTranslations();
        
        if($this->_payloads->isMain())
        {
			$name = 'gid';
			if($this->_controller->getRequest()->getPost($name)==Sitengine_Blog_Backend_Blogs_Posts_Controller::VALUE_NONESELECTED) {
				$message = $this->_controller->getTranslate()->translate('audioposthintsGidRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			
			$name = 'titleLang'.$translations->getDefaultIndex();
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getTranslate()->translate('audioposthintsTitleRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			
			$fileId = 'file1Original';
			$upload = new Sitengine_Upload($fileId);
			
			
			#### EMAIL ###################################
			$subject = 'New Audio Post on '.$_SERVER['SERVER_NAME'];
			$body  = 'Mime: '.$upload->getMime()."\n";
			$body .= 'Size: '.round(($upload->getSize() / 1024 / 1024),2)."MB\n";
			$body .= 'Name: '.$upload->getName()."\n";
			
			if(
				$this->_controller->getEnv()->getModeratorSenderMail() === null ||
				sizeof($this->_controller->getEnv()->getModeratorMails()) == 0
			)
			{
				require_once 'Sitengine/Blog/Backend/Blogs/Posts/Exception.php';
            	throw new Sitengine_Blog_Backend_Blogs_Posts_Exception('administrator sender/recipients not set in config');
			}
			
			require_once 'Zend/Mail.php';
			$mail = new Zend_Mail();
			
			foreach($this->_controller->getEnv()->getAdministratorMails() as $address)
			{
				$mail->addTo($address);
			}
			/*
			$mail
				->setSubject($subject)
				->setBodyText($body)
				->setFrom($this->_controller->getEnv()->getAdministratorSenderMail(), 'chrigu')
				->send()
			;
			*/
			#### EMAIL ###################################
			
			
			if($upload->isFile())
			{
				$messages = array();
				
				if(!preg_match('/(mp3|mpg|mpeg)/i', $upload->getMime())) {
					$messages[] = $this->_controller->getTranslate()->translate('audioposthintsFile1OriginalFiletype');
				}
				if($upload->getSize() > 1024*1024*60) {
					$messages[] = $this->_controller->getTranslate()->translate('audioposthintsFile1OriginalFilesize');
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
            
            $data = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getDefaultPermissionData(
        		$this->_controller->getPermiso(),
        		$this->_controller->getFrontController()->getBlogPackage()->getOwnerGroup()
        	);
            
            $name = 'file1Original';
			$upload = new Sitengine_Upload($name);
			
            if(!$upload->isFile()) {
				$message = $this->_controller->getTranslate()->translate('audioposthintsFile1OriginalRequired');
				$this->_controller->getStatus()->addHint($name, $message);
				return null;
			}
            
            if(!$this->_checkAudioPostInput()) { return null; }
            
            $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->handleInsertUploads($id);
            
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
            ##$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
            $data = array_merge($data, $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getFileData());
            #Sitengine_Debug::print_r($data);
            $affectedRows = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->insert($data);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Posts_Exception('insert error', $exception);
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
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            if($this->_controller->getRequest()->getPost('mdate') != $stored['mdate']) {
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_DATA_EXPIRED);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            
            if(!$this->_checkAudioPostInput()) { return null; }
            
            $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->handleUpdateUploads($id, $stored);
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
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['mdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            $data = array_merge($data, $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getFileData());
            unset($data['id']);
            #Sitengine_Debug::print_r($data);
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id)
            );
            require_once 'Sitengine/Sql.php';
    		$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            $affectedRows = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->update($data, $where);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Posts_Exception('update error', $exception);
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    ########################################################################
    ### VIDEO POSTS
    ########################################################################
    protected function _getVideoPostFields()
    {
    	$table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
        $translations = $table->getTranslations();
        
    	$fieldsNormal = array(
            ##Sitengine_Permiso::FIELD_UID => '',
            ##Sitengine_Permiso::FIELD_GID => '',
            'embedTag' => '',
            'titleLang'.$translations->getDefaultIndex() => '',
            'teaserLang'.$translations->getDefaultIndex() => ''
        );
        
        $fieldsOnOff = array(
            /*
            #Sitengine_Permiso::FIELD_RAG => 0,
            #Sitengine_Permiso::FIELD_RAW => 0,
            #Sitengine_Permiso::FIELD_UAG => 0,
            #Sitengine_Permiso::FIELD_UAW => 0,
            #Sitengine_Permiso::FIELD_DAG => 0,
            #Sitengine_Permiso::FIELD_DAW => 0,
            */
            'publish' => 0
        );
        
        $fields[$this->_payloads->getMainName()] = array(
        	self::FIELDS_NORMAL => $fieldsNormal,
        	self::FIELDS_ONOFF => $fieldsOnOff
        );
        
        foreach($translations->get() as $index => $symbol)
        {
        	$payloadName = $this->_payloads->makeTranslationName($symbol);
        	$fields[$payloadName] = array(
        		self::FIELDS_NORMAL => array(
        			'titleLang'.$index => '',
        			'teaserLang'.$index => ''
        		),
        		self::FIELDS_ONOFF => array()
        	);
        }
        #Sitengine_Debug::print_r($fields);
        return $fields[$this->_payloads->getName()];
    }
    
    
    
    protected function _checkVideoPostInput()
    {
    	$table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
        $translations = $table->getTranslations();
        
        if($this->_payloads->isMain())
        {
			$name = 'gid';
			if($this->_controller->getRequest()->getPost($name)==Sitengine_Blog_Backend_Blogs_Posts_Controller::VALUE_NONESELECTED) {
				$message = $this->_controller->getTranslate()->translate('videoposthintsGidRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			
			$name = 'embedTag';
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getTranslate()->translate('videoposthintsEmbedTagRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
        }
        if(
        	$this->_payloads->isMain() ||
        	$this->_payloads->isDefaultTranslation()
        )
        {
        	$name = 'titleLang'.$translations->getDefaultIndex();
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getTranslate()->translate('videoposthintsTitleRequired');
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
            
            $data = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getDefaultPermissionData(
        		$this->_controller->getPermiso(),
        		$this->_controller->getFrontController()->getBlogPackage()->getOwnerGroup()
        	);
            
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
            ##$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
            #Sitengine_Debug::print_r($data);
            $affectedRows = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->insert($data);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Posts_Exception('insert error', $exception);
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
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            if($this->_controller->getRequest()->getPost('mdate') != $stored['mdate']) {
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_DATA_EXPIRED);
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
            $affectedRows = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->update($data, $where);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Posts_Exception('update error', $exception);
        }
    }
    
    
    
    
    
    
    
    public function delete($id)
    {
        try {
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id),
            	$this->_controller->getPermiso()->getDac()->getDeleteAccessSql($this->_controller->getFrontController()->getBlogPackage()->getAuthorizedGroups(), '', false)
            );
            require_once 'Sitengine/Sql.php';
			$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            return $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->delete($where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Posts_Exception('delete error', $exception);
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
            return $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->update($data, $where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Posts_Exception('update from list error', $exception);
        }
    }
    
}
?>