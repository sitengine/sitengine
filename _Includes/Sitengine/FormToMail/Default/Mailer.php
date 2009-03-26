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
 * @package    Sitengine_FormToMail
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */



abstract class Sitengine_FormToMail_Default_Mailer
{
    
    protected $_controller = null;
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_FormToMail_Default_Controller)
    	{
    		$this->_controller = $config['controller'];
    	}
    	else {
    		require_once 'Sitengine/Exception.php';
        	throw new Sitengine_Exception('construct error');
    	}
    }
    
    
    public function checkInput()
    {
    	require_once 'Sitengine/Validator.php';
    	
    	$name = 'firstname';
		if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
			$message = $this->_controller->getTranslate()->translate('hintFirstnameRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		$name = 'lastname';
		if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
			$message = $this->_controller->getTranslate()->translate('hintLastnameRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		$name = 'email';
		if(!Sitengine_Validator::emailAddress($this->_controller->getRequest()->getPost($name))) {
			$message = $this->_controller->getTranslate()->translate('hintEmailRequired');
			$this->_controller->getStatus()->addHint($name, $message);
		}
		return (!$this->_controller->getStatus()->hasHints());
    }
    
    
    public function send()
    {
    	$view = $this->_controller->getMessageInstance();
		$view->setHelperPath($this->_controller->getEnv()->getIncludesDir());
		$view->setScriptPath(dirname($this->_controller->getMessageTemplate()));
		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
		$view->build()->batchAssign($view->getData());
		$body = $view->render(basename($this->_controller->getMessageTemplate()));
		
		require_once 'Zend/Mail.php';
		$mail = new Zend_Mail();
		$mail
			->setSubject($view->getSubject())
			->setBodyHtml($body)
			->setFrom(
				$this->_controller->getFrontController()->getFormToMailPackage()->getSenderEmail(),
				$this->_controller->getFrontController()->getFormToMailPackage()->getSenderName()
			)
			->addTo($this->_controller->getFrontController()->getFormToMailPackage()->getSenderEmail())
			->addTo($this->_controller->getRequest()->getPost('email'))
			->send()
		;
    }
    
}


?>