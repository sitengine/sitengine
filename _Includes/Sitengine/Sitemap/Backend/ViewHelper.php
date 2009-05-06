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
 * @package    Sitengine_Sitemap
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/View.php';


abstract class Sitengine_Sitemap_Backend_ViewHelper extends Sitengine_View {
    
       
    protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Sitemap_Backend_Controller)
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
    		$this->_queries = $this->_controller->getFrontController()->getQueries(
				$this->_controller->getPermiso()
			);
			
			$this->setSection(
				'GLOBALNAV',
				$this->_controller->getFrontController()->getGlobalNavSection(
					$this->_controller->getPermiso(),
					$this->_controller->getTranslate(),
					$this->_queries,
					'sitemapBackend'
				)
			);
			
			
			/*
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
            */
            
            $subject = $this->_controller->getEntity()->getData();
            
            if($this->_controller->getEntity()->getParentId()) { $subjectId = $this->_controller->getEntity()->getParentId(); }
            else { $subjectId = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ID); }
            
            
            $args = array(
                #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                Sitengine_Env::PARAM_ACTION => Sitengine_Sitemap_Backend_Controller::ACTION_SEARCH
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
            $uriSearch = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $args = array(
                #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                Sitengine_Env::PARAM_ACTION => Sitengine_Sitemap_Backend_Controller::ACTION_INDEX,
                Sitengine_Env::PARAM_PARENTID => $subjectId
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
            $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $args = array(
                #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                Sitengine_Env::PARAM_ACTION => Sitengine_Sitemap_Backend_Controller::ACTION_NEWLAYER,
                Sitengine_Env::PARAM_PARENTID => $subjectId
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
            $uriNewLayer = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $args = array(
                #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                Sitengine_Env::PARAM_ACTION => Sitengine_Sitemap_Backend_Controller::ACTION_NEWSNIPPET,
                Sitengine_Env::PARAM_PARENTID => $subjectId
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
            $uriNewSnippet = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $args = array(
                #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                Sitengine_Env::PARAM_ACTION => Sitengine_Sitemap_Backend_Controller::ACTION_NEWFILE,
                Sitengine_Env::PARAM_PARENTID => $subjectId
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
            $uriNewFile = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $args = array(
                #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                Sitengine_Env::PARAM_ACTION => Sitengine_Sitemap_Backend_Controller::ACTION_NEWPAGE,
                Sitengine_Env::PARAM_PARENTID => $subjectId
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
            $uriNewPage = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $args = array(
                #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                Sitengine_Env::PARAM_ACTION => Sitengine_Sitemap_Backend_Controller::ACTION_NEWMASK,
                Sitengine_Env::PARAM_PARENTID => $subjectId
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
            $uriNewMask = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            
            
            
            
            
            if(!$subject) {
            	$childCount = '';
            }
            else {
                $childCount = '('.$this->countChildren($subjectId).'/'.$this->countChildrenRecursively($subjectId).')';
                
                switch($subject['type']) {
                    case Sitengine_Sitemap::ITEMTYPE_SNIPPET: $updateDirective = Sitengine_Sitemap_Backend_Controller::ACTION_UPDATESNIPPET; break;
                    case Sitengine_Sitemap::ITEMTYPE_LAYER: $updateDirective = Sitengine_Sitemap_Backend_Controller::ACTION_UPDATELAYER; break;
                    case Sitengine_Sitemap::ITEMTYPE_FILE: $updateDirective = Sitengine_Sitemap_Backend_Controller::ACTION_UPDATEFILE; break;
                    case Sitengine_Sitemap::ITEMTYPE_PAGE: $updateDirective = Sitengine_Sitemap_Backend_Controller::ACTION_UPDATEPAGE; break;
                    case Sitengine_Sitemap::ITEMTYPE_MASK: $updateDirective = Sitengine_Sitemap_Backend_Controller::ACTION_UPDATEMASK; break;
                }
                $args = array(
                    #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                    Sitengine_Env::PARAM_ACTION => $updateDirective,
                    Sitengine_Env::PARAM_ID => $subjectId
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
                $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                
                $args = array(
                    #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                    Sitengine_Env::PARAM_ACTION => Sitengine_Sitemap_Backend_Controller::ACTION_INDEX,
                    Sitengine_Env::PARAM_PARENTID => $subject['pid']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
                $uriBack = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $actionsBack = array(
                    'uri' => $uriBack,
                    'label' => $this->_controller->getTranslate()->translate('labelsActionsBack')
                );
            }
            
            $actionsSearch = array(
                'uri' => $uriSearch,
                'label' => $this->_controller->getTranslate()->translate('labelsActionsSearch')
            );
            $actionsList = array(
                'uri' => $uriIndex,
                'label' => $this->_controller->getTranslate()->translate('labelsActionsList'),
                'postfix' => $childCount
            );
            $actionsNewSnippet = array(
                'uri' => $uriNewSnippet,
                'label' => $this->_controller->getTranslate()->translate('labelsActionsNewSnippet')
            );
            $actionsNewLayer = array(
                'uri' => $uriNewLayer,
                'label' => $this->_controller->getTranslate()->translate('labelsActionsNewLayer')
            );
            $actionsNewFile = array(
                'uri' => $uriNewFile,
                'label' => $this->_controller->getTranslate()->translate('labelsActionsNewFile')
            );
            $actionsNewPage = array(
                'uri' => $uriNewPage,
                'label' => $this->_controller->getTranslate()->translate('labelsActionsNewPage')
            );
            /*
            $actionsNewMask = array(
                'uri' => $uriNewMask,
                'label' => $this->_controller->getTranslate()->translate('labelsActionsNewMask')
            );
            */
            
            
            if(!$subject) {
                $type = '';
                $title = $this->_controller->getTranslate()->translate('labelsEntityTitle');
                $uriUpdate = '';
                $actions = array($actionsList, $actionsNewLayer, $actionsNewPage, $actionsNewSnippet, $actionsNewFile, $actionsSearch);
            }
            else if($subject['type']==Sitengine_Sitemap::ITEMTYPE_LAYER) {
                $type = $subject['type'];
                $title = $subject['keyword'];
                $actions = array($actionsBack, $actionsList, $actionsNewLayer, $actionsNewPage, $actionsNewSnippet, $actionsNewFile, $actionsSearch);
            }
            else if($subject['type']==Sitengine_Sitemap::ITEMTYPE_SNIPPET) {
                $type = $subject['type'];
                $title = $subject['keyword'];
                $actions = array($actionsBack, $actionsSearch);
            }
            else if($subject['type']==Sitengine_Sitemap::ITEMTYPE_FILE) {
                $type = $subject['type'];
                $title = $subject['keyword'];
                $actions = array($actionsBack, $actionsSearch);
            }
            else if($subject['type']==Sitengine_Sitemap::ITEMTYPE_PAGE) {
                $type = $subject['type'];
                $title = $subject['keyword'];
                $actions = array($actionsBack, $actionsList, $actionsNewSnippet, $actionsNewFile, $actionsSearch);
            }
            else if($subject['type']==Sitengine_Sitemap::ITEMTYPE_MASK) {
                $type = $subject['type'];
                $title = $subject['keyword'];
                $actions = array($actionsBack, $actionsList, $actionsNewSnippet, $actionsNewFile, $actionsSearch);
            }
            $this->setSection('ACTIONS', $actions);   
            
            
            $this->setSection(
                'ABSTRACT',
                array(
                    'type' => $type,
                    'title' => $title,
                    'uri' => $uriUpdate,
                    'help' => ''
                )
            );
            
            
            $this->setSection(
                'BREADCRUMBS',
                array(
                    'title' => '',
                    'DATA' => $this->_makeBreadcrumbsData()
                )
            );
			return $this;
		}
		catch (Exception $exception) {
        	require_once 'Sitengine/Sitemap/Backend/Exception.php';
        	throw new Sitengine_Sitemap_Backend_Exception('build page error', $exception);
        }
    }
    
    
    
    
    public function getListFilterInstance()
    {
    	require_once 'Sitengine/Grid/Search.php';
    	$filter = new Sitengine_Grid_Search();
		$reset = ($this->_controller->getRequest()->get(Sitengine_Sitemap_Backend_Controller::PARAM_FILTER_RESET));
		
		
		### filter element ###
		if($reset) { $filter->resetSessionVal(Sitengine_Sitemap_Backend_Controller::PARAM_FILTER_BY_TYPE, $this->_controller->getNamespace()); }
		$filter->registerSessionVal(
			$this->_controller->getNamespace(),
			$this->_controller->getRequest(),
			Sitengine_Sitemap_Backend_Controller::PARAM_FILTER_BY_TYPE,
			Sitengine_Sitemap_Backend_Controller::VALUE_NONESELECTED
		);
		# set clause
		if($filter->getVal(Sitengine_Sitemap_Backend_Controller::PARAM_FILTER_BY_TYPE)) {
			$value = $this->_controller->getDatabase()->quote($filter->getVal(Sitengine_Sitemap_Backend_Controller::PARAM_FILTER_BY_TYPE));
			$clause = 'type = '.$value;
			$filter->setClause(Sitengine_Sitemap_Backend_Controller::PARAM_FILTER_BY_TYPE, $clause);
		}
		
		
		### filter element ###
		if($reset) { $filter->resetSessionVal(Sitengine_Sitemap_Backend_Controller::PARAM_FILTER_BY_FIND, $this->_controller->getNamespace()); }
		$filter->registerSessionVal(
			$this->_controller->getNamespace(),
			$this->_controller->getRequest(),
			Sitengine_Sitemap_Backend_Controller::PARAM_FILTER_BY_FIND,
			Sitengine_Sitemap_Backend_Controller::VALUE_NONESELECTED
		);
		# set clause
		if($filter->getVal(Sitengine_Sitemap_Backend_Controller::PARAM_FILTER_BY_FIND)) {
			$value = $this->_controller->getDatabase()->quote($filter->getVal(Sitengine_Sitemap_Backend_Controller::PARAM_FILTER_BY_FIND));
			$value = preg_replace('/\s+/', ' ', $value);
			$value = preg_replace('/.(.*)./', "$1", trim($value)); # remove quote delimiters
			$clause  = '(';
			$clause .= ' LOWER(keyword) LIKE LOWER("%'.$value.'%")';
			$clause .= ' OR LOWER(file1OriginalSource) LIKE LOWER("%'.$value.'%")';
			foreach($this->_controller->getTranscripts()->get() as $index => $symbol) {
				#$clause .= ' OR LOWER(titleLang'.$index.') LIKE LOWER("%'.$value.'%")';
				$clause .= ' OR LOWER(htmlLang'.$index.') LIKE LOWER("%'.$value.'%")';
				$clause .= ' OR LOWER(metaKeywordsLang'.$index.') LIKE LOWER("%'.$value.'%")';
				$clause .= ' OR LOWER(metaDescriptionLang'.$index.') LIKE LOWER("%'.$value.'%")';
			}
			$clause .= ')';
			$filter->setClause(Sitengine_Sitemap_Backend_Controller::PARAM_FILTER_BY_FIND, $clause);
		}
		return $filter;
    }
    
    
    
    
    
    public function getSearchFilterInstance()
    {
    	require_once 'Sitengine/Grid/Search.php';
    	$filter = new Sitengine_Grid_Search();
		$reset = ($this->_controller->getRequest()->get(Sitengine_Sitemap_Backend_Controller::PARAM_SEARCH_RESET));
		
		
		### filter element ###
		if($reset) { $filter->resetSessionVal(Sitengine_Sitemap_Backend_Controller::PARAM_SEARCH_BY_TYPE, $this->_controller->getNamespace()); }
		$filter->registerSessionVal(
			$this->_controller->getNamespace(),
			$this->_controller->getRequest(),
			Sitengine_Sitemap_Backend_Controller::PARAM_SEARCH_BY_TYPE,
			Sitengine_Sitemap_Backend_Controller::VALUE_NONESELECTED
		);
		# set clause
		if($filter->getVal(Sitengine_Sitemap_Backend_Controller::PARAM_SEARCH_BY_TYPE)) {
			$value = $this->_controller->getDatabase()->quote($filter->getVal(Sitengine_Sitemap_Backend_Controller::PARAM_SEARCH_BY_TYPE));
			$clause = 'type = '.$value;
			$filter->setClause(Sitengine_Sitemap_Backend_Controller::PARAM_SEARCH_BY_TYPE, $clause);
		}
		
		
		### filter element ###
		if($reset) { $filter->resetSessionVal(Sitengine_Sitemap_Backend_Controller::PARAM_SEARCH_BY_FIND, $this->_controller->getNamespace()); }
		$filter->registerSessionVal(
			$this->_controller->getNamespace(),
			$this->_controller->getRequest(),
			Sitengine_Sitemap_Backend_Controller::PARAM_SEARCH_BY_FIND,
			Sitengine_Sitemap_Backend_Controller::VALUE_NONESELECTED
		);
		# set clause
		if($filter->getVal(Sitengine_Sitemap_Backend_Controller::PARAM_SEARCH_BY_FIND)) {
			$value = $this->_controller->getDatabase()->quote($filter->getVal(Sitengine_Sitemap_Backend_Controller::PARAM_SEARCH_BY_FIND));
			$value = preg_replace('/\s+/', ' ', $value);
			$value = preg_replace('/.(.*)./', "$1", trim($value)); # remove quote delimiters
			$clause  = '(';
			$clause .= ' LOWER(keyword) LIKE LOWER("%'.$value.'%")';
			$clause .= ' OR LOWER(file1OriginalSource) LIKE LOWER("%'.$value.'%")';
			foreach($this->_controller->getTranscripts()->get() as $index => $symbol) {
				#$clause .= ' OR LOWER(titleLang'.$index.') LIKE LOWER("%'.$value.'%")';
				$clause .= ' OR LOWER(htmlLang'.$index.') LIKE LOWER("%'.$value.'%")';
				$clause .= ' OR LOWER(metaKeywordsLang'.$index.') LIKE LOWER("%'.$value.'%")';
				$clause .= ' OR LOWER(metaDescriptionLang'.$index.') LIKE LOWER("%'.$value.'%")';
			}
			$clause .= ')';
			$filter->setClause(Sitengine_Sitemap_Backend_Controller::PARAM_SEARCH_BY_FIND, $clause);
		}
		return $filter;
    }
    
    
    
    public function getSortingInstance()
    {
    	require_once 'Sitengine/Grid/Sorting.php';
    	$sorting = new Sitengine_Grid_Sorting(
    		$this->_controller->getRequest()->get(Sitengine_Env::PARAM_SORT),
    		$this->_controller->getRequest()->get(Sitengine_Env::PARAM_ORDER)
    	);
		$sorting->addRule('locked', 'desc', 'locked asc', 'locked desc');
		$sorting->addRule('enabled', 'desc', 'enabled asc', 'enabled desc');
		$sorting->addRule('cdate', 'desc', 'cdate asc', 'cdate desc');
		$sorting->addRule('mdate', 'desc', 'mdate asc', 'mdate desc');
		$sorting->addRule('keyword', 'asc', 'keyword asc', 'keyword desc');
		$sorting->addRule('type', 'asc', 'type asc, keyword asc', 'type desc, keyword desc');
		#$sorting->addRule('title', 'asc', 'titleLang0 asc', 'titleLang0 desc');
		$sorting->setDefaultRule('type');
		return $sorting;
    }
    
    
    
    
    
    
    
    protected function _makeBreadcrumbsData()
    {
        $data = array();
        
        if(!$this->_controller->getEntity()->isRootListing())
        {
            $args = array(
                #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                Sitengine_Env::PARAM_ACTION => Sitengine_Sitemap_Backend_Controller::ACTION_INDEX
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
            $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
            $data[] = array(
                'name' => $this->_controller->getTranslate()->translate('breadcrumbsPageEntity'),
                'uriIndex' => $uriIndex
            );
            
            $thread = $this->_controller->getEntity()->getThreadData();
            foreach($thread as $level => $item)
            {
            	/*
                switch($item['type'])
                {
                    case Sitengine_Sitemap::ITEMTYPE_LAYER: $a = Sitengine_Sitemap_Backend_Controller::ACTION_UPDATELAYER; break;
                    case Sitengine_Sitemap::ITEMTYPE_PAGE: $a = Sitengine_Sitemap_Backend_Controller::ACTION_UPDATEPAGE; break;
                    case Sitengine_Sitemap::ITEMTYPE_MASK: $a = Sitengine_Sitemap_Backend_Controller::ACTION_UPDATEMASK; break;
                }
                $args = array(
                    #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                    Sitengine_Env::PARAM_ACTION => $a,
                    Sitengine_Env::PARAM_ID => $item['id']
                );
                */
                switch($item['type'])
                {
                    case Sitengine_Sitemap::ITEMTYPE_LAYER: $a = Sitengine_Sitemap_Backend_Controller::ACTION_INDEX; break;
                    case Sitengine_Sitemap::ITEMTYPE_PAGE: $a = Sitengine_Sitemap_Backend_Controller::ACTION_INDEX; break;
                    case Sitengine_Sitemap::ITEMTYPE_MASK: $a = Sitengine_Sitemap_Backend_Controller::ACTION_INDEX; break;
                }
                $args = array(
                    #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                    Sitengine_Env::PARAM_ACTION => $a,
                    Sitengine_Env::PARAM_PARENTID => $item['id']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
                $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $data[] = array(
                    'title' => $item['keyword'],
                    'uriUpdate' => $uriUpdate
                );
            }
            
            $subject = $this->_controller->getEntity()->getData();
            
            /*
            switch($subject['type'])
            {
                case Sitengine_Sitemap::ITEMTYPE_SNIPPET: $a = Sitengine_Sitemap_Backend_Controller::ACTION_UPDATESNIPPET; break;
                case Sitengine_Sitemap::ITEMTYPE_LAYER: $a = Sitengine_Sitemap_Backend_Controller::ACTION_UPDATELAYER; break;
                case Sitengine_Sitemap::ITEMTYPE_FILE: $a = Sitengine_Sitemap_Backend_Controller::ACTION_UPDATEFILE; break;
                case Sitengine_Sitemap::ITEMTYPE_PAGE: $a = Sitengine_Sitemap_Backend_Controller::ACTION_UPDATEPAGE; break;
                case Sitengine_Sitemap::ITEMTYPE_MASK: $a = Sitengine_Sitemap_Backend_Controller::ACTION_UPDATEMASK; break;
            }
            $args = array(
                #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                Sitengine_Env::PARAM_ACTION => $a,
                Sitengine_Env::PARAM_ID => $subject['id']
            );
            */
            
            if(
            	$subject['type'] == Sitengine_Sitemap::ITEMTYPE_LAYER ||
            	$subject['type'] == Sitengine_Sitemap::ITEMTYPE_PAGE ||
            	$subject['type'] == Sitengine_Sitemap::ITEMTYPE_MASK
            )
            {
				$args = array(
					#Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
					Sitengine_Env::PARAM_ACTION => Sitengine_Sitemap_Backend_Controller::ACTION_INDEX,
					Sitengine_Env::PARAM_PARENTID => $subject['id']
				);
			}
			else {
				switch($subject['type'])
				{
					case Sitengine_Sitemap::ITEMTYPE_SNIPPET: $a = Sitengine_Sitemap_Backend_Controller::ACTION_UPDATESNIPPET; break;
					case Sitengine_Sitemap::ITEMTYPE_FILE: $a = Sitengine_Sitemap_Backend_Controller::ACTION_UPDATEFILE; break;
				}
				$args = array(
					#Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
					Sitengine_Env::PARAM_ACTION => $a,
					Sitengine_Env::PARAM_ID => $subject['id']
				);
			}
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
            $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $data[] = array(
                'title' => $subject['keyword'],
                'uriUpdate' => $uriUpdate
            );
        }
        return $data;
    }
    
    
    
    
    
    
    public function countChildren($id)
    {
    	try {
			$count = 0;
			
			if($id!='')
			{
				$q  = 'SELECT COUNT(*) AS count';
				$q .= ' FROM '.$this->_controller->getFrontController()->getSitemapPackage()->getTableSitemap();
				$q .= ' WHERE pid = '.$this->_controller->getDatabase()->quote($id);
				$q .= ' LIMIT 0, 1';
				$statement = $this->_controller->getDatabase()->prepare($q);
				$statement->execute();
				$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
				$count = (sizeof($result)) ? $result[0]['count'] : 0;
			}
			return $count;
		}
		catch (Exception $exception) {
            require_once 'Sitengine/Sitemap/Backend/Exception.php';
            throw new Sitengine_Sitemap_Backend_Exception('count childpages error', $exception);
        }
    }
    
    
    
    
    public function countChildrenRecursively($id)
    {
    	try {
			$count = 0;
			
			if($id!='')
			{
				$q  = 'SELECT id';
				$q .= ' FROM '.$this->_controller->getFrontController()->getSitemapPackage()->getTableSitemap();
				$q .= ' WHERE pid = '.$this->_controller->getDatabase()->quote($id);
				$statement = $this->_controller->getDatabase()->prepare($q);
				$statement->execute();
				$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
				
				foreach($result as $row) {
					$count += $this->countChildrenRecursively($row['id']);
					$count++;
				}
				return $count;
			}
			return $count;
		}
		catch (Exception $exception) {
            require_once 'Sitengine/Sitemap/Backend/Exception.php';
            throw new Sitengine_Sitemap_Backend_Exception('count childpages recursively error', $exception);
        }
    }
    
}

?>