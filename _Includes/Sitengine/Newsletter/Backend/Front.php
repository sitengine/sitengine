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
 * @package    Sitengine_Newsletter
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/Controller/Front.php';


abstract class Sitengine_Newsletter_Backend_Front extends Sitengine_Controller_Front
{
	
	const CONTROLLER_CAMPAIGNS = 'campaigns';
    const CONTROLLER_CAMPAIGNS_ATTACHMENTS = 'campaignsAttachments';
    const CONTROLLER_LOGIN = 'login';
    const CONTROLLER_ERROR = 'error';
    const ROUTE_LOGIN = 'login';
    const ROUTE_CAMPAIGNS = 'campaigns';
    const ROUTE_CAMPAIGNS_NEW = 'campaignsNew';
    const ROUTE_CAMPAIGNS_BATCH = 'campaignsBatch';
    const ROUTE_CAMPAIGNS_SHARP = 'campaignsSharp';
    #const ROUTE_CAMPAIGNS_UPLOAD = 'campaignsUpload';
    #const ROUTE_CAMPAIGNS_ASSIGN = 'campaignsAssign';
    const ROUTE_CAMPAIGNS_ATTACHMENTS = 'campaignsAttachments';
    const ROUTE_CAMPAIGNS_ATTACHMENTS_NEW = 'campaignsAttachmentsNew';
    const ROUTE_CAMPAIGNS_ATTACHMENTS_BATCH = 'campaignsAttachmentsBatch';
    const ROUTE_CAMPAIGNS_ATTACHMENTS_SHARP = 'campaignsAttachmentsSharp';
    const ROUTE_CAMPAIGNS_ATTACHMENTS_UPLOAD = 'campaignsAttachmentsUpload';
    const ROUTE_CAMPAIGNS_ATTACHMENTS_ASSIGN = 'campaignsAttachmentsAssign';
    const ROUTE_CAMPAIGNS_ATTACHMENTS_JSURLS = 'campaignsAttachmentsJsUrls';
    
    
    
    protected $_newsletterPackage = null;
    
    public function getNewsletterPackage()
    {
    	if($this->_newsletterPackage === null) {
    		$this->_newsletterPackage = $this->_getNewsletterPackageInstance();
    	}
    	return $this->_newsletterPackage;
    }
    
    abstract protected function _getNewsletterPackageInstance();
    
    
    
    
    
    public function start(Sitengine_Env $env, $configName = null)
    {
    	if(!$env instanceof Sitengine_Env_Default) {
    		require_once 'Sitengine/Newsletter/Backend/Exception.php';
    		$message = 'env must be an instance of Sitengine_Env_Default';
    		throw new Sitengine_Newsletter_Backend_Exception($message);
    	}
    	if($configName === null)
    	{
    		# set default
    		$configName = 'newsletter';
    	}
    	return parent::start($env, $configName);
    }
    
    
    
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	if(!isset($config->{$this->getConfigName()}->backend->frontController))
    	{
    		require_once 'Sitengine/Newsletter/Backend/Exception.php';
    		$message = "front controller: {$this->getConfigName()} config not found";
    		throw new Sitengine_Newsletter_Backend_Exception($message);
    	}
    	
    	$config = $config->{$this->getConfigName()}->backend->frontController;
    	
    	if(
    		isset($config->servicePath) &&
			isset($config->campaignsController) &&
			isset($config->campaignsAttachmentsController) &&
			isset($config->loginController) &&
			isset($config->errorController)
		)
		{
			$this->setServicePath($config->servicePath);
			$this->_controllers = array(
				self::CONTROLLER_CAMPAIGNS => $config->campaignsController,
				self::CONTROLLER_CAMPAIGNS_ATTACHMENTS => $config->campaignsAttachmentsController,
				self::CONTROLLER_LOGIN => $config->loginController,
				self::CONTROLLER_ERROR => $config->errorController
			);
		}
		else {
			require_once 'Sitengine/Newsletter/Backend/Exception.php';
    		$message = 'front controller config error';
    		throw new Sitengine_Newsletter_Backend_Exception($message);
		}
    }
    
    
    
    protected function _getRouterInstance()
    {
    	$path = $this->getServicePath();
    	
    	$routes = array(
			'default' 					=> $this->_getRoute('*', self::CONTROLLER_CAMPAIGNS),
			self::ROUTE_LOGIN 			=> $this->_getRoute("$path/login", self::CONTROLLER_LOGIN),
			
			self::ROUTE_CAMPAIGNS 		=> $this->_getRoute("$path/campaigns", self::CONTROLLER_CAMPAIGNS),
			self::ROUTE_CAMPAIGNS_SHARP 	=> $this->_getRoute("$path/campaigns/:id", self::CONTROLLER_CAMPAIGNS),
			self::ROUTE_CAMPAIGNS_NEW 	=> $this->_getRoute("$path/campaigns/new", self::CONTROLLER_CAMPAIGNS),
			self::ROUTE_CAMPAIGNS_BATCH 	=> $this->_getRoute("$path/campaigns/batch", self::CONTROLLER_CAMPAIGNS),
			#self::ROUTE_CAMPAIGNS_UPLOAD 	=> $this->_getRoute("$path/campaigns/upload", self::CONTROLLER_CAMPAIGNS),
			#self::ROUTE_CAMPAIGNS_ASSIGN 	=> $this->_getRoute("$path/campaigns/assign", self::CONTROLLER_CAMPAIGNS),
			
			self::ROUTE_CAMPAIGNS_ATTACHMENTS 			=> $this->_getRoute("$path/campaigns/:aid/attachments", self::CONTROLLER_CAMPAIGNS_ATTACHMENTS),
			self::ROUTE_CAMPAIGNS_ATTACHMENTS_SHARP		=> $this->_getRoute("$path/campaigns/:aid/attachments/:id", self::CONTROLLER_CAMPAIGNS_ATTACHMENTS),
			self::ROUTE_CAMPAIGNS_ATTACHMENTS_NEW 		=> $this->_getRoute("$path/campaigns/:aid/attachments/new", self::CONTROLLER_CAMPAIGNS_ATTACHMENTS),
			self::ROUTE_CAMPAIGNS_ATTACHMENTS_BATCH 	=> $this->_getRoute("$path/campaigns/:aid/attachments/batch", self::CONTROLLER_CAMPAIGNS_ATTACHMENTS),
			self::ROUTE_CAMPAIGNS_ATTACHMENTS_UPLOAD 	=> $this->_getRoute("$path/campaigns/:aid/attachments/upload", self::CONTROLLER_CAMPAIGNS_ATTACHMENTS),
			self::ROUTE_CAMPAIGNS_ATTACHMENTS_ASSIGN 	=> $this->_getRoute("$path/campaigns/:aid/attachments/assign", self::CONTROLLER_CAMPAIGNS_ATTACHMENTS),
			self::ROUTE_CAMPAIGNS_ATTACHMENTS_JSURLS 	=> $this->_getRoute("$path/campaigns/:aid/attachments/jsurls", self::CONTROLLER_CAMPAIGNS_ATTACHMENTS),
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
			Sitengine_Env::PARAM_ACTION => 'restMapper'
		);
		require_once 'Zend/Controller/Router/Route.php';
    	return new Zend_Controller_Router_Route($uri, $defaults);
    }
    
    
    
    protected function _getErrorHandlerInstance()
    {
    	require_once 'Zend/Controller/Plugin/ErrorHandler.php';
		$plugin = new Zend_Controller_Plugin_ErrorHandler();
		$plugin->setErrorHandlerController($this->getController(self::CONTROLLER_ERROR));
		return $plugin;
    }
    
    
    
    
    
    protected $_permiso = null;
    
    public function getPermiso()
    {
    	if($this->_permiso === null) {
    		$this->_permiso = $this->_getPermisoInstance();
    	}
    	return $this->_permiso;
    }
    
    abstract protected function _getPermisoInstance();
    
    
    
    
    
    public function getQueries(Sitengine_Permiso $permiso)
    {
    	$queries = array();
    	
    	$args = array(
            Sitengine_Env::PARAM_LOGOUT => 1
        );
        $queries['signOut'] = $this->getRequest()->getBasePath().'/backend/newsletter/campaigns'.Sitengine_Controller_Request_Http::makeNameValueQuery($args);
        $queries['backendHome'] = $this->getEnv()->getMyProjectRequestDir().'/backend/home';
        $queries['newsletterBackendCampaigns'] = $this->getRequest()->getBasePath().'/backend/newsletter/campaigns';
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
        
        $items['separator10'] = '----------------';
        
        $location = 'newsletterBackendCampaigns';
        $items[$queries[$location]] = '> '.$translate->translate('labels'.ucfirst($location));
        $selected = ($current=='newsletterBackendCampaigns') ? $queries[$location] : $selected;
        
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