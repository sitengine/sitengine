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


require_once 'Sitengine/Controller/Front.php';


abstract class Sitengine_Permiso_Backend_Front extends Sitengine_Controller_Front
{
	
    const CONTROLLER_USERS = 'users';
    const CONTROLLER_USERS_MEMBERSHIPS = 'memberships';
    const CONTROLLER_GROUPS = 'groups';
    const CONTROLLER_GROUPS_MEMBERS = 'members';
    const CONTROLLER_LOGIN = 'login';
    const CONTROLLER_ERROR = 'error';
    const ROUTE_USERS = 'users';
    const ROUTE_USERS_MEMBERSHIPS = 'memberships';
    const ROUTE_GROUPS = 'groups';
    const ROUTE_GROUPS_MEMBERS = 'members';
    const ROUTE_LOGIN = 'login';
    
    
    public function start(Sitengine_Env $env, $configName = null)
    {
    	if(!$env instanceof Sitengine_Env_Default) {
    		require_once 'Sitengine/Permiso/Backend/Exception.php';
    		$message = 'env must be an instance of Sitengine_Env_Default';
    		throw new Sitengine_Permiso_Backend_Exception($message);
    	}
    	if($configName === null)
    	{
    		# set default
    		$configName = 'permiso';
    	}
    	return parent::start($env, $configName);
    }
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	if(!isset($config->{$this->getConfigName()}->backend->frontController))
    	{
    		require_once 'Sitengine/Permiso/Backend/Exception.php';
    		$message = "front controller: {$this->getConfigName()} config not found";
    		throw new Sitengine_Permiso_Backend_Exception($message);
    	}
    	
    	$config = $config->{$this->getConfigName()}->backend->frontController;
    	
    	if(
    		isset($config->servicePath) &&
			isset($config->usersController) &&
			isset($config->usersMembershipsController) &&
			isset($config->groupsController) &&
			isset($config->groupsMembersController) &&
			isset($config->loginController) &&
			isset($config->errorController)
		)
		{
			$this->setServicePath($config->servicePath);
			
			$this->_controllers = array(
				self::CONTROLLER_USERS => $config->usersController,
				self::CONTROLLER_USERS_MEMBERSHIPS => $config->usersMembershipsController,
				self::CONTROLLER_GROUPS => $config->groupsController,
				self::CONTROLLER_GROUPS_MEMBERS => $config->groupsMembersController,
				self::CONTROLLER_LOGIN => $config->loginController,
				self::CONTROLLER_ERROR => $config->errorController
			);
		}
		else {
			require_once 'Sitengine/Permiso/Backend/Exception.php';
    		$message = 'front controller config error';
    		throw new Sitengine_Permiso_Backend_Exception($message);
		}
    }
    
    
    protected function _getRouterInstance()
    {
    	require_once 'Zend/Controller/Router/Route.php';
    	$path = $this->getServicePath();
    	
    	$routes = array(
			'default' => new Zend_Controller_Router_Route(
				'*',
				array(
					Sitengine_Env::PARAM_CONTROLLER => $this->getController(self::CONTROLLER_USERS),
					Sitengine_Env::PARAM_ACTION => 'index'
				)
			),
			self::ROUTE_USERS => new Zend_Controller_Router_Route(
				"$path/users/:".Sitengine_Env::PARAM_ACTION."/*",
				array(
					Sitengine_Env::PARAM_CONTROLLER => $this->getController(self::CONTROLLER_USERS),
					Sitengine_Env::PARAM_ACTION => 'index'
				)
			),
			self::ROUTE_USERS_MEMBERSHIPS => new Zend_Controller_Router_Route(
				"$path/users/memberships/:".Sitengine_Env::PARAM_ACTION."/*",
				array(
					Sitengine_Env::PARAM_CONTROLLER => $this->getController(self::CONTROLLER_USERS_MEMBERSHIPS),
					Sitengine_Env::PARAM_ACTION => 'index'
				)
			),
			self::ROUTE_GROUPS => new Zend_Controller_Router_Route(
				"$path/groups/:".Sitengine_Env::PARAM_ACTION."/*",
				array(
					Sitengine_Env::PARAM_CONTROLLER => $this->getController(self::CONTROLLER_GROUPS),
					Sitengine_Env::PARAM_ACTION => 'index'
				)
			),
			self::ROUTE_GROUPS_MEMBERS => new Zend_Controller_Router_Route(
				"$path/groups/members/:".Sitengine_Env::PARAM_ACTION."/*",
				array(
					Sitengine_Env::PARAM_CONTROLLER => $this->getController(self::CONTROLLER_GROUPS_MEMBERS),
					Sitengine_Env::PARAM_ACTION => 'index'
				)
			),
			self::ROUTE_LOGIN => new Zend_Controller_Router_Route(
				"$path/login",
				array(
					Sitengine_Env::PARAM_CONTROLLER => $this->getController(self::CONTROLLER_LOGIN),
					Sitengine_Env::PARAM_ACTION => 'index'
				)
			)
		);
		require_once 'Zend/Controller/Router/Rewrite.php';
		$router = new Zend_Controller_Router_Rewrite();
		$router->removeDefaultRoutes();
		$router->addRoutes($routes);
		return $router;
    }
    
    
    
    protected $_permisoPackage = null;
    
    public function getPermisoPackage()
    {
    	if($this->_permisoPackage === null) {
    		$this->_permisoPackage = $this->_getPermisoPackageInstance();
    	}
    	return $this->_permisoPackage;
    }
    
    abstract protected function _getPermisoPackageInstance();
    
    
    
    protected function _getErrorHandlerInstance()
    {
    	require_once 'Zend/Controller/Plugin/ErrorHandler.php';
		$plugin = new Zend_Controller_Plugin_ErrorHandler();
		$plugin->setErrorHandlerController($this->getController(self::CONTROLLER_ERROR));
		return $plugin;
    }
    
    
    
    public function getQueries(Sitengine_Permiso $permiso)
    {
    	$queries = array();
    	
    	require_once 'Zend/Controller/Router/Route/Module.php';
    	$route = new Zend_Controller_Router_Route_Module(array(), $this->getDispatcher(), $this->getRequest());
    	
    	
    	$args = array(
            #Sitengine_Env::PARAM_ORG => $permiso->getOrganization()->getNameNoDefault()
        );
        $queries['backendHome'] = $this->getEnv()->getMyProjectRequestDir().'/backend/home/'.$route->assemble($args , true);
        
        
        $args = array(
    		#Sitengine_Env::PARAM_ORG => $permiso->getOrganization()->getNameNoDefault(),
            Sitengine_Env::PARAM_LOGOUT => 1
        );
        $queries['signOut'] = $this->getRequest()->getBasePath().'/backend/home'.Sitengine_Controller_Request_Http::makeNameValueQuery($args);
        
        
        $args = array(
        	#Sitengine_Env::PARAM_CONTROLLER => 'users',
            #Sitengine_Env::PARAM_ACTION => 'me',
            #Sitengine_Env::PARAM_ORG => $permiso->getOrganization()->getNameNoDefault()
        );
        $queries['myAccount'] = $this->getRequest()->getBasePath().'/backend/users/me'.$route->assemble($args , true);
        
        
        $args = array(
        	#Sitengine_Env::PARAM_CONTROLLER => 'users',
            #Sitengine_Env::PARAM_ACTION => 'index',
            #Sitengine_Env::PARAM_ORG => $permiso->getOrganization()->getNameNoDefault()
        );
        $queries['permisoBackendUsers'] = $this->getRequest()->getBasePath().'/backend/users/'.$route->assemble($args , true);
        
        
        $args = array(
        	#Sitengine_Env::PARAM_CONTROLLER => 'groups',
            #Sitengine_Env::PARAM_ACTION => 'index',
            #Sitengine_Env::PARAM_ORG => $permiso->getOrganization()->getNameNoDefault()
        );
        $queries['permisoBackendGroups'] = $this->getRequest()->getBasePath().'/backend/groups/'.$route->assemble($args , true);
        
        return $queries;
    }
    
    
    
    
    
    
    
    public function getGlobalNavSection(
        Sitengine_Permiso $permiso,
        Sitengine_Translate $translate,
        array $queries,
        $current,
        $selectorPrefix=''
    )
    {
        $selected = '';
        $items = array();
        
        $location = 'backendHome';
        $items[$queries[$location]] = '> '.$translate->translate('labels'.ucfirst($location));
        $selected = ($current==$location) ? $queries[$location] : $selected;
        
        $items['separator10'] = '----------------';
        /*
        $location = 'permisoBackendOrganizations';
        $items[$queries[$location]] = '> '.$translate->translate('labels'.ucfirst($location));
        $selected = ($current==$location) ? $queries[$location] : $selected;
        */
        $location = 'permisoBackendUsers';
        $items[$queries[$location]] = '> '.$translate->translate('labels'.ucfirst($location));
        $selected = ($current==$location) ? $queries[$location] : $selected;
        
        $location = 'permisoBackendGroups';
        $items[$queries[$location]] = '> '.$translate->translate('labels'.ucfirst($location));
        $selected = ($current==$location) ? $queries[$location] : $selected;
        
        
        $n = 'globalNav';
        require_once 'Sitengine/Form/Element.php';
        $e = new Sitengine_Form_Element('', $selected);
        $e->setScript('onchange="if(!this.options[this.selectedIndex].value.match(/^separator/)) { window.location=this.options[this.selectedIndex].value; }"');
        $e->setClass($selectorPrefix.'Select');
        $e->setId($selectorPrefix.$n);
        
        return array(
            'ITEMS' => $items,
            'menu' => $e->getSelect($items)
        );
    }
    
}
?>