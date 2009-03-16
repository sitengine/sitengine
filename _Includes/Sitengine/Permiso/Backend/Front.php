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
    const CONTROLLER_USERS_MEMBERSHIPS = 'usersMemberships';
    const CONTROLLER_GROUPS = 'groups';
    const CONTROLLER_GROUPS_MEMBERS = 'groupsMembers';
    const CONTROLLER_LOGIN = 'login';
    const CONTROLLER_ERROR = 'error';
    const ROUTE_LOGIN = 'login';
    const ROUTE_USERS = 'users';
    const ROUTE_USERS_NEW = 'usersNew';
    const ROUTE_USERS_BATCH = 'usersBatch';
    const ROUTE_USERS_SHARP = 'usersSharp';
    const ROUTE_USERS_MEMBERSHIPS = 'usersMemberships';
    const ROUTE_USERS_MEMBERSHIPS_NEW = 'usersMembershipsNew';
    const ROUTE_USERS_MEMBERSHIPS_BATCH = 'usersMembershipsBatch';
    const ROUTE_USERS_MEMBERSHIPS_SHARP = 'usersMembershipsSharp';
    const ROUTE_GROUPS = 'groups';
    const ROUTE_GROUPS_NEW = 'groupsNew';
    const ROUTE_GROUPS_BATCH = 'groupsBatch';
    const ROUTE_GROUPS_SHARP = 'groupsSharp';
    const ROUTE_GROUPS_MEMBERS = 'groupsMembers';
    const ROUTE_GROUPS_MEMBERS_NEW = 'groupsMembersNew';
    const ROUTE_GROUPS_MEMBERS_BATCH = 'groupsMembersBatch';
    const ROUTE_GROUPS_MEMBERS_SHARP = 'groupsMembersSharp';
    
    
    
    protected $_permisoPackage = null;
    
    public function getPermisoPackage()
    {
    	if($this->_permisoPackage === null) {
    		$this->_permisoPackage = $this->_getPermisoPackageInstance();
    	}
    	return $this->_permisoPackage;
    }
    
    abstract protected function _getPermisoPackageInstance();
    
    
    
    
    
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
    	$path = $this->getServicePath();
    	
    	$routes = array(
			'default' 					=> $this->_getRoute('*', self::CONTROLLER_USERS),
			self::ROUTE_LOGIN 			=> $this->_getRoute("$path/login", self::CONTROLLER_LOGIN),
			self::ROUTE_USERS 			=> $this->_getRoute("$path/users", self::CONTROLLER_USERS),
			self::ROUTE_USERS_SHARP 	=> $this->_getRoute("$path/users/:id", self::CONTROLLER_USERS),
			self::ROUTE_USERS_NEW 		=> $this->_getRoute("$path/users/new", self::CONTROLLER_USERS),
			self::ROUTE_USERS_BATCH 	=> $this->_getRoute("$path/users/batch", self::CONTROLLER_USERS),
			self::ROUTE_USERS_MEMBERSHIPS 			=> $this->_getRoute("$path/users/:aid/memberships", self::CONTROLLER_USERS_MEMBERSHIPS),
			self::ROUTE_USERS_MEMBERSHIPS_SHARP		=> $this->_getRoute("$path/users/:aid/memberships/:id", self::CONTROLLER_USERS_MEMBERSHIPS),
			self::ROUTE_USERS_MEMBERSHIPS_NEW 		=> $this->_getRoute("$path/users/:aid/memberships/new", self::CONTROLLER_USERS_MEMBERSHIPS),
			self::ROUTE_USERS_MEMBERSHIPS_BATCH 	=> $this->_getRoute("$path/users/:aid/memberships/batch", self::CONTROLLER_USERS_MEMBERSHIPS),
			self::ROUTE_GROUPS 			=> $this->_getRoute("$path/groups", self::CONTROLLER_GROUPS),
			self::ROUTE_GROUPS_SHARP 	=> $this->_getRoute("$path/groups/:id", self::CONTROLLER_GROUPS),
			self::ROUTE_GROUPS_NEW 		=> $this->_getRoute("$path/groups/new", self::CONTROLLER_GROUPS),
			self::ROUTE_GROUPS_BATCH 	=> $this->_getRoute("$path/groups/batch", self::CONTROLLER_GROUPS),
			self::ROUTE_GROUPS_MEMBERS 			=> $this->_getRoute("$path/groups/:aid/members", self::CONTROLLER_GROUPS_MEMBERS),
			self::ROUTE_GROUPS_MEMBERS_SHARP		=> $this->_getRoute("$path/groups/:aid/members/:id", self::CONTROLLER_GROUPS_MEMBERS),
			self::ROUTE_GROUPS_MEMBERS_NEW 		=> $this->_getRoute("$path/groups/:aid/members/new", self::CONTROLLER_GROUPS_MEMBERS),
			self::ROUTE_GROUPS_MEMBERS_BATCH 	=> $this->_getRoute("$path/groups/:aid/members/batch", self::CONTROLLER_GROUPS_MEMBERS),
		);
		require_once 'Zend/Controller/Router/Rewrite.php';
		$router = new Zend_Controller_Router_Rewrite();
		$router->removeDefaultRoutes();
		$router->addRoutes($routes);
		return $router;
    }
    
    
    
    protected function _getRoute($uri, $controller)
    {
    	$defaults = array(
			Sitengine_Env::PARAM_CONTROLLER => $this->getController($controller),
			Sitengine_Env::PARAM_ACTION => 'factory'
		);
		require_once 'Sitengine/Controller/Router/Route.php';
    	$route = new Sitengine_Controller_Router_Route($uri, $defaults);
    	return $route->setRepresentationParam(Sitengine_Env::PARAM_REPRESENTATION);
    }
    
    
    
    protected function _getErrorHandlerInstance()
    {
    	require_once 'Zend/Controller/Plugin/ErrorHandler.php';
		$plugin = new Zend_Controller_Plugin_ErrorHandler();
		$plugin->setErrorHandlerController($this->getController(self::CONTROLLER_ERROR));
		return $plugin;
    }
    
    
    
    
    
    public function getQueries()
    {
    	$queries = array();
    	
    	$args = array(
            Sitengine_Env::PARAM_LOGOUT => 1
        );
        $queries['signOut'] = $this->getRequest()->getBasePath().'/backend/permiso/users'.Sitengine_Controller_Request_Http::makeNameValueQuery($args);
        $queries['backendHome'] = $this->getEnv()->getMyProjectRequestDir().'/backend/home';
        $queries['permisoBackendUsers'] = $this->getRequest()->getBasePath().'/backend/users';
        $queries['permisoBackendGroups'] = $this->getRequest()->getBasePath().'/backend/groups';
        return $queries;
    }
    
    
    
    
    
    
    public function getGlobalNavSection(
        Sitengine_Dictionary $dictionary,
        array $queries,
        $current,
        $selectorPrefix=''
    )
    {
        $selected = '';
        $items = array();
        
        $location = 'backendHome';
        $items[$queries[$location]] = '> '.$dictionary->getFromLabels($location);
        
        $items['separator10'] = '----------------';
        
        $location = 'permisoBackendUsers';
        $items[$queries[$location]] = '> '.$dictionary->getFromLabels($location);
        $selected = ($current=='permisoBackendUsers') ? $queries[$location] : $selected;
        
        $location = 'permisoBackendGroups';
        $items[$queries[$location]] = '> '.$dictionary->getFromLabels($location);
        $selected = ($current=='permisoBackendGroups') ? $queries[$location] : $selected;
        
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