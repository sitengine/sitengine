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
        	require_once 'Sitengine/Permiso/Login/Exception.php';
        	throw new Sitengine_Permiso_Login_Exception('build page error', $exception);
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
				#'DICTIONARY' => $this->_controller->getTranslate()->translateGroup('data')->toArray()
			);
		}
        catch (Exception $exception) {
			require_once 'Sitengine/Permiso/Login/Exception.php';
			throw new Sitengine_Permiso_Login_Exception('build page error', $exception);
		}
    }
    
    
    public function getLoginSection()
    {
    	require_once 'Sitengine/Form/Element.php';
    	
		$hiddens = array(
			Sitengine_Env::PARAM_TARGET => $this->_controller->getRequest()->getParam(Sitengine_Env::PARAM_TARGET)
		);
		
		foreach($hiddens as $k => $v) {
			$hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
		}
		
		return array(
			'handler' => $this->_controller->getRequest()->getParam(Sitengine_Env::PARAM_HANDLER),
			'hiddens' => implode('', $hiddens)
		);
    }
    
}


?>