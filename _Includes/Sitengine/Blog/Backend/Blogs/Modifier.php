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


abstract class Sitengine_Blog_Backend_Blogs_Modifier
{
    
    const FIELDS_NORMAL = 'normal';
    const FIELDS_ONOFF = 'onOff';
    
    protected $_controller = null;
    protected $_payloads = null;
    
    
    public function __construct(Sitengine_Blog_Backend_Blogs_Controller $controller)
    {
        $this->_controller = $controller;
        
        $table = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTable();
        $translations = $table->getTranslations();
        require_once 'Sitengine/Form/TranslationPayloads.php';
        $this->_payloads = new Sitengine_Form_TranslationPayloads($translations);
    }
    
    
    
    protected function _getFields()
    {
    	$table = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTable();
        $translations = $table->getTranslations();
        
    	$fieldsNormal = array(
            ##Sitengine_Permiso::FIELD_UID => '',
            ##Sitengine_Permiso::FIELD_GID => '',
            'slug' => '',
            'titleLang'.$translations->getDefaultIndex() => ''
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
        
        return $fields[$this->_payloads->getName()];
    }
    
    
    
    
    public function insert()
    {
        try {
        	$this->_payloads->start();
        	$fields = $this->_getFields();
        	$input = $this->_controller->getRequest()->getPost(null);
        	$id = Sitengine_String::createId();
            
            $data = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTable()->getDefaultPermissionData(
        		$this->_controller->getPermiso(),
        		$this->_controller->getFrontController()->getBlogPackage()->getOwnerGroup()
        	);
            
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
            ##$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
            #Sitengine_Debug::print_r($data);
            $insertId = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTable()->insert($data);
            if(!$insertId)
            {
            	$error = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Exception('insert error', $exception);
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
            
            $where = $this->_controller->getDatabase()->quoteInto('id = ?', $stored['id']);
            $affectedRows = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTable()->update($data, $where);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Exception('update error', $exception);
        }
    }
    
    
    
    
    
    protected function _checkInput()
    {
    	$table = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTable();
        $translations = $table->getTranslations();
        
		$name = 'titleLang'.$translations->getDefaultIndex();
		if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
			$message = $this->_controller->getTranslate()->translate('hintsTitleRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		
		$name = 'gid';
		if($this->_controller->getRequest()->getPost($name)==Sitengine_Blog_Backend_Blogs_Controller::VALUE_NONESELECTED) {
			$message = $this->_controller->getTranslate()->translate('hintsGidRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		
		$name = 'slug';
		if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name), Sitengine_Blog_Backend_Blogs_Controller::VALUE_NONESELECTED)) {
			$message = $this->_controller->getTranslate()->translate('hintsSlugRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		
		$name = 'slug';
		if(!Sitengine_Validator::word($this->_controller->getRequest()->getPost($name))) {
			$message = $this->_controller->getTranslate()->translate('hintsSlugInvalid');
			$this->_controller->getStatus()->addHint($name, $message);
		}
        return (!$this->_controller->getStatus()->hasHints());
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
            return $this->_controller->getFrontController()->getBlogPackage()->getBlogsTable()->delete($where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Exception('delete error', $exception);
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
            return $this->_controller->getFrontController()->getBlogPackage()->getBlogsTable()->update($data, $where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Exception('update from list error', $exception);
        }
    }
    
}
?>