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
 * @package    Sitengine_Proto
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/View.php';


abstract class Sitengine_Proto_Backend_Goodies_Shouldies_ViewHelper extends Sitengine_View {
    
       
	protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Proto_Backend_Goodies_Shouldies_Controller)
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
    		$this->_settings['editorSnippet'] = $this->_controller->getEditorSnippet();
    		$this->_queries = $this->_controller->getFrontController()->getQueries();
			
			$this->setSection(
				'GLOBALNAV',
				$this->_controller->getFrontController()->getGlobalNavSection(
					$this->_controller->getTranslate(),
					$this->_queries,
					'protoBackendGoodies'
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
			$breadcrumbs = $this->_makeBreadcrumbsData();
			
			$this->setSection(
				'BREADCRUMBS',
				array(
					'title' => $this->_controller->getTranslate()->translate('breadcrumbsTitle'),
					'DATA' => $breadcrumbs
				)
			);
			
			$this->setSection(
				'ABSTRACT',
				array(
					'title' => $breadcrumbs['goody']['title'],
					'uri' => $breadcrumbs['goody']['uriUpdate'],
					'help' => ''
				)
			);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHOULDIES);
			$uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHOULDIES_NEW);
			$uriInsert = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHOULDIES_ASSIGN);
			$uriAssign = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHOULDIES_UPLOAD);
			$uriUpload = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
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
					),
					array(
						'uri' => $uriAssign,
						'label' => $this->_controller->getTranslate()->translate('labelsActionsSectionAssign')
					),
					array(
						'uri' => $uriUpload,
						'label' => $this->_controller->getTranslate()->translate('labelsActionsSectionUpload')
					)
				)
			);
			return $this;
		}
		catch (Exception $exception) {
        	require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Exception.php';
        	throw new Sitengine_Proto_Backend_Goodies_Shouldies_Exception('build page error', $exception);
        }
    }
    
    
    
    public function countCouldies($id)
    {
    	try {
    		$table = $this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable();
			$where = $this->_controller->getDatabase()->quoteInto('shouldyId = ?', $id);
			$select = $table->select()->from($table, array('COUNT(*) AS count'))->where($where);
			$count = $table->fetchRow($select);
			return $count->count;
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Exception.php';
			throw new Sitengine_Proto_Backend_Goodies_Shouldies_Exception('child count error', $exception);
		}
    }
    
    
    
    
    protected function _makeBreadcrumbsData()
    {
    	$data = array();
		$breadcrumbs = $this->_controller->getEntity()->getBreadcrumbs();
        $table = $this->_controller->getFrontController()->getProtoPackage()->getGoodiesTable();
        $table->setTranscript($this->_controller->getPreferences()->getTranscript());
        $goody = $table->complementRow($breadcrumbs['goody']);
        
        
        $args = array(
            Sitengine_Env::PARAM_ID => $goody['id']
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHARP);
        $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        
        $args = array(
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES);
        $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        $extras = array(
            'name' => $this->_controller->getTranslate()->translate('breadcrumbsGoodyEntity'),
            'uriIndex' => $uriIndex,
            'uriUpdate' => $uriUpdate
        );
        $data['goody'] = array_merge($goody, $extras);
        
        
        
        $args = array(
            Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
            Sitengine_Env::PARAM_ANCESTORID => $goody['id']
        );
        $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHOULDIES);
        $uriIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
        $level = array();
        $level['name'] = $this->_controller->getTranslate()->translate('breadcrumbsShouldyEntity');
        $level['uriIndex'] = $uriIndex;
        
        if($breadcrumbs['shouldy'] !== null)
        {
        	$table = $this->_controller->getFrontController()->getProtoPackage()->getShouldiesTable();
        	$table->setTranscript($this->_controller->getPreferences()->getTranscript());
        	$shouldy = $table->complementRow($breadcrumbs['shouldy']);
        	
            $args = array(
                Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
                Sitengine_Env::PARAM_ID => $shouldy['id']
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHOULDIES_SHARP);
            $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            $level['uriUpdate'] = $uriUpdate;
            $level = array_merge($shouldy, $level);
        }
        $data['shouldy'] = $level;
        return $data;
    }
}

?>