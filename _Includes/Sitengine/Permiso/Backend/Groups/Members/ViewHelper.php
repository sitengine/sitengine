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


abstract class Sitengine_Permiso_Backend_Groups_Members_ViewHelper extends Sitengine_View {
    
       
	protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Permiso_Backend_Groups_Members_Controller)
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
					'permisoBackendGroups'
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
			$breadcrumbs = $this->_makeBreadcrumbsData();
			
			$this->setSection(
				'BREADCRUMBS',
				array(
					'title' => $this->_controller->getTranslate()->translate('breadcrumbsTitle'),
					'DATA' => $breadcrumbs
				)
			);
			
			$this->setSection(
				'ABSTRACT',
				array(
					'title' => $breadcrumbs['group']['title'],
					'uri' => $breadcrumbs['group']['uriUpdate'],
					'help' => ''
				)
			);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS_MEMBERS);
			$uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS_MEMBERS_NEW);
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
        	require_once 'Sitengine/Permiso/Backend/Groups/Members/Exception.php';
        	throw new Sitengine_Permiso_Backend_Groups_Members_Exception('build page error', $exception);
        }
    }
    
    
    
    
    protected function _makeBreadcrumbsData()
    {
    	$data = array();
		$breadcrumbs = $this->_controller->getEntity()->getBreadcrumbs();
        $table = $this->_controller->getFrontController()->getPermisoPackage()->getGroupsTable();
        $group = $table->complementRow($breadcrumbs['group']);
        
        
        $args = array(
            Sitengine_Env::PARAM_ID => $group['id']
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS_SHARP);
        $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        
        $args = array(
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS);
        $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        $extras = array(
        	'title' => $group['name'],
            'name' => $this->_controller->getTranslate()->translate('breadcrumbsGroupEntity'),
            'uriIndex' => $uriIndex,
            'uriUpdate' => $uriUpdate
        );
        $data['group'] = array_merge($group, $extras);
        
        
        
        $args = array(
            Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
            Sitengine_Env::PARAM_ANCESTORID => $group['id']
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS_MEMBERS);
        $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        $level = array();
        $level['name'] = $this->_controller->getTranslate()->translate('breadcrumbsMemberEntity');
        $level['uriIndex'] = $uriIndex;
        
        if($breadcrumbs['member'] !== null)
        {
        	$table = $this->_controller->getFrontController()->getPermisoPackage()->getMembershipsTable();
        	$member = $table->complementRow($breadcrumbs['member']);
        	
            $args = array(
                Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
                Sitengine_Env::PARAM_ID => $member['id']
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS_MEMBERS_SHARP);
            $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            $level['title'] = $member['id'];
            $level['uriUpdate'] = $uriUpdate;
            $level = array_merge($member, $level);
        }
        $data['member'] = $level;
        return $data;
    }
}

?>