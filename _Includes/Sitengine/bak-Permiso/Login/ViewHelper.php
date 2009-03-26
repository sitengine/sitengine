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


abstract class Sitengine_Permiso_Login_ViewHelper extends Sitengine_View
{
    
	protected $_controller = null;
	protected $_queries = array();
    
    
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
    	return $this;
    }
    
    
    public function getDebugSection()
    {
    	/*
    	if($this->_controller->getEnv()->getDebugControl())
    	{
			require_once 'Sitengine/Debug/Sections.php';
			return Sitengine_Debug_Sections::getForm(
				$this->_controller->getRequest(),
				$this->_controller->getPreferences()->getDebugMode(),
				array('queries' => 'Queries', 'templateData' => 'Template Data')
			);
		}
		*/
		return array();
    }
    
    
    public function getLanguageSection()
    {
    	/*
        if(sizeof($this->_controller->getTranslate()->getAvailableLanguages()) > 1)
        {
        	require_once 'Sitengine/Env/Preferences/Sections.php';
			return Sitengine_Env_Preferences_Sections::getLanguageForm(
				$this->_controller->getPreferences()->getLanguage(),
				$this->_controller->getTranslate()->getAvailableLanguages(),
				$this->_controller->getTranslate()->translateGroup('loclangs')->toArray()
			);
		}
		*/
		return array();
    }
    
    
    public function getTimezoneSection()
    {
    	/*
    	require_once 'Sitengine/Env/Preferences/Sections.php';
		return Sitengine_Env_Preferences_Sections::getTimezoneForm(
			$this->_controller->getPreferences()->getTimezone(),
			$this->_controller->getEnv()->getTimezones()
		);
		*/
		return array();
    }
}

?>