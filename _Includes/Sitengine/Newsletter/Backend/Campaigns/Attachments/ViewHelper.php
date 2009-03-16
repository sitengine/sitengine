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


abstract class Sitengine_Newsletter_Backend_Campaigns_Attachments_ViewHelper extends Sitengine_View {
    
       
	protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Newsletter_Backend_Campaigns_Attachments_Controller)
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
					$this->_controller->getDictionary(),
					$this->_queries,
					'newsletterBackendCampaigns'
				)
			);
			
			
			
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
			
			if(sizeof($this->_controller->getDictionary()->getAvailableLanguages()) > 1) {
				$this->setSection(
					'LANGUAGE',
					Sitengine_Env_Preferences_Sections::getLanguageForm(
						$this->_controller->getPreferences()->getLanguage(),
						$this->_controller->getDictionary()->getAvailableLanguages(),
						$this->_controller->getDictionary()->getLocLangs(),
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
			
			$breadcrumbs = $this->_makeBreadcrumbsData();
			
			$this->setSection(
				'BREADCRUMBS',
				array(
					'title' => $this->_controller->getDictionary()->getFromBreadcrumbs('title'),
					'DATA' => $breadcrumbs
				)
			);
			
			$this->setSection(
				'ABSTRACT',
				array(
					'title' => $breadcrumbs['campaign']['title'],
					'uri' => $breadcrumbs['campaign']['uriUpdate'],
					'help' => $this->_controller->getDictionary()->getFromHelps($this->_controller->getRequest()->getActionName())
				)
			);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Backend_Front::ROUTE_CAMPAIGNS_ATTACHMENTS);
			$uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Backend_Front::ROUTE_CAMPAIGNS_ATTACHMENTS_NEW);
			$uriInsert = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Backend_Front::ROUTE_CAMPAIGNS_ATTACHMENTS_ASSIGN);
			$uriAssign = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Backend_Front::ROUTE_CAMPAIGNS_ATTACHMENTS_UPLOAD);
			$uriUpload = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			$this->setSection(
				'ACTIONS',
				array(
					array(
						'uri' => $uriIndex,
						'label' => $this->_controller->getDictionary()->getFromLabels('actionsSectionIndex')
					),
					array(
						'uri' => $uriInsert,
						'label' => $this->_controller->getDictionary()->getFromLabels('actionsSectionInsert')
					)/*,
					array(
						'uri' => $uriAssign,
						'label' => $this->_controller->getDictionary()->getFromLabels('actionsSectionAssign')
					),
					array(
						'uri' => $uriUpload,
						'label' => $this->_controller->getDictionary()->getFromLabels('actionsSectionUpload')
					)*/
				)
			);
			return $this;
		}
		catch (Exception $exception) {
        	require_once 'Sitengine/Newsletter/Backend/Campaigns/Attachments/Exception.php';
        	throw new Sitengine_Newsletter_Backend_Campaigns_Attachments_Exception('build page error', $exception);
        }
    }
    
    
    
    
    protected function _makeBreadcrumbsData()
    {
    	$data = array();
		$breadcrumbs = $this->_controller->getEntity()->getBreadcrumbs();
        $table = $this->_controller->getFrontController()->getNewsletterPackage()->getCampaignsTable();
        $campaign = $table->complementRow($breadcrumbs['campaign']);
        
        
        $args = array(
            Sitengine_Env::PARAM_ID => $campaign['id']
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Backend_Front::ROUTE_CAMPAIGNS_SHARP);
        $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        
        $args = array(
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Backend_Front::ROUTE_CAMPAIGNS);
        $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        $extras = array(
            'name' => $this->_controller->getDictionary()->getFromBreadcrumbs('campaignEntity'),
            'uriIndex' => $uriIndex,
            'uriUpdate' => $uriUpdate
        );
        $data['campaign'] = array_merge($campaign, $extras);
        
        
        
        $args = array(
            Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
            Sitengine_Env::PARAM_ANCESTORID => $campaign['id']
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Backend_Front::ROUTE_CAMPAIGNS_ATTACHMENTS);
        $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        $level = array();
        $level['name'] = $this->_controller->getDictionary()->getFromBreadcrumbs('attachmentEntity');
        $level['uriIndex'] = $uriIndex;
        
        if($breadcrumbs['attachment'] !== null)
        {
        	$table = $this->_controller->getFrontController()->getNewsletterPackage()->getAttachmentsTable();
        	$attachment = $table->complementRow($breadcrumbs['attachment']);
        	
            $args = array(
                Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
                Sitengine_Env::PARAM_ID => $attachment['id']
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Backend_Front::ROUTE_CAMPAIGNS_ATTACHMENTS_SHARP);
            $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            $level['uriUpdate'] = $uriUpdate;
            $level = array_merge($attachment, $level);
        }
        $data['attachment'] = $level;
        return $data;
    }
}

?>