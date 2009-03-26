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
 * @package    Sitengine_Newsletter
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/View.php';


abstract class Sitengine_Newsletter_Backend_Campaigns_ViewHelper extends Sitengine_View
{
    
	protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Newsletter_Backend_Campaigns_Controller)
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
			$this->_queries = $this->_controller->getFrontController()->getQueries(
				$this->_controller->getPermiso()
			);
			
			$this->setSection(
				'GLOBALNAV',
				$this->_controller->getFrontController()->getGlobalNavSection(
					$this->_controller->getPermiso(),
					$this->_controller->getTranslate(),
					$this->_queries,
					'newsletterBackendCampaigns'
				)
			);
    		
			
			/*
			if($this->_controller->getEnv()->getDebugControl()) {
				require_once 'Sitengine/Debug/Sections.php';
				$this->setSection(
					'DBG',
					Sitengine_Debug_Sections::getForm(
						$this->_controller->getRequest(),
						$this->_controller->getPreferences()->getDebugMode(),
						array('queries' => 'Queries', 'templateData' => 'Template Data'),
						'dbg'
					)
				);
			}
			
			require_once 'Sitengine/Env/Preferences/Sections.php';
			
			if(sizeof($this->_controller->getTranslate()->getAvailableLanguages()) > 1) {
				$this->setSection(
					'LANGUAGE',
					Sitengine_Env_Preferences_Sections::getLanguageForm(
						$this->_controller->getPreferences()->getLanguage(),
						$this->_controller->getTranslate()->getAvailableLanguages(),
						$this->_controller->getTranslate()->translateGroup('loclangs')->toArray(),
						'language'
					)
				);
			}
			
			$this->setSection(
				'TIMEZONE',
				Sitengine_Env_Preferences_Sections::getTimezoneForm(
					$this->_controller->getPreferences()->getTimezone(),
					$this->_controller->getEnv()->getTimezones(),
					'timezone'
				)
			);
			*/
			$this->setSection(
				'ABSTRACT',
				array(
					'title' => $this->_controller->getTranslate()->translate('labelsEntityTitle'),
					'uri' => '',
					'help' => ''
				)
			);
			
			$args = array();
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Backend_Front::ROUTE_CAMPAIGNS);
			$uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			
			$args = array();
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Backend_Front::ROUTE_CAMPAIGNS_NEW);
			$uriInsert = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			
			$this->setSection(
				'ACTIONS',
				array(
					array(
						'uri' => $uriIndex,
						'label' => $this->_controller->getTranslate()->translate('labelsActionsSectionIndex')
					),
					array(
						'uri' => $uriInsert,
						'label' => $this->_controller->getTranslate()->translate('labelsActionsSectionInsert')
					)
				)
			);
			return $this;
		}
		catch (Exception $exception) {
        	require_once 'Sitengine/Newsletter/Backend/Campaigns/Exception.php';
        	throw new Sitengine_Newsletter_Backend_Campaigns_Exception('build page error', $exception);
        }
    }
    
    
    
    
    public function countAttachments($id)
    {
    	try {
    		$table = $this->_controller->getFrontController()->getNewsletterPackage()->getAttachmentsTable();
			$where = $this->_controller->getDatabase()->quoteInto('campaignId = ?', $id);
			$select = $table->select()->from($table, array('COUNT(*) AS count'))->where($where);
			$count = $table->fetchRow($select);
			return $count->count;
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Newsletter/Backend/Campaigns/Exception.php';
			throw new Sitengine_Newsletter_Backend_Campaigns_Exception('child count error', $exception);
		}
    }
    
    
    
    
    public function getFrontendCampaignViewUrl(Sitengine_Newsletter_Campaigns_Row $row)
    {
    	$basePath = $this->_controller->getRequest()->getBasePath();
    	return 'http://'.$_SERVER['SERVER_NAME'].$basePath.'/newsletter/campaigns/'.$row->id;
    }
}

?>