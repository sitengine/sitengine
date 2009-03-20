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


abstract class Sitengine_Permiso_Login_IndexView extends Sitengine_View
{
    
	protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    protected $_stylesheets = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Permiso_Login_Controller)
    	{
    		$this->_controller = $config['controller'];
    	}
    	else {
    		require_once 'Sitengine/Exception.php';
        	throw new Sitengine_Exception('construct error');
    	}
    }
    
    
    public function build()
    {
        try {
        	$this->_controller->getViewHelper()->build();
        	
            $this->_sections = array(
				'LOGIN' => $this->getLoginSection(),
				'DBG' => $this->_controller->getViewHelper()->getDebugSection(),
				'LANGUAGE' => $this->_controller->getViewHelper()->getLanguageSection(),
				'TIMEZONE' => $this->_controller->getViewHelper()->getTimezoneSection()
			);
            return $this;
        }
        catch (Exception $exception) {
        	throw $this->_controller->getExceptionInstance('build page error', $exception);
        }
    }
    
    
    public function getData()
    {
    	try {
			return array(
				'STYLESHEETS' => $this->_stylesheets,
				'QUERIES' => $this->_queries,
				'SECTIONS' => $this->_sections,
				'SETTINGS' => $this->_settings,
				#'ENV' => $this->_controller->getEnv()->getData(),
				#'Env' => $this->_controller->getEnv(),
				#'STATUS' => $this->_controller->getStatus()->getData(),
				#'USER' => $this->_controller->getPermiso()->getAuth()->getData(),
				#'Auth' => $this->_controller->getPermiso()->getAuth(),
				#'DICTIONARY' => $this->_controller->getTranslate()->translateGroup('data')
			);
		}
        catch (Exception $exception) {
			throw $this->_controller->getExceptionInstance('build page error', $exception);
		}
    }
    
    
    public function getLoginSection()
    {
		try {
    		require_once 'Sitengine/Form/Element.php';
			require_once 'Sitengine/Env.php';
			
			$html = array();
			$selector = 'login';
			/*
			$hiddens = $this->_controller->getRequest()->getParams();
			unset($hiddens[Sitengine_Env::PARAM_MODULE]);
			unset($hiddens[Sitengine_Env::PARAM_CONTROLLER]);
			unset($hiddens[Sitengine_Env::PARAM_ACTION]);
			unset($hiddens[Sitengine_Env::PARAM_LOGOUT]);
			unset($hiddens[Sitengine_Env::PARAM_LOGINUSER]);
			unset($hiddens[Sitengine_Env::PARAM_LOGINPASS]);
			unset($hiddens[Sitengine_Env::PARAM_ORG]);
			unset($hiddens[Sitengine_Env::PARAM_REMEMBERME]);
			unset($hiddens[Sitengine_Env::PARAM_HANDLER]);
			*/
			$hiddens = array(
				Sitengine_Env::PARAM_TARGET => $this->_controller->getRequest()->getParam(Sitengine_Env::PARAM_TARGET)
			);
			
			foreach($hiddens as $k => $v) {
				$hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
			}
			
			$v = $this->_controller->getRequest()->getPost(Sitengine_Env::PARAM_LOGINUSER);
			$v = ($v) ? $v : '';
			$e = new Sitengine_Form_Element(Sitengine_Env::PARAM_LOGINUSER, $v);
			$e->setClass($selector.'Input');
			$e->setId($selector.Sitengine_Env::PARAM_LOGINUSER);
			$html[Sitengine_Env::PARAM_LOGINUSER] = $e->getText(20);
			
			$e = new Sitengine_Form_Element(Sitengine_Env::PARAM_LOGINPASS, '');
			$e->setClass($selector.'Input');
			$e->setId($selector.Sitengine_Env::PARAM_LOGINPASS);
			$html[Sitengine_Env::PARAM_LOGINPASS] = $e->getText(20, 'password');
			
			$param = Sitengine_Env::PARAM_REMEMBERME;
			$v = (isset($this->_controller->getNamespace()->$param)) ? Sitengine_String::runtimeStripSlashes($this->_controller->getNamespace()->$param) : '';
			$v = ($this->_controller->getRequest()->get($param)) ? $this->_controller->getRequest()->get($param) : $v;
			$e = new Sitengine_Form_Element($param, '');
			$e->setClass($selector.'Checkbox');
			$e->setId($selector.$param);
			$html[$param] = $e->getCheckbox($v);
			
			return array(
				'handler' => $this->_controller->getRequest()->getParam(Sitengine_Env::PARAM_HANDLER),
				'hiddens' => implode('', $hiddens),
				'ELEMENTS' => $html
			);
		}
		catch (Exception $exception) {
			throw $this->_controller->getExceptionInstance('error creating login form', $exception);
		}
    }
    
}


?>