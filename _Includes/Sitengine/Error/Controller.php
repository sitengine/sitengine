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
 * @package    Sitengine_Error
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/Controller/Action.php';


abstract class Sitengine_Error_Controller extends Sitengine_Controller_Action
{
    
    const ACTION_NOT_FOUND = 'notFoundError';
    const ACTION_BAD_REQUEST = 'badRequestError';
    const ACTION_INTERNAL = 'internalError';
    const ACTION_UNAUTHORIZED = 'unauthorizedError';
    const ACTION_NOT_SUPPORTED = 'notSupportedError';
	
	
	protected $_started = false;
	protected $_config = null;
    protected $_env = null;
    protected $_status = null;
    protected $_locale = null;
    protected $_translate = null;
    protected $_defaultLanguage = 'en';
    protected $_templateIndexView = null;
    
    
    public function getEnv() { return $this->_env; }
    public function getStatus() { return $this->_status; }
    public function getLocale() { return $this->_locale; }
    public function getTranslate() { return $this->_translate; }
    
    
    # objects that are not initialized on controller init
    private $_viewHelper = null;
    
    public function getViewHelper()
    {
    	if($this->_viewHelper === null) {
    		$this->_viewHelper = $this->_getViewHelperInstance();
    	}
    	return $this->_viewHelper;
    }
    
    abstract protected function _getViewHelperInstance();
    
    
	
    public function __construct(
    	Sitengine_Controller_Request_Http $request,
    	Zend_Controller_Response_Http $response,
    	array $invokeArgs
    )
    {
        try {
        	parent::__construct($request, $response, $invokeArgs);
        	$this->_mapInvokeArgs($invokeArgs);
        	$this->_mapConfig($this->_config);
        	
        	require_once 'Sitengine/Status.php';
			$this->_status = Sitengine_Status::getInstance();
			$this->_locale = $this->getEnv()->getLocaleInstance();
        	$this->_translate = $this->_getTranslateInstance();
        	$this->_templateIndexView = $this->getEnv()->getIncludesDir().'/Sitengine/Error/_Templates/IndexView.html';
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Error/Exception.php';
            throw new Sitengine_Error_Exception('object instantiation error', $exception);
        }
    }
    
    
    
    protected function _mapInvokeArgs(array $invokeArgs)
    {
    	if(
    		array_key_exists('env', $invokeArgs) &&
    		$invokeArgs['env'] instanceof Sitengine_Env &&
    		array_key_exists('config', $invokeArgs) &&
    		$invokeArgs['config'] instanceof Zend_Config
    	)
    	{
    		$this->_env = $invokeArgs['env'];
    		$this->_config = $invokeArgs['config'];
    	}
    	else {
    		require_once 'Sitengine/Error/Exception.php';
    		throw new Sitengine_Error_Exception('invalid invoke args');
    	}
    }
    
    
    
    protected function _mapConfig(Zend_Config $config) {}
    
    
    
    abstract protected function _getIndexViewInstance();
    
    
    
    
    public function getExceptionInstance($message, $p2 = null, $p3 = null, $priority = null)
    {
    	require_once 'Sitengine/Error/Exception.php';
        return new Sitengine_Error_Exception($message, $p2, $p3, $priority);
    }
    
    
    protected function _getTranslateInstance()
    {
    	require_once 'Sitengine/Translate.php';
		$translate = new Sitengine_Translate(
			Sitengine_Translate::AN_XML,
			$this->getEnv()->getIncludesDir().'/Sitengine/Env/_Dictionary/global.xml',
			Sitengine_Env::LANGUAGE_EN
		);
		
		$en = array(
			$this->getEnv()->getIncludesDir().'/Sitengine/Env/_Dictionary/en.xml',
			$this->getEnv()->getIncludesDir().'/Sitengine/Error/_Dictionary/en.xml'
		);
		
		$de = array(
			$this->getEnv()->getIncludesDir().'/Sitengine/Env/_Dictionary/global.xml',
			$this->getEnv()->getIncludesDir().'/Sitengine/Env/_Dictionary/de.xml',
			$this->getEnv()->getIncludesDir().'/Sitengine/Error/_Dictionary/de.xml'
		);
		
		$translate->addMergeTranslation($en, Sitengine_Env::LANGUAGE_EN);
		$translate->addMergeTranslation($de, Sitengine_Env::LANGUAGE_DE);
		return $translate;
    }
    
    
    protected function _start()
    {
    	try {
			if(!$this->_started)
			{
				$this->_started = true;
				
				$this->getLocale()->setLocale(Sitengine_Env::LANGUAGE_EN);
				
				if($this->getTranslate()->isAvailable($this->getPreferences()->getLanguage()))
				{
					$this->getLocale()->setLocale($this->getPreferences()->getLanguage());
					$this->getTranslate()->setLocale($this->getPreferences()->getLanguage());
				}
				
				require_once 'Zend/Registry.php';
				Zend_Registry::set('Zend_Translate', $this->getTranslate()->getAdapter());
			}
		}
        catch (Exception $exception) {
            require_once 'Sitengine/Error/Exception.php';
            throw new Sitengine_Error_Exception('init error', $exception);
        }
    }
    
    
    public function __call($handler, $args)
    {
    	$errors = $this->_getParam('error_handler');
		require_once 'Zend/Controller/Plugin/ErrorHandler.php';
		
    	if(
    		preg_match('/^'.self::ACTION_NOT_FOUND.'\w*$/i', $handler) ||
    		(isset($errors->type) && $errors->type == Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER) ||
    		(isset($errors->type) && $errors->type == Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION)
    	) {
    		return $this->_notFoundErrorAction();
    	}
    	else if(preg_match('/^'.self::ACTION_BAD_REQUEST.'\w*$/i', $handler)) {
    		return $this->_badRequestErrorAction();
    	}
    	else if(preg_match('/^'.self::ACTION_UNAUTHORIZED.'\w*$/i', $handler)) {
    		return $this->_unauthorizedErrorAction();
    	}
    	else if(preg_match('/^'.self::ACTION_NOT_SUPPORTED.'\w*$/i', $handler)) {
    		return $this->_notSupportedErrorAction();
    	}
    	else {
    		return $this->_internalErrorAction();
    	}
    }
    
    
    protected function _badRequestErrorAction()
    {
    	try {
    		$this->_start();
    		$this->getStatus()->set(
				Sitengine_Env::STATUS_BADREQUEST,
				$this->getTranslate()->translate(Sitengine_Env::STATUS_BADREQUEST),
				true
			);
			$view = $this->_getIndexViewInstance();
			$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateIndexView));
    		$view->doctype()->setDoctype('XHTML1_STRICT');
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateIndexView));
			$this->getResponse()->setBody($body);
			$this->getResponse()->setHttpResponseCode(500);
			$this->getResponse()->sendResponse();
		}
		catch (Exception $exception) {
            require_once 'Sitengine/Error/Exception.php';
            throw new Sitengine_Error_Exception('bad request action error', $exception);
        }
    }
    
    
    protected function _internalErrorAction()
    {
    	try {
    		$this->_start();
    		$this->getStatus()->set(
				Sitengine_Env::STATUS_ERROR,
				$this->getTranslate()->translate(Sitengine_Env::STATUS_ERROR),
				true
			);
			$view = $this->_getIndexViewInstance();
        	$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateIndexView));
    		$view->doctype()->setDoctype('XHTML1_STRICT');
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateIndexView));
			$this->getResponse()->setBody($body);
			$this->getResponse()->setHttpResponseCode(500);
			$this->getResponse()->sendResponse();
		}
		catch (Exception $exception) {
            require_once 'Sitengine/Error/Exception.php';
            throw new Sitengine_Error_Exception('internal action error', $exception);
        }
    }
    
    
    protected function _notFoundErrorAction()
    {
    	try {
    		$this->_start();
    		$this->getStatus()->set(
				Sitengine_Env::STATUS_NOTFOUND,
				$this->getTranslate()->translate(Sitengine_Env::STATUS_NOTFOUND),
				true
			);
			$view = $this->_getIndexViewInstance();
        	$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateIndexView));
    		$view->doctype()->setDoctype('XHTML1_STRICT');
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateIndexView));
			$this->getResponse()->setBody($body);
			$this->getResponse()->setHttpResponseCode(404);
    		#$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
			$this->getResponse()->sendResponse();
		}
		catch (Exception $exception) {
            require_once 'Sitengine/Error/Exception.php';
            throw new Sitengine_Error_Exception('not found action error', $exception);
        }
    }
    
    
    protected function _unauthorizedErrorAction()
    {
    	try {
    		$this->_start();
    		$this->getStatus()->set(
				Sitengine_Env::STATUS_UNAUTHORIZED,
				$this->getTranslate()->translate(Sitengine_Env::STATUS_UNAUTHORIZED),
				true
			);
			$view = $this->_getIndexViewInstance();
        	$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateIndexView));
    		$view->doctype()->setDoctype('XHTML1_STRICT');
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateIndexView));
			$this->getResponse()->setBody($body);
			$this->getResponse()->setHttpResponseCode(401);
			$this->getResponse()->sendResponse();
		}
		catch (Exception $exception) {
            require_once 'Sitengine/Error/Exception.php';
            throw new Sitengine_Error_Exception('not found action error', $exception);
        }
    }
    
    
    protected function _notSupportedErrorAction()
    {
    	try {
    		$this->_start();
    		$this->getStatus()->set(
				Sitengine_Env::STATUS_NOT_SUPPORTED,
				$this->getTranslate()->translate(Sitengine_Env::STATUS_NOT_SUPPORTED),
				true
			);
			$view = $this->_getIndexViewInstance();
        	$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateIndexView));
    		$view->doctype()->setDoctype('XHTML1_STRICT');
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateIndexView));
			$this->getResponse()->setBody($body);
			$this->getResponse()->setHttpResponseCode(500);
			$this->getResponse()->sendResponse();
		}
		catch (Exception $exception) {
            require_once 'Sitengine/Error/Exception.php';
            throw new Sitengine_Error_Exception('not found action error', $exception);
        }
    }
}
?>