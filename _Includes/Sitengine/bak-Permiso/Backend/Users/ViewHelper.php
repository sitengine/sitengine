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


abstract class Sitengine_Permiso_Backend_Users_ViewHelper extends Sitengine_View {
    
       
    protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Permiso_Backend_Users_Controller)
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
					$this->_controller->getDictionary(),
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
			
			if($this->_controller->getRequest()->getActionName()==Sitengine_Permiso_Backend_Users_Controller::ACTION_ME) {
				$title = $this->_controller->getDictionary()->getFromLabels('meEntityTitle');
			}
			else {
				$title = $this->_controller->getDictionary()->getFromLabels('entityTitle');
			}
			
			$this->setSection(
				'ABSTRACT',
				array(
					'title' => $title,
					'uri' => '',
					'help' => $this->_controller->getDictionary()->getFromHelps($this->_controller->getRequest()->getActionName())
				)
			);
			
			$args = array(
				#Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
				Sitengine_Env::PARAM_ACTION => Sitengine_Permiso_Backend_Users_Controller::ACTION_INDEX
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS);
			$uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			$args = array(
				#Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
				Sitengine_Env::PARAM_ACTION => Sitengine_Permiso_Backend_Users_Controller::ACTION_INSERT
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS);
			$uriInsert = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			if($this->_controller->getRequest()->getActionName()!=Sitengine_Permiso_Backend_Users_Controller::ACTION_ME) {
				$this->setSection(
					'ACTIONS',
					array(
						array(
							'uri' => $uriIndex,
							'label' => $this->_controller->getDictionary()->getFromLabels('actionsSectionIndex')
						),
						array(
							'uri' => $uriInsert,
							'label' => $this->_controller->getDictionary()->getFromLabels('actionsSectionInsert')
						)
					)
				);
			}
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
		$reset = ($this->_controller->getRequest()->get(Sitengine_Permiso_Backend_Users_Controller::PARAM_FILTER_RESET));
		
		### filter element ###
		if($reset) { $filter->resetSessionVal(Sitengine_Permiso_Backend_Users_Controller::PARAM_FILTER_BY_FIND, $this->_controller->getNamespace()); }
		$filter->registerSessionVal(
			$this->_controller->getNamespace(),
			$this->_controller->getRequest(),
			Sitengine_Permiso_Backend_Users_Controller::PARAM_FILTER_BY_FIND,
			Sitengine_Permiso_Backend_Users_Controller::VALUE_NONESELECTED
		);
		# set clause
		if($filter->getVal(Sitengine_Permiso_Backend_Users_Controller::PARAM_FILTER_BY_FIND)) {
			$value = $this->_controller->getDatabase()->quote($filter->getVal(Sitengine_Permiso_Backend_Users_Controller::PARAM_FILTER_BY_FIND));
			$value = preg_replace('/\s+/', ' ', $value);
			$value = preg_replace('/.(.*)./', "$1", trim($value)); # remove quote delimiters
			$clause  = '(';
			$clause .= 'LOWER(name) LIKE LOWER("%'.$value.'%")';
			$clause .= ' OR LOWER(firstname) LIKE LOWER("%'.$value.'%")';
			$clause .= ' OR LOWER(lastname) LIKE LOWER("%'.$value.'%")';
			$clause .= ' OR LOWER(nickname) LIKE LOWER("%'.$value.'%")';
			$clause .= ' OR LOWER(email) LIKE LOWER("%'.$value.'%")';
			$clause .= ' OR LOWER(description) LIKE LOWER("%'.$value.'%")';
			$clause .= ')';
			$filter->setClause(Sitengine_Permiso_Backend_Users_Controller::PARAM_FILTER_BY_FIND, $clause);
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
		$sorting->addRule('enabled', 'desc', 'enabled asc', 'enabled desc');
		$sorting->addRule('locked', 'desc', 'locked asc', 'locked desc');
		$sorting->addRule('cdate', 'desc', 'cdate asc', 'cdate desc');
		$sorting->addRule('lastLogin', 'desc', 'lastLogin asc', 'lastLogin desc');
		$sorting->addRule('mdate', 'desc', 'mdate asc', 'mdate desc');
		$sorting->addRule('name', 'asc', 'name asc', 'name desc');
		$sorting->addRule('nickname', 'asc', 'nickname asc', 'nickname desc');
		$sorting->addRule('firstname', 'asc', 'firstname asc', 'firstname desc');
		$sorting->addRule('lastname', 'asc', 'lastname asc', 'lastname desc');
		$sorting->addRule('language', 'asc', 'language asc', 'language desc');
		$sorting->addRule('onlineNow', 'asc', 'onlineNow asc', 'onlineNow desc');
		$sorting->setDefaultRule('name');
		return $sorting;
    }
    
    
    
    public function countMemberships($id)
    {
    	try {
			$q  = 'SELECT COUNT(*) AS count';
        	$q .= ' FROM '.$this->_controller->getPermiso()->getMembershipsTableName();
        	$q .= ' WHERE userId = '.$this->_controller->getDatabase()->quote($id);
			$statement = $this->_controller->getDatabase()->prepare($q);
			$statement->execute();
			$result = $statement->fetchAll();
			return $result[0]['count'];
		}
		catch (Exception $exception) {
			throw $this->_controller->getExceptionInstance('child count error', $exception);
		}
    }
    
}

?>