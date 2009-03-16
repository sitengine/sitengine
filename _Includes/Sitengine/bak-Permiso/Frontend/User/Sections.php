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
 * @package    Sitengine_Permiso_Frontend_User
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */



abstract class Sitengine_Permiso_Frontend_User_Sections {
    
    
    protected $_controller = null;
    
    
    public function __construct(Sitengine_Permiso_Frontend_User_Controller $controller)
    {
        $this->_controller = $controller;
    }
    
    
    
    public function makeLoginFormSection(array $hiddens = array())
    {
    	try {
    		require_once 'Sitengine/Form/Element.php';
			
			$html = array();
			$selector = 'loginForm';
			/*
			$hiddens = $this->_controller->getRequest()->getParams();
			#$hiddens[Sitengine_Env::PARAM_ORG] = $this->_controller->getPermiso()->getOrganization()->getNameNoDefault();
			
			unset($hiddens[Sitengine_Env::PARAM_MODULE]);
			unset($hiddens[Sitengine_Env::PARAM_CONTROLLER]);
			#unset($hiddens[Sitengine_Env::PARAM_ACTION]);
			unset($hiddens[Sitengine_Env::PARAM_LOGOUT]);
			unset($hiddens[Sitengine_Env::PARAM_LOGINUSER]);
			unset($hiddens[Sitengine_Env::PARAM_LOGINPASS]);
			unset($hiddens[Sitengine_Env::PARAM_REMEMBERME]);
			*/
			
			foreach($hiddens as $k => $v) {
				$hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
			}
			
			$v = $this->_controller->getRequest()->getPost(Sitengine_Env::PARAM_LOGINUSER);
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
			
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Frontend_Front::ROUTE_USER);
			$submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble();
			
			return array(
				'hiddens' => implode('', $hiddens),
				'submitUri' => $submitUri,
				'ELEMENTS' => $html
			);
		}
		catch (Exception $exception) {
			throw $this->_controller->getExceptionInstance('login form section error', $exception);
		}
    }
    
    
    
    
    
    
    public function makeFormSection()
    {
        try {
        	$elements = array();
            $input = $this->_controller->getRequest()->getPost(null);
            $stored = array();
            
            foreach($this->_controller->getPermiso()->getAuth()->getData() as $key => $val)
            {
            	$stored[$key] = $val;
            }
            #unset($stored['password']);
            #Sitengine_Debug::print_r($stored);
            
            if($this->_controller->getPermiso()->getAuth()->hasIdentity()) {
            	$inputMode = Sitengine_Env::INPUTMODE_UPDATE;
            }
            else {
            	$inputMode = Sitengine_Env::INPUTMODE_INSERT;
            }
            #Sitengine_Debug::print_r($stored);
            ########################################################################
            #### FILTER INPUT
            ########################################################################
            $fieldsNormal = array(
                'name' => '',
                'firstname' => '',
                'lastname' => '',
                'nickname' => '',
                'password' => '',
                'email' => '',
                'timezone' => 'UTC'
            );
            
            $fieldsOnOff = array();
            
            if($inputMode == Sitengine_Env::INPUTMODE_UPDATE)
            {
                $data = Sitengine_Controller_Request_Http::filterUpdateDeprecated(
                    sizeof($input),
                    $input,
                    $fieldsNormal,
                    $fieldsOnOff,
                    $stored
                );
                $data = array_merge($stored, $data);
                
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Frontend_Front::ROUTE_USER);
				$submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble();
            }
            else
            {
                $data = Sitengine_Controller_Request_Http::filterInsertDeprecated(
                    sizeof($input),
                    $input,
                    $fieldsNormal,
                    $fieldsOnOff
                );
                
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Frontend_Front::ROUTE_USER_NEW);
				$submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble();
            }
            #Sitengine_Debug::print_r($data);
            
            
            ########################################################################
            #### ELEMENTS
            ########################################################################
			$class = 'accountInput';
			
			$n = 'name';
			$e = new Sitengine_Form_Element($n, $data[$n]);
			$e->setClass($class);
			$e->setId($class.$n);
			$elements[$n] = $e->getText(20);
			
			$n = 'password';
			$e = new Sitengine_Form_Element($n, $this->_controller->getRequest()->getPost('password'));
			$e->setClass($class);
			$e->setId($class.$n);
			$elements[$n] = $e->getText(20, 'password');
			
			$n = 'passwordConfirm';
			$e = new Sitengine_Form_Element($n, $this->_controller->getRequest()->getPost('passwordConfirm'));
			$e->setClass($class);
			$e->setId($class.$n);
			$elements[$n] = $e->getText(20, 'password');
			
			$n = 'firstname';
			$e = new Sitengine_Form_Element($n, $data[$n]);
			$e->setClass($class);
			$e->setId($class.$n);
			$elements[$n] = $e->getText(20);
			
			$n = 'nickname';
			$e = new Sitengine_Form_Element($n, $data[$n]);
			$e->setClass($class);
			$e->setId($class.$n);
			$elements[$n] = $e->getText(20);
			
			$n = 'lastname';
			$e = new Sitengine_Form_Element($n, $data[$n]);
			$e->setClass($class);
			$e->setId($class.$n);
			$elements[$n] = $e->getText(20);
			
			$n = 'email';
			$e = new Sitengine_Form_Element($n, $data[$n]);
			$e->setClass($class);
			$e->setId($class.$n);
			$elements[$n] = $e->getText(20);
			
			$n = 'timezone';
			$e = new Sitengine_Form_Element($n, $data[$n]);
			$e->setClass($class);
			$e->setId($class.$n);
			$elements[$n] = $e->getSelect($this->_controller->getEnv()->getTimezones());
            
            
            ########################################################################
            #### COLLECT ALL DATA
            ########################################################################
            $hiddens = array(
				#Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault()
			);
			
            foreach($hiddens as $k => $v) {
                $hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
            }
            
            return array(
                'inputMode' => $inputMode,
                'hiddens' => implode('', $hiddens),
                'submitUri' => $submitUri,
                'ELEMENTS' => $elements,
                'DATA' => $data
            );
        }
        catch (Exception $exception) {
			throw $this->_controller->getExceptionInstance('account form section error', $exception);
		}
    }
    
}

?>