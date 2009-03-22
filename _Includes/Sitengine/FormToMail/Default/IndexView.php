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


require_once 'Sitengine/View.php';


abstract class Sitengine_FormToMail_Default_IndexView extends Sitengine_View
{
    
    protected $_controller = null;
    protected $_settings = array();
    protected $_sections = array();
    
    
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
    
    
    
    
    
    
    public function build()
    {
        try {
            $this->_controller->getViewHelper()->build();
            $this->_settings = $this->_controller->getViewHelper()->getSettings();
            $this->_sections = $this->_controller->getViewHelper()->getSections();
            $this->setSection('FORM', $this->_getFormSection());
            return $this;
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/FormToMail/Default/Exception.php';
        	throw new Sitengine_FormToMail_Default_Exception('build page error', $exception);
        }
    }
    
    
    
    
    
    public function getData()
    {
    	try {
			return array(
				'SECTIONS' => $this->_sections,
				'SETTINGS' => $this->_settings,
				#'ENV' => $this->_controller->getEnv()->getData(),
				#'Env' => $this->_controller->getEnv(),
				#'STATUS' => $this->_controller->getStatus()->getData(),
				#'ORGANIZATION' => $this->_controller->getPermiso()->getOrganization()->getData(),
				#'USER' => $this->_controller->getPermiso()->getAuth()->getData(),
				#'Auth' => $this->_controller->getPermiso()->getAuth(),
				##'DICTIONARY' => $this->_controller->getTranslate()->translateGroup('data')->toArray()
			);
		}
        catch (Exception $exception) {
			require_once 'Sitengine/FormToMail/Default/Exception.php';
			throw new Sitengine_FormToMail_Default_Exception('build page error', $exception);
		}
    }
    
    
    
    public function setSection($name, array $data)
    {
        $this->_sections[$name] = $data;
    }
    
    
    
    
    
    protected function _getFormSection()
	{
		$input = $this->_controller->getRequest()->getPost(null);
		
		
		########################################################################
		#### FILTER INPUT
		########################################################################
		$fields = array(
			'firstname' => '',
			'lastname' => '',
			'email' => '',
			'message' => '',
			'subscribeNewsletter' => 0
		);
		
		$data = Sitengine_Controller_Request_Http::filterInsert(
			sizeof($input),
			$input,
			$fields
		);
		#Sitengine_Debug::print_r($data);
		
		$hiddens = array();
		
		$args = array(
			Sitengine_Env::PARAM_ACTION => Sitengine_FormToMail_Default_Controller::ACTION_SENDMAIL
		);
		$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_FormToMail_Front::ROUTE_INDEX);
		$submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
		
		########################################################################
		#### COLLECT ALL DATA
		########################################################################
		foreach($hiddens as $k => $v) {
			$hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
		}
		
		return array(
			'hiddens' => implode('', $hiddens),
			'submitUri' => $submitUri,
			'DATA' => $data
		);
    }

}


?>