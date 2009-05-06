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
    		$this->_queries = $this->_controller->getFrontController()->getQueries();
			
			$this->setSection(
				'GLOBALNAV',
				$this->_controller->getFrontController()->getGlobalNavSection(
					$this->_controller->getTranslate(),
					$this->_queries,
					'permisoBackendUsers'
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
					'title' => $breadcrumbs['user']['title'],
					'uri' => $breadcrumbs['user']['uriUpdate'],
					'help' => ''
				)
			);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS_MEMBERSHIPS);
			$uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS_MEMBERSHIPS_NEW);
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
        	require_once 'Sitengine/Permiso/Backend/Users/Memberships/Exception.php';
        	throw new Sitengine_Permiso_Backend_Users_Memberships_Exception('build page error', $exception);
        }
    }
    
    
    
    
    protected function _makeBreadcrumbsData()
    {
    	$data = array();
		$breadcrumbs = $this->_controller->getEntity()->getBreadcrumbs();
        $table = $this->_controller->getFrontController()->getPermiso()->getUsersTable();
        $user = $table->complementRow($breadcrumbs['user']);
        
        
        $args = array(
            Sitengine_Env::PARAM_ID => $user['id']
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS_SHARP);
        $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        
        $args = array(
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS);
        $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        $extras = array(
        	'title' => $user['name'],
            'name' => $this->_controller->getTranslate()->translate('breadcrumbsUserEntity'),
            'uriIndex' => $uriIndex,
            'uriUpdate' => $uriUpdate
        );
        $data['user'] = array_merge($user, $extras);
        
        
        
        $args = array(
            Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
            Sitengine_Env::PARAM_ANCESTORID => $user['id']
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS_MEMBERSHIPS);
        $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        $level = array();
        $level['name'] = $this->_controller->getTranslate()->translate('breadcrumbsMembershipEntity');
        $level['uriIndex'] = $uriIndex;
        
        if($breadcrumbs['membership'] !== null)
        {
        	#Sitengine_Debug::print_r($breadcrumbs['membership']);
        	$table = $this->_controller->getFrontController()->getPermiso()->getMembershipsTable();
        	$membership = $table->complementRow($breadcrumbs['membership']);
        	
            $args = array(
                Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
                Sitengine_Env::PARAM_ID => $membership['id']
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS_MEMBERSHIPS_SHARP);
            $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            $level['uriUpdate'] = $uriUpdate;
            $level['title'] = $breadcrumbs['membership']->groupId;
            $level = array_merge($membership, $level);
        }
        $data['membership'] = $level;
        return $data;
    }
}

?>