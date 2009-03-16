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



require_once 'Sitengine/View.php';


abstract class Sitengine_Permiso_Frontend_User_ViewHelper extends Sitengine_View {
    
    
    protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Permiso_Frontend_User_Controller)
    	{
    		$this->_controller = $config['controller'];
    	}
    	else {
    		require_once 'Sitengine/Exception.php';
        	throw new Sitengine_Exception('construct error');
    	}
    }
    
    
    public function setSection($name, array $data)
    {
        $this->_sections[$name] = $data;
    }
    
    
    public function getSections()
    {
        return $this->_sections;
    }
    
    
    public function getSettings()
    {
        return $this->_settings;
    }
    
    
    public function getQueries()
    {
        return $this->_queries;
    }
    
    
    public function build()
    {
    	try {
    		/*
    		$projectDir = $this->_controller->getEnv()->getMyProjectRequestDir();
    		$path = $this->_controller->getFrontController()->getServicePath();
    		
    		$args = array(
				Sitengine_Env::PARAM_LOGOUT => 1,
			);
			
			$this->_queries = array(
				'user' => "$projectDir/$path",
				'userNew' => "$projectDir/$path/new",
				'userLogout' => "$projectDir/$path".Sitengine_Controller_Request_Http::makeNameValueQuery($args)
			);
            */
			if($this->_controller->getEnv()->getDebugControl()) {
				require_once 'Sitengine/Debug/Sections.php';
				$this->setSection(
					'DBG',
					Sitengine_Debug_Sections::getForm(
						$this->_controller->getRequest(),
						$this->_controller->getPreferences()->getDebugMode(),
						array('queries' => 'Queries', 'templateData' => 'Template Data')
					)
				);
			}
			
			require_once 'Sitengine/Env/Preferences/Sections.php';
			
			if(sizeof($this->_controller->getDictionary()->getAvailableLanguages()) > 1) {
				$this->setSection(
					'LANGUAGE',
					Sitengine_Env_Preferences_Sections::getLanguageForm(
						$this->_controller->getPreferences()->getLanguage(),
						$this->_controller->getDictionary()->getAvailableLanguages(),
						$this->_controller->getDictionary()->getLocLangs()
					)
				);
			}
			
			$this->setSection(
				'TIMEZONE',
				Sitengine_Env_Preferences_Sections::getTimezoneForm(
					$this->_controller->getPreferences()->getTimezone(),
					$this->_controller->getEnv()->getTimezones()
				)
			);
			
			#$this->_settings['isNewAccount'] = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_TARGET);
			return $this;
		}
		catch (Exception $exception) {
        	throw $this->_controller->getExceptionInstance('build page error', $exception);
        }
    }
    
}

?>