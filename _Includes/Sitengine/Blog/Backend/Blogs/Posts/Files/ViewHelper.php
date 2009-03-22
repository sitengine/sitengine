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


abstract class Sitengine_Blog_Backend_Blogs_Posts_Files_ViewHelper extends Sitengine_View {
    
       
	protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Blog_Backend_Blogs_Posts_Files_Controller)
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
    		$this->_settings['editorSnippet'] = $this->_controller->getEditorSnippet();
    		$this->_settings['ancestorType'] = $this->_controller->getEntity()->getAncestorType();
    		
    		
    		$this->_queries = $this->_controller->getFrontController()->getQueries();
			
			$this->setSection(
				'GLOBALNAV',
				$this->_controller->getFrontController()->getGlobalNavSection(
					$this->_controller->getTranslate(),
					$this->_queries,
					'blogBackendBlogs'
				)
			);
			
			
			
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
			
			if(sizeof($this->_controller->getTranslate()->getAvailableLanguages()) > 1) {
				$this->setSection(
					'LANGUAGE',
					Sitengine_Env_Preferences_Sections::getLanguageForm(
						$this->_controller->getPreferences()->getLanguage(),
						$this->_controller->getTranslate()->getAvailableLanguages(),
						$this->_controller->getTranslate()->translateGroup('loclangs')->toArray(),
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
					#'title' => $this->_controller->getTranslate()->translate('breadcrumbsTitle'),
					'DATA' => $breadcrumbs
				)
			);
			
			$this->setSection(
				'ABSTRACT',
				array(
					'title' => $breadcrumbs['post']['title'],
					'uri' => $breadcrumbs['post']['uriUpdate'],
					'help' => ''
				)
			);
			
			
			
			$type = $this->_controller->getEntity()->getAncestorType();
			if($type == Sitengine_Blog_Posts_Table::TYPE_GALLERY) {
				$labelIndex = $this->_controller->getTranslate()->translate('labelsActionsSectionPhotoIndex');
				$labelInsert = $this->_controller->getTranslate()->translate('labelsActionsSectionPhotoInsert');
			}
			else {
				$labelIndex = $this->_controller->getTranslate()->translate('labelsActionsSectionFileIndex');
				$labelInsert = $this->_controller->getTranslate()->translate('labelsActionsSectionFileInsert');
			}
			
			$args = array(
				Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_POSTS_FILES);
			$uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			$args = array(
				Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_POSTS_FILES_NEW);
			$uriInsert = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			/*
			$args = array(
				Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_POSTS_FILES_ASSIGN);
			$uriAssign = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			*/
			$args = array(
				Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_POSTS_FILES_UPLOAD);
			$uriUpload = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			$this->setSection(
				'ACTIONS',
				array(
					array(
						'uri' => $uriIndex,
						'label' => $labelIndex
					),
					array(
						'uri' => $uriInsert,
						'label' => $labelInsert
					)/*,
					array(
						'uri' => $uriAssign,
						'label' => $this->_controller->getTranslate()->translate('labelsActionsSectionAssign')
					)*/,
					array(
						'uri' => $uriUpload,
						'label' => $this->_controller->getTranslate()->translate('labelsActionsSectionUpload')
					)
				)
			);
			return $this;
		}
		catch (Exception $exception) {
        	require_once 'Sitengine/Blog/Backend/Blogs/Posts/Files/Exception.php';
        	throw new Sitengine_Blog_Backend_Blogs_Posts_Files_Exception('build page error', $exception);
        }
    }
    
    
    
    
    
    protected function _makeBreadcrumbsData()
    {
    	$data = array();
        $breadcrumbs = $this->_controller->getEntity()->getBreadcrumbs();
        
        $blogSlug = $this->_controller->getFrontController()->getBlogPackage()->getBlogSlug();
        if($blogSlug === null)
        {
			$table = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTable();
			$table->setTranslation($this->_controller->getPreferences()->getTranslation());
			$blog = $table->complementRow($breadcrumbs['blog']);
			
			$args = array(
				Sitengine_Env::PARAM_ID => $this->_controller->getEntity()->getGreatAncestorSlug()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_SHARP);
			$uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			$args = array();
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS);
			$uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			$data['blog'] = array(
				'name' => $this->_controller->getTranslate()->translate('breadcrumbsBlogEntity'),
				'uriIndex' => $uriIndex,
				'title' => $blog['title'],
				'uriUpdate' => $uriUpdate
			);
		}
        
        
        $table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
        $table->setTranslation($this->_controller->getPreferences()->getTranslation());
    	$post = $table->complementRow($breadcrumbs['post']);
        
        $args = array(
            Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
            Sitengine_Env::PARAM_ID => $post['id']
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_POSTS_SHARP);
        $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        
        $args = array(
            Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug()
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_POSTS);
        $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        /*
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
		*/
        $data['post'] = array(
            'name' => $this->_controller->getTranslate()->translate('breadcrumbsPostEntity'),
            'uriIndex' => $uriIndex,
            'title' => $post['title'],
            'uriUpdate' => $uriUpdate
        );
        
        
    	$table = $this->_controller->getFrontController()->getBlogPackage()->getFilesTable();
        $table->setTranslation($this->_controller->getPreferences()->getTranslation());
        
        $args = array(
            Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
            Sitengine_Env::PARAM_ANCESTORID => $post['id']
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_POSTS_FILES);
        $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        
        $type = $this->_controller->getEntity()->getAncestorType();
		if($type == Sitengine_Blog_Posts_Table::TYPE_GALLERY) {
			$name = $this->_controller->getTranslate()->translate('breadcrumbsGalleryFileEntity');
		}
		else {
			$name = $this->_controller->getTranslate()->translate('breadcrumbsOtherFileEntity');
		}
		
        $level = array();
        $level['name'] = $name;
        $level['uriIndex'] = $uriIndex;
        
        if($breadcrumbs['file'])
        {
        	$file = $table->complementRow($breadcrumbs['file']);
        	
            $args = array(
                Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
				Sitengine_Env::PARAM_ANCESTORID => $post['id'],
                Sitengine_Env::PARAM_ID => $file['id']
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_POSTS_FILES_SHARP);
            $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            $level['title'] = $file['title'];
            $level['uriUpdate'] = $uriUpdate;
        }
        $data['file'] = $level;
        return $data;
    }
}

?>