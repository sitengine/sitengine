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


abstract class Sitengine_Blog_Frontend_Blogs_Entity
{
	
	protected $_controller = null;
    protected $_started = false;
    protected $_data = null;
    
    
    public function __construct(Sitengine_Blog_Frontend_Blogs_Controller $controller)
    {
        $this->_controller = $controller;
    }
    
    
    
    public function getId()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Exception('entity not started');
    	}
        return ($this->_data !== null) ? $this->_data['id'] : null;
    }
    
    
    
    public function getSlug()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Exception('entity not started');
    	}
        return ($this->_data !== null) ? $this->_data['slug'] : null;
    }
    
    
    
    public function getData()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Exception('entity not started');
    	}
        return $this->_data;
    }
    
    
    /*
    public function refreshData(array $updatedData)
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Exception('entity not started');
    	}
        $this->_data = array_merge($this->_data, $updatedData);
    }
    */
    
    
    public function start()
    {
        try {
            if($this->_started) { return true; }
            else { $this->_started = true; }
            
            $id = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ID);
            
            require_once 'Sitengine/Blog/Blogs.php';
        	$blogsObj = new Sitengine_Blog_Blogs(
        		$this->_controller->getDatabase(),
        		$this->_controller->getFrontController()->getBlogPackage()
        	);
        	$blogsObj->setTranscript($this->_controller->getPreferences()->getLanguage());
        	$blogs = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTableName();
        	$whereClauses = array(
        		"$blogs.id = ".$this->_controller->getDatabase()->quote($id)." OR $blogs.slug = ".$this->_controller->getDatabase()->quote($id)
        	);
        	$items = $blogsObj->get($whereClauses, '', 1, 0);
        	if(sizeof($items)) { $this->_data = $items[0]->getData(); }
			else {
				require_once 'Zend/Log.php';
				require_once 'Sitengine/Blog/Frontend/Blogs/Exception.php';
				throw new Sitengine_Blog_Frontend_Blogs_Exception(
                    'resource not found',
                    Sitengine_Env::ERROR_NOT_FOUND,
                    null,
                    Zend_Log::INFO
                );
			}
            if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_data)) { return false; }
            else { return true; }
        }
        catch (Sitengine_Blog_Frontend_Blogs_Exception $exception) {
            throw $exception;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Exception('start entity error', $exception);
        }
    }
    
}
?>