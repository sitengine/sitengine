<?php

require_once 'Zend/Controller/Plugin/Abstract.php';

class Sitengine_Controller_Plugin_Query extends Zend_Controller_Plugin_Abstract
{
	
	public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
    	require_once 'Sitengine/Env.php';
    	$frontController = Zend_Controller_Front::getInstance();
    	#print $request->getQuery(Sitengine_Env::PARAM_CONTROLLER);
    	#Sitengine_Debug::print_r($frontController->getControllers());
    	$controllerName = $frontController->getController($request->getQuery(Sitengine_Env::PARAM_CONTROLLER));
    	$request->setParam(Sitengine_Env::PARAM_CONTROLLER, $controllerName);
    	$request->setControllerName($controllerName);
    	$request->setActionName($request->getQuery(Sitengine_Env::PARAM_ACTION));
    	#$frontController->getDispatcher()->dispatch($request, $frontController->getResponse());
    }
    
}

?>