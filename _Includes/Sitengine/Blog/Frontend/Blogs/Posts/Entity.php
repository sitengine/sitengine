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


abstract class Sitengine_Blog_Frontend_Blogs_Posts_Entity
{
    
    protected $_controller = null;
    protected $_started = false;
    protected $_blogRow = null;
    protected $_row = null;
    
    
    public function __construct(Sitengine_Blog_Frontend_Blogs_Posts_Controller $controller)
    {
        $this->_controller = $controller;
    }
    
    
    
    public function getRow()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('entity not started');
    	}
        return $this->_row;
    }
    
    
    
    public function getAncestorId()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('entity not started');
    	}
    	return $this->_blogRow->id;
    }
    
    
    public function getId()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('entity not started');
    	}
    	return ($this->_row !== null) ? $this->_row->id : null;
    }
    
    
    public function getAncestorSlug()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('entity not started');
    	}
    	return $this->_blogRow->slug;
    }
    
    
    
    public function getBreadcrumbs()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('entity not started');
    	}
        return array(
            'blog' => $this->_blogRow,
            'post' => $this->_row
        );
    }
    
    
    public function getBreadcrumbsData()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('entity not started');
    	}
    	
        return array(
            'blog' => $this->_blogRow->toArray(),
            'post' => ($this->_row !== null) ? $this->_row->toArray() : null
        );
    }
    
    
    
    public function start()
    {
        try {
            if($this->_started) { return true; }
            else { $this->_started = true; }
            
            $id = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ID);
            $aid = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ANCESTORID);
            
            if(!$id) { $this->_row = null; }
            else {
            	/*
            	require_once 'Sitengine/Blog/Posts.php';
				$postsObj = new Sitengine_Blog_Posts(
					$this->_controller->getDatabase(),
					$this->_controller->getFrontController()->getBlogPackage()
				);
				$postsObj->setTranslation($this->_controller->getPreferences()->getLanguage());
				$posts = $this->_controller->getFrontController()->getBlogPackage()->getPostsTableName();
				$whereClauses = array(
					"$posts.publish = '1'",
					"$posts.id = ".$this->_controller->getDatabase()->quote($id)
				);
				$items = $postsObj->get($whereClauses, '', 1, 0);
				$config = $this->_controller->getEnv()->getAmazonConfig('default');
				
				if(sizeof($items)) { $this->_row = $items[0]->getData($config); }
				*/
				$table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
				$select = $table->select()->where('id = ?', $id)->where('publish = ?', 1);
				$row = $table->fetchRow($select);
				if($row !== null) { $this->_row = $row; }
                else {
                	require_once 'Zend/Log.php';
                    require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
                    throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception(
                        'resource not found',
                        Sitengine_Env::ERROR_NOT_FOUND,
						null,
						Zend_Log::INFO
                    );
                }
                #if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_row->toArray())) { return false; }
            }
            
            /*
            require_once 'Sitengine/Blog/Blogs.php';
        	$blogsObj = new Sitengine_Blog_Blogs(
        		$this->_controller->getDatabase(),
        		$this->_controller->getFrontController()->getBlogPackage()
        	);
        	$blogsObj->setTranslation($this->_controller->getPreferences()->getLanguage());
        	$blogs = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTableName();
        	$whereClauses = array(
        		"$blogs.publish = '1'",
        		"($blogs.id = ".$this->_controller->getDatabase()->quote($aid)." OR $blogs.slug = ".$this->_controller->getDatabase()->quote($aid).")"
        	);
        	$items = $blogsObj->get($whereClauses, '', 1, 0);
        	if(sizeof($items)) { $this->_blogRow = $items[0]->getData(); }
        	*/
        	$table = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTable();
			$select = $table->select()->where('publish = ?', 1)->where('id = ?', $aid)->orWhere('slug = ?', $aid);
			$row = $table->fetchRow($select);
			if($row !== null) { $this->_blogRow = $row; }
            else {
            	require_once 'Zend/Log.php';
                require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
                throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception(
                    'resource not found',
                    Sitengine_Env::ERROR_NOT_FOUND,
                    null,
                    Zend_Log::INFO
                );
            }
            #if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_blogRow->toArray())) { return false; }
            return true;
        }
        catch (Sitengine_Blog_Frontend_Blogs_Posts_Exception $exception) {
            throw $exception;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('start entity error', $exception);
        }
    }
}
?>