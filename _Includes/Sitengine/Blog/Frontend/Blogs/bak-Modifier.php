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
require_once 'Sitengine/Blog/Blog.php';


abstract class Sitengine_Blog_Frontend_Blogs_Modifier
{
    
    const FIELDS_NORMAL = 'normal';
    const FIELDS_ONOFF = 'onOff';
    
    protected $_controller = null;
    protected $_payloads = null;
    
    
    public function __construct(Sitengine_Blog_Frontend_Blogs_Controller $controller)
    {
        $this->_controller = $controller;
        
        require_once 'Sitengine/Form/TranslationPayloads.php';
        $this->_payloads = new Sitengine_Form_TranslationPayloads($this->_controller->getRecord()->getTranslations());
    }
    
    
    
    protected function _getFields()
    {
    	$fieldsNormal = array(
            #Sitengine_Permiso::FIELD_UID => '',
            #Sitengine_Permiso::FIELD_GID => '',
            'slug' => '',
            'titleLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex() => ''
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
        
        return $fields[$this->_payloads->getName()];
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
            
            $date = new Zend_Date();
            $date->setTimezone('UTC');
            $data['cdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            $data['mdate'] = $data['cdate'];
            $data['id'] = $id;
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
            require_once 'Sitengine/Blog/Frontend/Blogs/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Exception('insert error', $exception);
        }
    }
    
    
    
    
    public function update(array $stored)
    {
        try {
        	$this->_payloads->start($this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME));
        	$fields = $this->_getFields();
            $input = $this->_controller->getRequest()->getPost(null);
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
            
            if(!$this->_checkInput()) { return null; }
            
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
            
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($stored['id'])
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
            require_once 'Sitengine/Blog/Frontend/Blogs/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Exception('update error', $exception);
        }
    }
    
    
    
    
    
    protected function _checkInput()
    {
		$name = 'titleLang'.$this->_controller->getRecord()->getTranslations()->getDefaultIndex();
		if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
			$message = $this->_controller->getDictionary()->getFromHints('titleRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		
		$name = 'gid';
		if($this->_controller->getRequest()->getPost($name)==Sitengine_Blog_Frontend_Blogs_Controller::VALUE_NONESELECTED) {
			$message = $this->_controller->getDictionary()->getFromHints('gidRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		
		$name = 'slug';
		if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name), Sitengine_Blog_Frontend_Blogs_Controller::VALUE_NONESELECTED)) {
			$message = $this->_controller->getDictionary()->getFromHints('slugRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		
		$name = 'slug';
		if(!Sitengine_Validator::word($this->_controller->getRequest()->getPost($name))) {
			$message = $this->_controller->getDictionary()->getFromHints('slugInvalid');
			$this->_controller->getStatus()->addHint($name, $message);
		}
        return (!$this->_controller->getStatus()->hasHints());
    }
    
    
    
    
    
    public function delete(
        $id,
        Sitengine_Blog_Frontend_Blogs_Posts_Record $postRecord,
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
            $blog = $this->_controller->getRecord()->selectRowAndFiles($where);
            
            $deleted += $this->_controller->getRecord()->deleteRowAndFiles($blog);
            
            $whereClauses = array(
            	'blogId = '.$this->_controller->getDatabase()->quote($id)
            );
            require_once 'Sitengine/Sql.php';
			$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            $posts = $postRecord->selectRowsAndFiles($where);
            foreach($posts as $postRow)
            {
                $deleted += $postRecord->deleteRowAndFiles($postRow);
                
                $whereClauses = array(
					'parentId = '.$this->_controller->getDatabase()->quote($postRow['id'])
				);
				require_once 'Sitengine/Sql.php';
				$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
				$comments = $commentRecord->selectRowsAndFiles($where);
                foreach($comments as $commentRow)
                {
                    $deleted += $commentRecord->deleteRowAndFiles($commentRow);
                }
                
                $whereClauses = array(
					'parentId = '.$this->_controller->getDatabase()->quote($postRow['id'])
				);
				require_once 'Sitengine/Sql.php';
				$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
				$files = $fileRecord->selectRowsAndFiles($where);
                foreach($files as $fileRow)
                {
                    $deleted += $fileRecord->deleteRowAndFiles($fileRow);
                }
            }
            return $deleted;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Exception('delete error', $exception);
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
            	$this->_controller->getPermiso()->getDac()->getUpdateAccessSql($this->_controller->getFrontController()->getBlogPackage()->getAuthorizedGroups(), '', false)
            );
            require_once 'Sitengine/Sql.php';
    		$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            return $this->_controller->getRecord()->update($data, $where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Exception('update from list error', $exception);
        }
    }
    
}
?>