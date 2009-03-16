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



abstract class Sitengine_Blog_Backend_Blogs_Posts_Comments_Entity
{
    
    protected $_controller = null;
    protected $_started = false;
    protected $_blogRow = null;
    protected $_postRow = null;
    protected $_row = null;
    
    
    
    public function __construct(Sitengine_Blog_Backend_Blogs_Posts_Comments_Controller $controller)
    {
        $this->_controller = $controller;
    }
    
    
    
    public function getRow()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Backend/Blogs/Posts/Comments/Exception.php';
    		throw new Sitengine_Blog_Backend_Blogs_Posts_Comments_Exception('entity not started');
    	}
        return $this->_row;
    }
    
    
    
    public function refresh(array $updatedData)
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Backend/Blogs/Posts/Comments/Exception.php';
    		throw new Sitengine_Blog_Backend_Blogs_Posts_Comments_Exception('entity not started');
    	}
        foreach($updatedData as $field => $val)
    	{
    		$this->_row->$field = $val;
    	}
    }
    
    
    
    public function getAncestorId()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Backend/Blogs/Posts/Comments/Exception.php';
    		throw new Sitengine_Blog_Backend_Blogs_Posts_Comments_Exception('entity not started');
    	}
        return $this->_postRow->id;
    }
    
    
    /*
    public function getGreatAncestorId()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Backend/Blogs/Posts/Comments/Exception.php';
    		throw new Sitengine_Blog_Backend_Blogs_Posts_Comments_Exception('entity not started');
    	}
        return $this->_blogRow->id;
    }
    */
    
    
    public function getGreatAncestorSlug()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Backend/Blogs/Posts/Comments/Exception.php';
    		throw new Sitengine_Blog_Backend_Blogs_Posts_Comments_Exception('entity not started');
    	}
        return $this->_blogRow->slug;
    }
    
    
    /*
    public function getAncestorRow()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Backend/Blogs/Posts/Comments/Exception.php';
    		throw new Sitengine_Blog_Backend_Blogs_Posts_Comments_Exception('entity not started');
    	}
        return $this->_postRow;
    }
    */
    
    
    public function getAncestorType()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Backend/Blogs/Posts/Comments/Exception.php';
    		throw new Sitengine_Blog_Backend_Blogs_Posts_Comments_Exception('entity not started');
    	}
        return $this->_postRow->type;
    }
    
    
    
    public function getBreadcrumbs()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Backend/Blogs/Posts/Comments/Exception.php';
    		throw new Sitengine_Blog_Backend_Blogs_Posts_Comments_Exception('entity not started');
    	}
        return array(
            'blog' => $this->_blogRow,
            'post' => $this->_postRow,
            'comment' => $this->_row
        );
    }
    
    
    
    public function start()
    {
        try {
            if($this->_started) { return true; }
            else { $this->_started = true; }
            
            $id = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ID);
            $aid = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ANCESTORID);
            $gaid = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_GREATANCESTORID);
            
            if(!$id) { $this->_row = null; }
            else {
            	$table = $this->_controller->getFrontController()->getBlogPackage()->getCommentsTable();
				$select = $table->select()->where('id = ?', $id);
				$row = $table->fetchRow($select);
				if($row !== null) { $this->_row = $row; }
				else  {
					require_once 'Zend/Log.php';
                    require_once 'Sitengine/Blog/Backend/Blogs/Posts/Comments/Exception.php';
                    throw new Sitengine_Blog_Backend_Blogs_Posts_Comments_Exception(
                        'resource not found',
                        Sitengine_Env::ERROR_NOT_FOUND,
						null,
						Zend_Log::INFO
                    );
                }
                if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_row->toArray())) { return false; }
            }
            
            
            $table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
			$select = $table->select()->where('id = ?', $aid);
			$row = $table->fetchRow($select);
			if($row !== null) { $this->_postRow = $row; }
            else  {
            	require_once 'Zend/Log.php';
                require_once 'Sitengine/Blog/Backend/Blogs/Posts/Comments/Exception.php';
                throw new Sitengine_Blog_Backend_Blogs_Posts_Comments_Exception(
                    'resource not found',
                    Sitengine_Env::ERROR_NOT_FOUND,
                    null,
                    Zend_Log::INFO
                );
            }
            if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_postRow->toArray())) { return false; }
            
            
            $table = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTable();
			$select = $table->select()->where('id = ?', $gaid)->orWhere('slug = ?', $gaid);
			$row = $table->fetchRow($select);
			if($row !== null) { $this->_blogRow = $row; }
            else  {
            	require_once 'Zend/Log.php';
                require_once 'Sitengine/Blog/Backend/Blogs/Posts/Comments/Exception.php';
                throw new Sitengine_Blog_Backend_Blogs_Posts_Comments_Exception(
                    'resource not found',
                    Sitengine_Env::ERROR_NOT_FOUND,
                    null,
                    Zend_Log::INFO
                );
            }
            #if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_blogRow->toArray())) { return false; }
            return true;
        }
        catch (Sitengine_Blog_Backend_Blogs_Posts_Comments_Exception $exception) {
            throw $exception;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Posts/Comments/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Posts_Comments_Exception('start entity error', $exception);
        }
    }
    
}
?>