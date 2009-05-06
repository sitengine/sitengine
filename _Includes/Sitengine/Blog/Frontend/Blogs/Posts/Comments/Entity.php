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



abstract class Sitengine_Blog_Frontend_Blogs_Posts_Comments_Entity
{
    
    protected $_controller = null;
    protected $_started = false;
    protected $_blogRow = null;
    protected $_postRow = null;
    protected $_row = null;
    
    
    
    public function __construct(Sitengine_Blog_Frontend_Blogs_Posts_Comments_Controller $controller)
    {
        $this->_controller = $controller;
    }
    
    
    
    public function getRow()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('entity not started');
    	}
        return $this->_row;
    }
    
    
    
    public function refresh(array $updatedData)
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('entity not started');
    	}
        foreach($updatedData as $field => $val)
    	{
    		$this->_row->$field = $val;
    	}
    }
    
    
    
    public function getAncestorId()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('entity not started');
    	}
        return $this->_postRow->id;
    }
    
    
    
    public function getGreatAncestorId()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('entity not started');
    	}
        return $this->_blogRow->id;
    }
    
    
    
    public function getGreatAncestorSlug()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('entity not started');
    	}
        return $this->_blogRow->slug;
    }
    
    
    
    public function getAncestorData()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('entity not started');
    	}
        return $this->_postRow;
    }
    
    
    
    public function getAncestorType()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('entity not started');
    	}
        return $this->_postRow->type;
    }
    
    
    
    public function getBreadcrumbs()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('entity not started');
    	}
        return array(
            'blog' => $this->_blogRow,
            'post' => $this->_postRow,
            'comment' => $this->_row
        );
    }
    
    
    
    public function getBreadcrumbsData()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('entity not started');
    	}
        return array(
            'blog' => $this->_blogRow->toArray(),
            'post' => $this->_postRow->toArray(),
            'comment' => ($this->_row !== null) ? $this->_row->toArray() : null
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
				$select = $table->select()->where('id = ?', $id)->where('approve = "1"');
				$row = $table->fetchRow($select);
				if($row !== null) { $this->_row = $row; }
				else  {
					require_once 'Zend/Log.php';
                    require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
                    throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception(
                        'resource not found',
                        Sitengine_Env::ERROR_NOT_FOUND,
						null,
						Zend_Log::INFO
                    );
                }
                #if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_row->toArray())) { return false; }
            }
            
            
            $table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
			$select = $table->select()->where('id = ?', $aid)->where('publish = "1"');
			$row = $table->fetchRow($select);
			if($row !== null) { $this->_postRow = $row; }
            else  {
            	require_once 'Zend/Log.php';
                require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
                throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception(
                    'resource not found',
                    Sitengine_Env::ERROR_NOT_FOUND,
                    null,
                    Zend_Log::INFO
                );
            }
            #if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_postRow->toArray())) { return false; }
            
            
            $table = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTable();
			$select = $table->select()->where('id = ?', $gaid)->orWhere('slug = ?', $gaid)->where('publish = "1"');
			$row = $table->fetchRow($select);
			if($row !== null) { $this->_blogRow = $row; }
            else {
            	require_once 'Zend/Log.php';
                require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
                throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception(
                    'resource not found',
                    Sitengine_Env::ERROR_NOT_FOUND,
                    null,
                    Zend_Log::INFO
                );
            }
            #if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_blogRow->toArray())) { return false; }
            return true;
        }
        catch (Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception $exception) {
            throw $exception;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('start entity error', $exception);
        }
    }
    
    /*
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
            	require_once 'Sitengine/Blog/Comments.php';
				$commentsObj = new Sitengine_Blog_Comments(
					$this->_controller->getDatabase(),
					$this->_controller->getFrontController()->getBlogPackage(),
					$this->_controller->getPermiso()
				);
				$comments = $this->_controller->getFrontController()->getBlogPackage()->getCommentsTableName();
				$whereClauses = array(
					"$comments.approve = '1'",
					"$comments.id = ".$this->_controller->getDatabase()->quote($id)
				);
				$items = $commentsObj->get($whereClauses, '', 1, 0);
				if(sizeof($items)) { $this->_row = $items[0]->getData(); }
				else  {
                    require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
                    throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception(
                        'resource not found',
                        Sitengine_Env::ERROR_NOT_FOUND
                    );
                }
                if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_row)) { return false; }
            }
            
            
            require_once 'Sitengine/Blog/Posts.php';
			$postsObj = new Sitengine_Blog_Posts(
				$this->_controller->getDatabase(),
				$this->_controller->getFrontController()->getBlogPackage()
			);
			$postsObj->setTranscript($this->_controller->getPreferences()->getLanguage());
			$posts = $this->_controller->getFrontController()->getBlogPackage()->getPostsTableName();
			$whereClauses = array(
				"$posts.publish = '1'",
				"$posts.id = ".$this->_controller->getDatabase()->quote($aid)
			);
			$items = $postsObj->get($whereClauses, '', 1, 0);
			$config = $this->_controller->getEnv()->getAmazonConfig('default');
			
			if(sizeof($items)) { $this->_postRow = $items[0]->getData($config); }
            else  {
                require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
                throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception(
                    'resource not found',
                    Sitengine_Env::ERROR_NOT_FOUND
                );
            }
            if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_postRow)) { return false; }
            
            
            require_once 'Sitengine/Blog/Blogs.php';
        	$blogsObj = new Sitengine_Blog_Blogs(
        		$this->_controller->getDatabase(),
        		$this->_controller->getFrontController()->getBlogPackage()
        	);
        	$blogsObj->setTranscript($this->_controller->getPreferences()->getLanguage());
        	$blogs = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTableName();
        	$whereClauses = array(
        		"$blogs.publish = '1'",
        		"($blogs.id = ".$this->_controller->getDatabase()->quote($gaid)." OR $blogs.slug = ".$this->_controller->getDatabase()->quote($gaid).")"
        	);
        	$items = $blogsObj->get($whereClauses, '', 1, 0);
        	if(sizeof($items)) { $this->_blogRow = $items[0]->getData(); }
            else  {
                require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
                throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception(
                    'resource not found',
                    Sitengine_Env::ERROR_NOT_FOUND
                );
            }
            if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_blogRow)) { return false; }
            return true;
        }
        catch (Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception $exception) {
            throw $exception;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('start entity error', $exception);
        }
    }
    */
}
?>