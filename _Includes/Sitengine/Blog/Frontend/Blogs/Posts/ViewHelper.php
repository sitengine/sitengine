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


abstract class Sitengine_Blog_Frontend_Blogs_Posts_ViewHelper extends Sitengine_View {
    
       
	protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Blog_Frontend_Blogs_Posts_Controller)
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
    		$this->_queries = $this->_controller->getFrontController()->getQueries();
			
			$this->setSection(
				'GLOBALNAV',
				$this->_controller->getFrontController()->getGlobalNavSection(
					$this->_controller->getTranslate(),
					$this->_queries,
					'blogFrontendBlogs'
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
					'RAW' => $this->_controller->getEntity()->getBreadcrumbsData(),
					'DATA' => $breadcrumbs
				)
			);
			
			$this->setSection(
				'ABSTRACT',
				array(
					'title' => $breadcrumbs['blog']['title'],
					#'uri' => $breadcrumbs[sizeof($breadcrumbs)-2]['uriView'],
					#'help' => ''
				)
			);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorSlug()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS);
			$uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			/*
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorSlug()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS);
			$uriInsert = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorSlug()
			);
			$query = array(
				Sitengine_Blog::PARAM_TYPE => Sitengine_Blog_Posts_Table::TYPE_TEXT
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_NEW);
			$uriTextInsert  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			$uriTextInsert .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorSlug(),
				#Sitengine_Blog::PARAM_TYPE => Sitengine_Blog_Posts_Table::TYPE_PHOTO
			);
			$query = array(
				Sitengine_Blog::PARAM_TYPE => Sitengine_Blog_Posts_Table::TYPE_PHOTO
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_NEW);
			$uriPhotoInsert  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			$uriPhotoInsert .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorSlug(),
				#Sitengine_Blog::PARAM_TYPE => Sitengine_Blog_Posts_Table::TYPE_GALLERY
			);
			$query = array(
				Sitengine_Blog::PARAM_TYPE => Sitengine_Blog_Posts_Table::TYPE_GALLERY
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_NEW);
			$uriGalleryInsert  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			$uriGalleryInsert .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorSlug(),
				#Sitengine_Blog::PARAM_TYPE => Sitengine_Blog_Posts_Table::TYPE_QUOTE
			);
			$query = array(
				Sitengine_Blog::PARAM_TYPE => Sitengine_Blog_Posts_Table::TYPE_QUOTE
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_NEW);
			$uriQuoteInsert  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			$uriQuoteInsert .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorSlug(),
				#Sitengine_Blog::PARAM_TYPE => Sitengine_Blog_Posts_Table::TYPE_LINK
			);
			$query = array(
				Sitengine_Blog::PARAM_TYPE => Sitengine_Blog_Posts_Table::TYPE_LINK
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_NEW);
			$uriLinkInsert  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			$uriLinkInsert .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorSlug(),
				#Sitengine_Blog::PARAM_TYPE => Sitengine_Blog_Posts_Table::TYPE_AUDIO
			);
			$query = array(
				Sitengine_Blog::PARAM_TYPE => Sitengine_Blog_Posts_Table::TYPE_AUDIO
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_NEW);
			$uriAudioInsert  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			$uriAudioInsert .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorSlug(),
				#Sitengine_Blog::PARAM_TYPE => Sitengine_Blog_Posts_Table::TYPE_VIDEO
			);
			$query = array(
				Sitengine_Blog::PARAM_TYPE => Sitengine_Blog_Posts_Table::TYPE_VIDEO
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_NEW);
			$uriVideoInsert  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			$uriVideoInsert .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
			
			$this->setSection(
				'ACTIONS',
				array(
					array(
						'uri' => $uriIndex,
						'label' => $this->_controller->getTranslate()->translate('labelsActionsSectionIndex')
					),
					array(
						'uri' => $uriInsert,
						'label' => $this->_controller->getTranslate()->translate('labelsActionsSectionInsert')
					),
					array(
						'uri' => $uriTextInsert,
						'label' => $this->_controller->getTranslate()->translate('labelsActionsSectionInsertText')
					),
					array(
						'uri' => $uriPhotoInsert,
						'label' => $this->_controller->getTranslate()->translate('labelsActionsSectionInsertPhoto')
					),
					array(
						'uri' => $uriGalleryInsert,
						'label' => $this->_controller->getTranslate()->translate('labelsActionsSectionInsertGallery')
					),
					array(
						'uri' => $uriQuoteInsert,
						'label' => $this->_controller->getTranslate()->translate('labelsActionsSectionInsertQuote')
					),
					array(
						'uri' => $uriLinkInsert,
						'label' => $this->_controller->getTranslate()->translate('labelsActionsSectionInsertLink')
					),
					array(
						'uri' => $uriAudioInsert,
						'label' => $this->_controller->getTranslate()->translate('labelsActionsSectionInsertAudio')
					),
					array(
						'uri' => $uriVideoInsert,
						'label' => $this->_controller->getTranslate()->translate('labelsActionsSectionInsertVideo')
					)
				)
			);
			*/
			return $this;
		}
		catch (Exception $exception) {
        	require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
        	throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('build page error', $exception);
        }
    }
    
    
    
    public function getCommentActions($id)
    {
    	$childActions = array();
    	/*
		$args = array(
			Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getAncestorSlug(),
			Sitengine_Env::PARAM_ANCESTORID => $id
		);
		$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_COMMENTS);
		$uri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
		$childActions['commentList'] = array(
			'uri' => $uri,
			'label' => $this->_controller->getTranslate()->translate('labelsChildActionsSectionCommentsIndex'),
			'postfix' => ' ('.$this->_controller->getViewHelper()->countComments($id).')'
		);
		*/
		$args = array(
			Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getAncestorSlug(),
			Sitengine_Env::PARAM_ANCESTORID => $id
		);
		$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_COMMENTS);
		$uri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
		$childActions['commentInsert'] = array(
			'uri' => $uri,
			'label' => $this->_controller->getTranslate()->translate('labelsChildActionsSectionCommentsInsert')
		);
		return $childActions;
    }
    
    
    
    
    public function countComments($id)
    {
    	try {
			$table = $this->_controller->getFrontController()->getBlogPackage()->getCommentsTable();
			$tableName = $this->_controller->getFrontController()->getBlogPackage()->getCommentsTableName();
    		$usersTableName = $this->_controller->getPermiso()->getUsersTableName();
    		
			$select = $table
				->select()
        		->setIntegrityCheck(false)
        		->from($table, array('uid'))
        		->from($usersTableName, array('id'))
        		->where("$tableName.uid = $usersTableName.id")
        		->where("parentId = '".$id."'")
        		->where("approve = '1'")
			;
			$items = $table->fetchAll($select);
			return $items->count();
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
			throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('comment count error', $exception);
		}
    }
    
    
    
    public function countFiles($id)
    {
    	try {
    		$table = $this->_controller->getFrontController()->getBlogPackage()->getFilesTable();
			$where = $this->_controller->getDatabase()->quoteInto('parentId = ?', $id);
			$select = $table->select()->from($table, array('COUNT(*) AS count'))->where($where);
			$count = $table->fetchRow($select);
			return $count->count;
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
			throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('file count error', $exception);
		}
    }
    
    
    
    
    public function getComments($parentId)
    {
    	try {
    		$table = $this->_controller->getFrontController()->getBlogPackage()->getCommentsTable();
    		$tableName = $this->_controller->getFrontController()->getBlogPackage()->getCommentsTableName();
    		$usersTableName = $this->_controller->getPermiso()->getUsersTableName();
    		
        	$select = $table
        		->select()
        		->setIntegrityCheck(false)
        		->from($table)
        		->from($usersTableName, array('name', 'firstname', 'lastname', 'nickname'))
        		->where("$tableName.uid = $usersTableName.id")
        		->where("parentId = '".$parentId."'")
        		->where("approve = '1'")
        		->order('cdate DESC')
        	;
        	
        	$items = $table->fetchAll($select);
        	$list = array();
			
            foreach($items as $item)
            {
            	$row = $item->toArray();
            	
            	$args = array(
            		Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getAncestorSlug(),
					Sitengine_Env::PARAM_ANCESTORID => $parentId,
					Sitengine_Env::PARAM_ID => $row['id']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_COMMENTS_SHARP);
                $row['uriDelete'] = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
				$list[] = $row;
            }
            return $list;
    	}
    	catch (Exception $exception) {
			require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
			throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('comment list section error', $exception);
		}
    }
    
    
    
    
    
    
    
    public function getFiles($parentId)
    {
    	try {
    		$table = $this->_controller->getFrontController()->getBlogPackage()->getFilesTable();
    		
        	$select = $table
        		->select()
        		->order('sorting ASC')
        		->where("parentId = '".$parentId."'")
        		->where("publish = '1'")
        	;
        	$items = $table->fetchAll($select);
        	$list = array();
			
            foreach($items as $item)
            {
            	$row = $table->complementRow($item);
            	#$row['cdate'] = $this->formatDate($row['cdate']);
                #$row['mdate'] = $this->formatDate($row['mdate']);
				
				$args = array(
					Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getAncestorSlug(),
					Sitengine_Env::PARAM_ANCESTORID => $parentId,
					Sitengine_Env::PARAM_ID => $row['id']
				);
				$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_FILES_SHARP);
				$row['uriView'] = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
				$list[] = $row;
            }
            return $list;
    	}
    	catch (Exception $exception) {
			require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
			throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('file list section error', $exception);
		}
    }
    
    
    
    
    protected function _makeBreadcrumbsData()
    {
        $breadcrumbs = $this->_controller->getEntity()->getBreadcrumbs();
        $data = array();
        
        $blog = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTable()->complementRow($breadcrumbs['blog']);
    	$translations = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTable()->getTranslations();
    	$translations->setLanguage($this->_controller->getPreferences()->getTranslation());
        /*
    	require_once 'Sitengine/Blog/Blog.php';
        $blog = new Sitengine_Blog_Blog(
        	$this->_controller->getDatabase(),
        	$this->_controller->getFrontController()->getBlogPackage()
        );
    	$translations = $blog->getTranslations();
    	$translations->setLanguage($this->_controller->getPreferences()->getLanguage());
    	
        /*
        $args = array(
            Sitengine_Env::PARAM_ID => $this->_controller->getEntity()->getAncestorSlug()
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_SHARP);
        $uriView = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        */
        $args = array();
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS);
        $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        $data['blog'] = array(
            'name' => $this->_controller->getTranslate()->translate('breadcrumbsBlogEntity'),
            'uriIndex' => $uriIndex,
            'title' => $blog['title'],
            #'uriView' => $uriView
        );
        
        
    	$translations = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->getTranslations();
    	$translations->setLanguage($this->_controller->getPreferences()->getTranslation());
        
        
        $args = array(
            Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorSlug()
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS);
        $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        $level = array();
        $level['name'] = $this->_controller->getTranslate()->translate('breadcrumbsPostEntity');
        $level['uriIndex'] = $uriIndex;
        
        if($breadcrumbs['post'])
        {
        	$post = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable()->complementRow($breadcrumbs['post']);
        	
            $args = array(
            	Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorSlug(),
                Sitengine_Env::PARAM_ID => $post['id']
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_SHARP);
            $uriView = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            if($post['type'] == Sitengine_Blog_Posts_Table::TYPE_PHOTO) {
            	$title = $post['file1OriginalSource'];
            }
            else if($post['type'] == Sitengine_Blog_Posts_Table::TYPE_QUOTE) {
            	$title = $post['markupLang'.$translations->getDefaultIndex()];
            }
            else if($post['type'] == Sitengine_Blog_Posts_Table::TYPE_AUDIO) {
            	$title = $post['file1OriginalSource'];
            }
            else if($post['type'] == Sitengine_Blog_Posts_Table::TYPE_VIDEO) {
            	$title = $post['titleLang'.$translations->getDefaultIndex()];
            }
            else {
				$title = $post['titleLang'.$translations->getIndex()];
				$title = ($title) ? $title : $post['titleLang'.$translations->getDefaultIndex()];
			}
            $level['title'] = $title;
            $level['uriView'] = $uriView;
        }
        $data['post'] = $level;
        return $data;
    }
    
    
    
    public function fetchAuthor($uid)
    {
    	$user = $this->_controller->getPermiso()->getDirectory()->findUserById($uid);
    	return array(
    		'authorFirstname' => ($user !== null) ? $user['firstname'] : '',
    		'authorLastname' => ($user !== null) ? $user['lastname'] : '',
    		'authorNickname' => ($user !== null) ? $user['nickname'] : '',
    		'authorName' => ($user !== null) ? $user['name'] : '',
    	);
    }
}

?>