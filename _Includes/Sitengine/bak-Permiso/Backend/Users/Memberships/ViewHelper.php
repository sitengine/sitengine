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
 * @package    Sitengine_Permiso
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/View.php';


abstract class Sitengine_Permiso_Backend_Users_Memberships_ViewHelper extends Sitengine_View {
    
       
	protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Permiso_Backend_Users_Memberships_Controller)
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
    		$this->_queries = $this->_controller->getFrontController()->getQueries(
				$this->_controller->getPermiso()
			);
			
			$this->setSection(
				'GLOBALNAV',
				$this->_controller->getFrontController()->getGlobalNavSection(
					$this->_controller->getPermiso(),
					$this->_controller->getTranslate(),
					$this->_queries,
					'permisoBackendUsers'
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
					'title' => $breadcrumbs[0]['title'],
					'uri' => $breadcrumbs[0]['uriUpdate'],
					'help' => ''
				)
			);
			
			$args = array(
				#Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
				Sitengine_Env::PARAM_ACTION => Sitengine_Permiso_Backend_Users_Memberships_Controller::ACTION_INDEX,
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS_MEMBERSHIPS);
			$uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			$args = array(
				#Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
				Sitengine_Env::PARAM_ACTION => Sitengine_Permiso_Backend_Users_Memberships_Controller::ACTION_INSERT,
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS_MEMBERSHIPS);
			$uriInsert = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
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
					)
				)
			);
			return $this;
		}
		catch (Exception $exception) {
        	throw $this->_controller->getExceptionInstance('build page error', $exception);
        }
    }
    
    
    
    public function getFilterInstance()
    {
    	require_once 'Sitengine/Grid/Search.php';
    	$filter = new Sitengine_Grid_Search();
		$reset = ($this->_controller->getRequest()->get(Sitengine_Permiso_Backend_Users_Memberships_Controller::PARAM_FILTER_RESET));
		
		### filter element ###
		if($reset) { $filter->resetSessionVal(Sitengine_Permiso_Backend_Users_Memberships_Controller::PARAM_FILTER_BY_FIND, $this->_controller->getNamespace()); }
		$filter->registerSessionVal(
			$this->_controller->getNamespace(),
			$this->_controller->getRequest(),
			Sitengine_Permiso_Backend_Users_Memberships_Controller::PARAM_FILTER_BY_FIND,
			Sitengine_Permiso_Backend_Users_Memberships_Controller::VALUE_NONESELECTED
		);
		# set clause
		if($filter->getVal(Sitengine_Permiso_Backend_Users_Memberships_Controller::PARAM_FILTER_BY_FIND)) {
			$value = $this->_controller->getDatabase()->quote($filter->getVal(Sitengine_Permiso_Backend_Users_Memberships_Controller::PARAM_FILTER_BY_FIND));
			$value = preg_replace('/\s+/', ' ', $value);
			$value = preg_replace('/.(.*)./', "$1", trim($value)); # remove quote delimiters
			$clause  = '(';
			$clause .= 'LOWER(g.name) LIKE LOWER("%'.$value.'%")';
            $clause .= ' OR LOWER(g.description) LIKE LOWER("%'.$value.'%")';
			$clause .= ')';
			$filter->setClause(Sitengine_Permiso_Backend_Users_Memberships_Controller::PARAM_FILTER_BY_FIND, $clause);
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
		$sorting->addRule('locked', 'desc', 'm.locked asc', 'm.locked desc');
		$sorting->addRule('cdate', 'desc', 'm.cdate asc', 'm.cdate desc');
		$sorting->addRule('mdate', 'desc', 'm.mdate asc', 'm.mdate desc');
		$sorting->addRule('name', 'asc', 'g.name asc', 'g.name desc');
		$sorting->addRule('enabled', 'desc', 'g.enabled asc', 'g.enabled desc');
		$sorting->setDefaultRule('name');
		return $sorting;
    }
    
    
    
    
    
    
    protected function _makeBreadcrumbsData()
    {
        $breadcrumbs = $this->_controller->getEntity()->getBreadcrumbsData();
        $data = array();
        
        $levelIndex = 0;
        $args = array(
            #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
            Sitengine_Env::PARAM_ACTION => Sitengine_Permiso_Backend_Users_Memberships_Controller::ACTION_UPDATE,
            Sitengine_Env::PARAM_ID => $breadcrumbs[$levelIndex]['id']
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS);
        $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        
        $args = array(
            #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
            Sitengine_Env::PARAM_ACTION => Sitengine_Permiso_Backend_Users_Memberships_Controller::ACTION_INDEX
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS);
        $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        
        $name = $breadcrumbs[$levelIndex]['name'];
        $firstname = $breadcrumbs[$levelIndex]['firstname'];
        $lastname = $breadcrumbs[$levelIndex]['lastname'];
        $title = $name;
        if($firstname || $lastname) { $title .= ' ('; }
        if($firstname) { $title .= $firstname; }
        if($firstname && $lastname) { $title .= ' '; }
        if($lastname) { $title .= $lastname; }
        if($firstname || $lastname) { $title .= ')'; }
        
        $level = array(
            'name' => $this->_controller->getTranslate()->translate('breadcrumbsUserEntity'),
            'title' => $title,
            'uriUpdate' => $uriUpdate,
            'uriIndex' => $uriIndex
        );
        $data[] = $level;
        return $data;
    }
}

?>