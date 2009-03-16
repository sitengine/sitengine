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


abstract class Sitengine_Blog_Backend_Blogs_Entity
{
	
	protected $_controller = null;
    protected $_started = false;
    protected $_row = null;
    
    
    public function __construct(Sitengine_Blog_Backend_Blogs_Controller $controller)
    {
        $this->_controller = $controller;
    }
    
    
    
    public function getId()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
    		throw new Sitengine_Blog_Backend_Blogs_Exception('entity not started');
    	}
        return ($this->_row !== null) ? $this->_row->id : null;
    }
    
    
    
    public function getSlug()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
    		throw new Sitengine_Blog_Backend_Blogs_Exception('entity not started');
    	}
        return ($this->_row !== null) ? $this->_row->slug : null;
    }
    
    
    
    public function getRow()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
    		throw new Sitengine_Blog_Backend_Blogs_Exception('entity not started');
    	}
        return $this->_row;
    }
    
    
    
    public function refresh(array $updatedData)
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
    		throw new Sitengine_Blog_Backend_Blogs_Exception('entity not started');
    	}
        foreach($updatedData as $field => $val)
    	{
    		$this->_row->$field = $val;
    	}
    }
    
    
    
    public function start()
    {
        try {
            if($this->_started) { return true; }
            else { $this->_started = true; }
            
            $id = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ID);
            $table = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTable();
            $select = $table->select()->where('id = ?', $id)->orWhere('slug = ?', $id);
        	$row = $table->fetchRow($select);
        	if($row !== null) { $this->_row = $row; }
			else {
				require_once 'Zend/Log.php';
				require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
				throw new Sitengine_Blog_Backend_Blogs_Exception(
                    'resource not found',
                    Sitengine_Env::ERROR_NOT_FOUND,
                    null,
                    Zend_Log::INFO
                );
			}
            if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_row->toArray())) { return false; }
            else { return true; }
        }
        catch (Sitengine_Blog_Backend_Blogs_Exception $exception) {
            throw $exception;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Exception('start entity error', $exception);
        }
    }
    
}
?>