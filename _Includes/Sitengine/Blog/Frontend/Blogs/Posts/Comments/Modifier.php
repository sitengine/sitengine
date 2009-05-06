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


abstract class Sitengine_Blog_Frontend_Blogs_Posts_Comments_Modifier
{
    
    const FIELDS_NORMAL = 'normal';
    const FIELDS_ONOFF = 'onOff';
    
    protected $_controller = null;
    
    
    public function __construct(Sitengine_Blog_Frontend_Blogs_Posts_Comments_Controller $controller)
    {
        $this->_controller = $controller;
    }
    
    
    
    protected function _getFields()
    {
    	$fieldsNormal = array(
            ##Sitengine_Permiso::FIELD_UID => '',
            ##Sitengine_Permiso::FIELD_GID => '',
            'comment' => ''
        );
        
        $fieldsOnOff = array(
        	/*
            #Sitengine_Permiso::FIELD_RAG => 0,
            #Sitengine_Permiso::FIELD_RAW => 0,
            #Sitengine_Permiso::FIELD_UAG => 0,
            #Sitengine_Permiso::FIELD_UAW => 0,
            #Sitengine_Permiso::FIELD_DAG => 0,
            #Sitengine_Permiso::FIELD_DAW => 0,
            'approve' => 0
            */
        );
        
        $fields = array(
        	self::FIELDS_NORMAL => $fieldsNormal,
        	self::FIELDS_ONOFF => $fieldsOnOff
        );
        
        return $fields;
    }
    
    
    
    
    public function insert()
    {
        try {
        	$parentId = $this->_controller->getEntity()->getAncestorId();
        	$fields = $this->_getFields();
        	$input = $this->_controller->getRequest()->getPost(null);
        	$id = Sitengine_String::createId();
            
            $data = $this->_controller->getFrontController()->getBlogPackage()->getCommentsTable()->getDefaultPermissionData(
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
            $data['id'] = $id;
            $data['parentId']= $parentId;
            
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['cdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
			$data['mdate'] = $data['cdate'];
			$data['origin'] = $this->_controller->getFrontController()->getBlogPackage()->getName();
            #Sitengine_Debug::print_r($data);
            
			$data['approve'] = 1;
            $insertId = $this->_controller->getFrontController()->getBlogPackage()->getCommentsTable()->insertOrRollback($data);
            if(!$insertId)
            {
            	$error = $this->_controller->getFrontController()->getBlogPackage()->getCommentsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('insert error', $exception);
        }
    }
    
    
    
    
    
    
    public function delete()
    {
        try {
        	if($this->_controller->getEntity()->getRow()->uid != $this->_controller->getPermiso()->getAuth()->getId())
        	{
        		return false;
        	}
        	
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($this->_controller->getEntity()->getRow()->id)
            );
            require_once 'Sitengine/Sql.php';
			$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            return $this->_controller->getFrontController()->getBlogPackage()->getCommentsTable()->delete($where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('delete error', $exception);
        }
    }
    
    
    
    /*
    public function update($id, array $stored)
    {
        try {
 
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
            
            # sanitize data
            foreach($fields[self::FIELDS_ONOFF] as $k => $v) {
                $data[$k] = (array_key_exists($k, $input)) ? 1 : 0;
            }
            foreach($fields[self::FIELDS_NORMAL] as $k => $v) {
                if(array_key_exists($k, $input)) { $data[$k] = $input[$k]; }
            }
            
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['mdate'] = $date->get(Zend_Date::ISO_8601, Sitengine_Env::LANGUAGE_EN);
			$data['mdate'] = preg_replace('/^(\d{4,4}-\d{2,2}-\d{2,2}).*(\d{2,2}:\d{2,2}:\d{2,2}).* /', "$1 $2", $data['mdate']);
            unset($data['id']);
            #Sitengine_Debug::print_r($data);
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id)
            );
            require_once 'Sitengine/Sql.php';
    		$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            $affectedRows = $this->_controller->getFrontController()->getBlogPackage()->getCommentsTable()->updateOrRollback($data, $where);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getBlogPackage()->getCommentsTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('update error', $exception);
        }
    }
    */
    
    
    
    protected function _checkInput()
    {
    	/*
		$name = 'gid';
		if($this->_controller->getRequest()->getPost($name)==Sitengine_Blog_Frontend_Blogs_Posts_Comments_Controller::VALUE_NONESELECTED) {
			$message = $this->_controller->getTranslate()->translate('hintsGidRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		*/
		$name = 'comment';
		$val = $this->_controller->getRequest()->getPost($name);
		if(Sitengine_Validator::nada($val, Sitengine_Blog_Frontend_Blogs_Posts_Comments_Controller::VALUE_NONESELECTED)) {
			$message = $this->_controller->getTranslate()->translate('hintsCommentRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		
		if(strip_tags($val) != $val)
		{
			$message = $this->_controller->getTranslate()->translate('hintsCommentContainsHtml');
			$this->_controller->getStatus()->addHint($name, $message);
		}
        return (!$this->_controller->getStatus()->hasHints());
    }
    
    
    /*
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
            $comment = $this->_controller->getFrontController()->getBlogPackage()->getCommentsTable()->selectRowAndFiles($where);
            
            return $this->_controller->getFrontController()->getBlogPackage()->getCommentsTable()->deleteRowAndFiles($comment);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('delete error', $exception);
        }
    }
    
    
    
    
    
    public function updateFromList($id, array $data)
    {
        try {
            # sanitize data
            foreach($data as $k => $v) {
                if(!preg_match('/^(approve)$/', $k)) {
                    unset($data[$k]);
                }
            }
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['mdate'] = $date->get(Zend_Date::ISO_8601, Sitengine_Env::LANGUAGE_EN);
			$data['mdate'] = preg_replace('/^(\d{4,4}-\d{2,2}-\d{2,2}).*(\d{2,2}:\d{2,2}:\d{2,2}).* /', "$1 $2", $data['mdate']);
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id),
            	$this->_controller->getPermiso()->getDac()->getUpdateAccessSql($this->_controller->getFrontController()->getBlogPackage()->getAuthorizedGroups(), '', false)
            );
            require_once 'Sitengine/Sql.php';
    		$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            return $this->_controller->getFrontController()->getBlogPackage()->getCommentsTable()->updateOrRollback($data, $where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('update from list error', $exception);
        }
    }
    */
}
?>