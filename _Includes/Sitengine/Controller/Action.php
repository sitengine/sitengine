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
 * @package    Sitengine
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Zend/Controller/Action.php';


abstract class Sitengine_Controller_Action extends Zend_Controller_Action
{
	
	
	public function __construct(
		Zend_Controller_Request_Abstract $request,
    	Zend_Controller_Response_Abstract $response,
    	array $invokeArgs = array()
	)
	{
		
		if(!$request instanceof Sitengine_Controller_Request_Http)
		{
			require_once 'Sitengine/Controller/Exception.php';
			throw new Sitengine_Controller_Exception('request must be an instance of Sitengine_Controller_Request_Http');
		}
		
		if(!$response instanceof Zend_Controller_Response_Abstract)
		{
			require_once 'Sitengine/Controller/Exception.php';
			throw new Sitengine_Controller_Exception('response must be an instance of Zend_Controller_Response_Abstract');
		}
		
		parent::__construct($request, $response, $invokeArgs);
	}
	
	
	

	protected function _goToAction($action)
    {
    	$handler = $action.'Action';
    	if(method_exists($this, $handler))
    	{
    		$this->getRequest()->setActionName($action);
    		return call_user_func(array($this, $handler));
    	}
		require_once 'Sitengine/Controller/Exception.php';
		throw new Sitengine_Controller_Exception('trying to forward to a non-existing action handler');
    }
    
    
    /*
    protected function _prepareErrorHandler(Exception $exception)
    {
    	
    	require_once 'Sitengine/Env.php';
		require_once 'Sitengine/Error/Controller.php';
		
		switch($exception->getCode())
		{
			case Sitengine_Env::ERROR_NOT_FOUND:
				$handler = Sitengine_Error_Controller::ACTION_NOT_FOUND;
				break;
			case Sitengine_Env::ERROR_BAD_REQUEST:
				$handler = Sitengine_Error_Controller::ACTION_BAD_REQUEST;
				break;
			case Sitengine_Env::ERROR_FORBIDDEN:
				$handler = Sitengine_Error_Controller::ACTION_FORBIDDEN;
				break;
			case Sitengine_Env::ERROR_NOT_IMPLEMENTED:
				$handler = Sitengine_Error_Controller::ACTION_NOT_IMPLEMENTED;
				break;
			case Sitengine_Env::ERROR_METHOD_NOT_SUPPORTED:
				$handler = Sitengine_Error_Controller::ACTION_METHOD_NOT_SUPPORTED;
				break;
			default:
				$handler = Sitengine_Error_Controller::ACTION_INTERNAL_SERVER_ERROR;
		}
		
		$pluginClass = 'Zend_Controller_Plugin_ErrorHandler';
		if($this->getFrontController()->hasPlugin($pluginClass))
		{
			$this->getFrontController()->getPlugin($pluginClass)->setErrorHandlerAction($handler);
		}
		
		return $exception;
    }
	*/
}

?>