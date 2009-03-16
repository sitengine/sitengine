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



require_once 'Sitengine/View.php';


abstract class Sitengine_Blog_Frontend_Blogs_Posts_Comments_ViewHelper extends Sitengine_View {
    
       
	protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Blog_Frontend_Blogs_Posts_Comments_Controller)
    	{
    		$this->_controller = $config['controller'];
    	}
    	else {
    		require_once 'Sitengine/Exception.php';
        	throw new Sitengine_Exception('construct error');
    	}
    }
    
    
    public function setSection($name, array $data)
    {
        $this->_sections[$name] = $data;
    }
    
    
    public function getSections()
    {
        return $this->_sections;
    }
    
    
    public function getSettings()
    {
        return $this->_settings;
    }
    
    
    public function getQueries()
    {
        return $this->_queries;
    }
    
    
    public function build()
    {
    	try {
    		/*
    		$this->_queries = $this->_controller->getFrontController()->getQueries();
			
			$this->setSection(
				'GLOBALNAV',
				$this->_controller->getFrontController()->getGlobalNavSection(
					$this->_controller->getDictionary(),
					$this->_queries,
					'blogFrontendBlogs'
				)
			);
			*/
			
			
			if($this->_controller->getEnv()->getDebugControl()) {
				require_once 'Sitengine/Debug/Sections.php';
				$this->setSection(
					'DBG',
					Sitengine_Debug_Sections::getForm(
						$this->_controller->getRequest(),
						$this->_controller->getPreferences()->getDebugMode(),
						array('queries' => 'Queries', 'templateData' => 'Template Data'),
						'dbg'
					)
				);
			}
			
			require_once 'Sitengine/Env/Preferences/Sections.php';
			
			if(sizeof($this->_controller->getDictionary()->getAvailableLanguages()) > 1) {
				$this->setSection(
					'LANGUAGE',
					Sitengine_Env_Preferences_Sections::getLanguageForm(
						$this->_controller->getPreferences()->getLanguage(),
						$this->_controller->getDictionary()->getAvailableLanguages(),
						$this->_controller->getDictionary()->getLocLangs(),
						'language'
					)
				);
			}
			
			$this->setSection(
				'TIMEZONE',
				Sitengine_Env_Preferences_Sections::getTimezoneForm(
					$this->_controller->getPreferences()->getTimezone(),
					$this->_controller->getEnv()->getTimezones(),
					'timezone'
				)
			);
			
			$breadcrumbs = $this->_makeBreadcrumbsData();
			
			$this->setSection(
				'BREADCRUMBS',
				array(
					#'title' => $this->_controller->getDictionary()->getFromBreadcrumbs('title'),
					'RAW' => $this->_controller->getEntity()->getBreadcrumbsData(),
					'DATA' => $breadcrumbs
				)
			);
			
			$this->setSection(
				'ABSTRACT',
				array(
					'title' => $breadcrumbs['post']['title'],
					'uri' => $breadcrumbs['post']['uriUpdate'],
					#'help' => $this->_controller->getDictionary()->getFromHelps($this->_controller->getRequest()->getActionName())
				)
			);
			
			$args = array(
				Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_COMMENTS);
			$uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			$args = array(
				Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_COMMENTS);
			$uriInsert = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			$this->setSection(
				'ACTIONS',
				array(
					'index' => array(
						'uri' => $uriIndex,
						'label' => $this->_controller->getDictionary()->getFromLabels('actionsSectionIndex')
					),
					'insert' => array(
						'uri' => $uriInsert,
						'label' => $this->_controller->getDictionary()->getFromLabels('actionsSectionInsert')
					)
				)
			);
			return $this;
		}
		catch (Exception $exception) {
        	require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
        	throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('build page error', $exception);
        }
    }
    
    
    
    
    
    
    protected function _makeBreadcrumbsData()
    {
    	$data = array();
        $breadcrumbs = $this->_controller->getEntity()->getBreadcrumbs();
        $table = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTable();
        $table->setTranslation($this->_controller->getPreferences()->getTranslation());
        $blog = $table->complementRow($breadcrumbs['blog']);
        /*
        $args = array(
            Sitengine_Env::PARAM_ID => $this->_controller->getEntity()->getGreatAncestorSlug()
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_SHARP);
        $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        */
        $args = array();
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS);
        $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        $data['blog'] = array(
            'name' => $this->_controller->getDictionary()->getFromBreadcrumbs('blogEntity'),
            'uriIndex' => $uriIndex,
            'title' => $blog['title'],
            #'uriUpdate' => $uriUpdate
        );
        
        
        $table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
        $table->setTranslation($this->_controller->getPreferences()->getTranslation());
    	$post = $table->complementRow($breadcrumbs['post']);
        
        $args = array(
            Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
            Sitengine_Env::PARAM_ID => $post['id']
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_SHARP);
        $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        
        $args = array(
            Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug()
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS);
        $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        
        if($post['type'] == Sitengine_Blog_Posts_Table::TYPE_PHOTO) {
			$title = $post['file1OriginalSource'];
		}
		else if($post['type'] == Sitengine_Blog_Posts_Table::TYPE_QUOTE) {
			$title = $post['teaser'];
		}
		else if($post['type'] == Sitengine_Blog_Posts_Table::TYPE_AUDIO) {
			$title = $post['file1OriginalSource'];
		}
		else if($post['type'] == Sitengine_Blog_Posts_Table::TYPE_VIDEO) {
			$title = $post['title'];
		}
		else {
			$title = $post['title'];
		}
		
        $data['post'] = array(
            'name' => $this->_controller->getDictionary()->getFromBreadcrumbs('postEntity'),
            'uriIndex' => $uriIndex,
            'title' => $title,
            'uriUpdate' => $uriUpdate
        );
        
        
    	$table = $this->_controller->getFrontController()->getBlogPackage()->getCommentsTable();
        
        $args = array(
            Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
            Sitengine_Env::PARAM_ANCESTORID => $post['id']
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_COMMENTS);
        $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        
        $type = $this->_controller->getEntity()->getAncestorType();
		
        $level = array();
        $level['name'] = $this->_controller->getDictionary()->getFromBreadcrumbs('commentEntity');
        $level['uriIndex'] = $uriIndex;
        
        if($breadcrumbs['comment'])
        {
        	$comment = $breadcrumbs['comment']->toArray();
        	
            $args = array(
                Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
				Sitengine_Env::PARAM_ANCESTORID => $post['id'],
                Sitengine_Env::PARAM_ID => $comment['id']
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_COMMENTS_SHARP);
            $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            $level['title'] = $comment['comment'];
            $level['uriUpdate'] = $uriUpdate;
        }
        $data['comment'] = $level;
        return $data;
    }
    /*
    protected function _makeBreadcrumbsData()
    {
        $breadcrumbs = $this->_controller->getEntity()->getBreadcrumbsData();
        $data = array();
        
    	require_once 'Sitengine/Blog/Blog.php';
        $blog = new Sitengine_Blog_Blog(
        	$this->_controller->getDatabase(),
        	$this->_controller->getFrontController()->getBlogPackage()
        );
    	$translations = $blog->getTranslations();
    	$translations->setLanguage($this->_controller->getPreferences()->getLanguage());
        /*
        $args = array(
            Sitengine_Env::PARAM_ID => $this->_controller->getEntity()->getGreatAncestorSlug()
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_SHARP);
        $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        * /
        $args = array();
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS);
        $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        $title = $breadcrumbs['blog']['titleLang'.$translations->getIndex()];
        $title = ($title) ? $title : $breadcrumbs['blog']['titleLang'.$translations->getDefaultIndex()];
        $data['blog'] = array(
            'name' => $this->_controller->getDictionary()->getFromBreadcrumbs('blogEntity'),
            'uriIndex' => $uriIndex,
            #'title' => $title,
            #'uriUpdate' => $uriUpdate
        );
        
        
    	require_once 'Sitengine/Blog/Post.php';
        $post = new Sitengine_Blog_Post(
        	$this->_controller->getDatabase(),
        	$this->_controller->getFrontController()->getBlogPackage()
        );
    	$translations = $post->getTranslations();
    	$translations->setLanguage($this->_controller->getPreferences()->getLanguage());
    	
        $args = array(
            Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
            Sitengine_Env::PARAM_ID => $breadcrumbs['post']['id']
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_SHARP);
        $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        
        $args = array(
            Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug()
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS);
        $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        
        #$title = $breadcrumbs['post']['titleLang'.$translations->getIndex()];
        #$title = ($title) ? $title : $breadcrumbs['post']['titleLang'.$translations->getDefaultIndex()];
        
        if($breadcrumbs['post']['type'] == Sitengine_Blog_Posts_Table::TYPE_PHOTO) {
			$title = $breadcrumbs['post']['file1OriginalSource'];
		}
		else if($breadcrumbs['post']['type'] == Sitengine_Blog_Posts_Table::TYPE_QUOTE) {
			$title = $breadcrumbs['post']['markupLang'.$translations->getDefaultIndex()];
		}
		else if($breadcrumbs['post']['type'] == Sitengine_Blog_Posts_Table::TYPE_AUDIO) {
			$title = $breadcrumbs['post']['file1OriginalSource'];
		}
		else if($breadcrumbs['post']['type'] == Sitengine_Blog_Posts_Table::TYPE_VIDEO) {
			$title = $breadcrumbs['post']['titleLang'.$translations->getDefaultIndex()];
		}
		else {
			$title = $breadcrumbs['post']['titleLang'.$translations->getDefaultIndex()];
		}
		
        $data['post'] = array(
            'name' => $this->_controller->getDictionary()->getFromBreadcrumbs('postEntity'),
            'uriIndex' => $uriIndex,
            'title' => $title,
            'uriUpdate' => $uriUpdate
        );
        
        
        $args = array(
            Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
			Sitengine_Env::PARAM_ANCESTORID => $breadcrumbs['post']['id']
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_COMMENTS);
        $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        
        $name = $this->_controller->getDictionary()->getFromBreadcrumbs('commentEntity');
        $level = array();
        $level['name'] = $this->_controller->getDictionary()->getFromBreadcrumbs('commentEntity');
        $level['uriIndex'] = $uriIndex;
        
        if($breadcrumbs['comment'])
        {
            $args = array(
                Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
				Sitengine_Env::PARAM_ANCESTORID => $breadcrumbs['post']['id'],
                Sitengine_Env::PARAM_ID => $breadcrumbs['comment']['id']
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_COMMENTS_SHARP);
            $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $level['title'] = $breadcrumbs['comment']['comment'];
            $level['uriUpdate'] = $uriUpdate;
        }
        $data['comment'] = $level;
        return $data;
    }
    */
}

?>