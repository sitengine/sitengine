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
 * @package    Sitengine_Sitemap
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */



require_once 'Zend/Date.php';
require_once 'Sitengine/String.php';
require_once 'Sitengine/Form/Element.php';



require_once 'Sitengine/View.php';


abstract class Sitengine_Sitemap_Backend_PageFormView extends Sitengine_View {
    
    
    protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    protected $_inputMode = null;
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Sitemap_Backend_Controller)
    	{
    		$this->_controller = $config['controller'];
    	}
    	else {
    		require_once 'Sitengine/Exception.php';
        	throw new Sitengine_Exception('construct error');
    	}
    }
    
    
    
    public function setInputMode($inputMode)
    {
    	$this->_inputMode = $inputMode;
    }
    
    
    
    public function build()
    {
        try {
            $this->_controller->getViewHelper()->build();
			$this->_queries = $this->_controller->getViewHelper()->getQueries();
            $this->_settings = $this->_controller->getViewHelper()->getSettings();
            $this->_sections = $this->_controller->getViewHelper()->getSections();
            $this->setSection('VIEWFORM', $this->_getMainSection());
            return $this;
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/Sitemap/Backend/Exception.php';
        	throw new Sitengine_Sitemap_Backend_Exception('build page error', $exception);
        }
    }
    
    
    
    
    
    public function getData()
    {
    	try {
			return array(
				'QUERIES' => $this->_queries,
				'SECTIONS' => $this->_sections,
				'SETTINGS' => $this->_settings,
				#'ENV' => $this->_controller->getEnv()->getData(),
				#'Env' => $this->_controller->getEnv(),
				#'STATUS' => $this->_controller->getStatus()->getData(),
				#'ORGANIZATION' => $this->_controller->getPermiso()->getOrganization()->getData(),
				#'USER' => $this->_controller->getPermiso()->getAuth()->getData(),
				#'Auth' => $this->_controller->getPermiso()->getAuth(),
				#'DICTIONARY' => $this->_controller->getTranslate()->translateGroup('data')->toArray()
			);
       	}
        catch (Exception $exception) {
			require_once 'Sitengine/Sitemap/Backend/Exception.php';
			throw new Sitengine_Sitemap_Backend_Exception('build page error', $exception);
		}
    }
    
    
    public function setSection($name, array $data)
    {
        $this->_sections[$name] = $data;
    }
    
    
    
    
    
    protected function _getMainSection()
    {
        try {
            $input = $this->_controller->getRequest()->getPost(null);
            #$valueSort = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_SORT);
            #$valueOrder = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ORDER);
            #$valuePage = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAGE);
            
            ########################################################################
            #### FILTER INPUT
            ########################################################################
            $fieldsNormal = array(
                'id' => '',
                #Sitengine_Permiso::FIELD_UID => '',
                #Sitengine_Permiso::FIELD_GID => '',
                'keyword' => '',
                #'metaKeywords' => '',
                #'metaDescription' => '',
                'description' => ''
            );
            
            foreach($this->_controller->getTranscripts()->get() as $index => $symbol) {
            	$fieldsNormal['titleLang'.$index] = '';
            	$fieldsNormal['metaKeywordsLang'.$index] = '';
            	$fieldsNormal['metaDescriptionLang'.$index] = '';
            }
            
            $fieldsOnOff = array(
                #Sitengine_Permiso::FIELD_RAG => 1,
                #Sitengine_Permiso::FIELD_RAW => 1,
                #Sitengine_Permiso::FIELD_UAG => 1,
                #Sitengine_Permiso::FIELD_UAW => 0,
                #Sitengine_Permiso::FIELD_DAG => 0,
                #Sitengine_Permiso::FIELD_DAW => 0,
                'locked' => 0,
                #'enabled' => 1
            );
            
            if($this->_inputMode == Sitengine_Env::INPUTMODE_UPDATE)
            {
            	require_once 'Sitengine/Form/TranscriptsPayloads.php';
            	$payloads = new Sitengine_Form_TranscriptsPayloads($this->_controller->getTranscripts());
				$payloads->start($this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME));
                $stored = $this->_controller->getEntity()->getData();
                
                $data = Sitengine_Controller_Request_Http::filterUpdateDeprecated(
                    sizeof($input),
                    $input,
                    $fieldsNormal,
                    $fieldsOnOff,
                    $stored
                );
                $data = array_merge($stored, $data);
                
                # set dates to timezone
                /*
                $name = 'cdate';
                $date = new Zend_Date($stored[$name], Zend_Date::ISO_8601, $this->_controller->getLocale());
                $date->setTimezone($this->_controller->getPreferences()->getTimezone());
                $data[$name]  = $date->get(Zend_Date::DATE_FULL).' ';
                $data[$name] .= $date->get(Zend_Date::TIME_FULL);
                */
                
                
                /*
                $name = 'mdate';
                $date = new Zend_Date($stored[$name], Zend_Date::ISO_8601, $this->_controller->getLocale());
                $date->setTimezone($this->_controller->getPreferences()->getTimezone());
                $data[$name]  = $date->get(Zend_Date::DATE_LONG).' ';
                $data[$name] .= $date->get(Zend_Date::TIME_LONG);
                */
                
                
                $hiddens = array(
                    #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                    Sitengine_Env::PARAM_PAYLOAD_NAME => $payloads->getName(),
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $valueSort,
                    #Sitengine_Env::PARAM_ORDER => $valueOrder,
                    Sitengine_Env::PARAM_MDATE => $stored['mdate']
                );
                
                $args = array(
                	Sitengine_Env::PARAM_ID => $stored['id'],
                	Sitengine_Env::PARAM_ACTION => Sitengine_Sitemap_Backend_Controller::ACTION_DOUPDATEPAGE
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
                $submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $title = $this->_controller->getTranslate()->translate('labelsViewformUpdatePageTitle');
            }
            else
            {
            	require_once 'Sitengine/Form/TranscriptsPayloads.php';
            	$payloads = new Sitengine_Form_TranscriptsPayloads($this->_controller->getTranscripts());
            	$payloads->start();
            	
                $data = Sitengine_Controller_Request_Http::filterInsertDeprecated(
                    sizeof($input),
                    $input,
                    $fieldsNormal,
                    $fieldsOnOff
                );
                
                $gid = $this->_controller->getPermiso()->getDirectory()->getGroupId($this->_controller->getOwnerGroup());
                $data[Sitengine_Permiso::FIELD_GID] = (!is_null($gid)) ? $gid : Sitengine_Permiso::GID_ADMINISTRATORS;
                $data[Sitengine_Permiso::FIELD_UID] = $this->_controller->getPermiso()->getAuth()->getId();
                
                $hiddens = array(
                    #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                    Sitengine_Env::PARAM_PAYLOAD_NAME => $payloads->getName(),
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $valueSort,
                    #Sitengine_Env::PARAM_ORDER => $valueOrder,
                );
                
                $args = array(
                	Sitengine_Env::PARAM_PARENTID => $this->_controller->getEntity()->getParentId(),
                	Sitengine_Env::PARAM_ACTION => Sitengine_Sitemap_Backend_Controller::ACTION_DONEWPAGE
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
                $submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $title = $this->_controller->getTranslate()->translate('labelsViewformNewPageTitle');
            }
            #Sitengine_Debug::print_r($data);
            
            
            ########################################################################
            #### ELEMENTS
            ########################################################################
            
            if($payloads->isMain())
            {
            	/*
				if(
					(!$data['id']) || # on insert
					(isset($data[Sitengine_Permiso::FIELD_UID]) && $data[Sitengine_Permiso::FIELD_UID]==$this->_controller->getPermiso()->getAuth()->getId()) || # owners ok
					#$this->_controller->getPermiso()->getUser()->hasSupervisorRights() || # supervisors ok
					#$this->_controller->getPermiso()->getUser()->hasModeratorRights() # moderators ok
					$this->_controller->getPermiso()->getDirectory()->userIsMember($this->_controller->getPermiso()->getAuth()->getId(), Sitengine_Permiso::GID_ADMINISTRATORS)
				)
				{
					$n = Sitengine_Permiso::FIELD_UID;
					$e = new Sitengine_Form_Element($n, $data[$n]);
					$e->setClass('viewFormSelect');
					$e->setId('viewForm'.$n);
					$users = $this->_controller->getPermiso()->getDirectory()->getAllUsers();
					$elements[$n] = $e->getSelect($users);
					
					$n = Sitengine_Permiso::FIELD_GID;
					$e = new Sitengine_Form_Element($n, $data[$n]);
					$e->setClass('viewFormSelect');
					$e->setId('viewForm'.$n);
					$groups = $this->_controller->getPermiso()->getDirectory()->getAllGroups();
					$groups = array_merge($this->_controller->getTranslate()->translateGroup('fieldValsGid')->toArray(), $groups);
					$elements[$n] = $e->getSelect($groups);
					
					$n = Sitengine_Permiso::FIELD_RAG;
					$e = new Sitengine_Form_Element($n, '1');
					$e->setClass('viewFormCheckbox');
					$e->setId('viewForm'.$n);
					$elements[$n] = $e->getCheckbox($data[$n]);
					
					$n = Sitengine_Permiso::FIELD_RAW;
					$e = new Sitengine_Form_Element($n, '1');
					$e->setClass('viewFormCheckbox');
					$e->setId('viewForm'.$n);
					$elements[$n] = $e->getCheckbox($data[$n]);
					
					$n = Sitengine_Permiso::FIELD_UAG;
					$e = new Sitengine_Form_Element($n, '1');
					$e->setClass('viewFormCheckbox');
					$e->setId('viewForm'.$n);
					$elements[$n] = $e->getCheckbox($data[$n]);
					
					$n = Sitengine_Permiso::FIELD_UAW;
					$e = new Sitengine_Form_Element($n, '1');
					$e->setClass('viewFormCheckbox');
					$e->setId('viewForm'.$n);
					$elements[$n] = $e->getCheckbox($data[$n]);
					
					$n = Sitengine_Permiso::FIELD_DAG;
					$e = new Sitengine_Form_Element($n, '1');
					$e->setClass('viewFormCheckbox');
					$e->setId('viewForm'.$n);
					$elements[$n] = $e->getCheckbox($data[$n]);
					
					$n = Sitengine_Permiso::FIELD_DAW;
					$e = new Sitengine_Form_Element($n, '1');
					$e->setClass('viewFormCheckbox');
					$e->setId('viewForm'.$n);
					$elements[$n] = $e->getCheckbox($data[$n]);
				}
				*/
				$n = 'locked';
				$e = new Sitengine_Form_Element($n, '1');
				$e->setClass('viewFormCheckbox');
				$e->setId('viewForm'.$n);
				$elements[$n] = $e->getCheckbox($data[$n]);
				/*
				$n = 'enabled';
				$e = new Sitengine_Form_Element($n, '1');
				$e->setClass('viewFormCheckbox');
				$e->setId('viewForm'.$n);
				$elements[$n] = $e->getCheckbox($data[$n]);
				*/
				$n = 'keyword';
				$e = new Sitengine_Form_Element($n, $data[$n]);
				$e->setClass('viewFormInput');
				$e->setId('viewForm'.$n);
				$elements[$n] = $e->getText(40);
				/*
				$n = 'metaKeywords';
				$e = new Sitengine_Form_Element($n, $data[$n]);
				$e->setClass('viewFormTextarea');
				$e->setId('viewForm'.$n);
				$elements[$n] = $e->getTextarea(40, 3);
				
				$n = 'metaDescription';
				$e = new Sitengine_Form_Element($n, $data[$n]);
				$e->setClass('viewFormTextarea');
				$e->setId('viewForm'.$n);
				$elements[$n] = $e->getTextarea(40, 3);
				*/
				$n = 'description';
				$e = new Sitengine_Form_Element($n, $data[$n]);
				$e->setClass('viewFormInput');
				$e->setId('viewForm'.$n);
				$elements[$n] = $e->getTextarea(60, 10);
            }
            else {
            	$n = 'titleLang'.$payloads->getTranscriptIndex();
				$e = new Sitengine_Form_Element($n, $data[$n]);
				$e->setClass('viewFormInput');
				$e->setId('viewForm'.$n);
				$elements['title'] = $e->getText(40);
            	
            	$n = 'metaKeywordsLang'.$payloads->getTranscriptIndex();
				$e = new Sitengine_Form_Element($n, $data[$n]);
				$e->setClass('viewFormTextarea');
				$e->setId('viewForm'.$n);
				$elements['metaKeywords'] = $e->getTextarea(40, 3);
				
				$n = 'metaDescriptionLang'.$payloads->getTranscriptIndex();
				$e = new Sitengine_Form_Element($n, $data[$n]);
				$e->setClass('viewFormTextarea');
				$e->setId('viewForm'.$n);
				$elements['metaDescription'] = $e->getTextarea(40, 3);
            }
            
            
            ########################################################################
            #### CONTENT PAYLOAD SECTION TITLE
            ########################################################################
            $contentSectionTitle = $this->_controller->getTranslate()->translate('labelsViewformContentSectionTitleDefault');
            $contentSectionTitle = 'Meta Info';
            
            if(sizeof($this->_controller->getTranscripts()->get()) > 1)
            {
            	if(!$payloads->isMain()) { $symbol = $payloads->getTranscriptSymbol(); }
            	else { $symbol = $this->_controller->getTranscripts()->getDefaultSymbol(); }
            	$contentSectionTitle .= ' ('.$this->_controller->getTranslate()->translate('languages'.ucfirst($symbol)).')';
            }
            
            
            
            ########################################################################
            #### PAYLOAD NAV DATA
            ########################################################################
            $payloadNav = array();
            
            if($this->_inputMode == Sitengine_Env::INPUTMODE_UPDATE)
			{
				$args = array(
					#Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
					Sitengine_Env::PARAM_ACTION => Sitengine_Sitemap_Backend_Controller::ACTION_UPDATEPAGE,
					Sitengine_Env::PARAM_ID => $stored['id'],
					Sitengine_Env::PARAM_PAYLOAD_NAME => $payloads->getMainName()
				);
				
				$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
                $uri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
				
				$payloadNav[$payloads->getMainName()] = array(
					'uri' => $uri,
					'label' => $this->_controller->getTranslate()->translate('labelsViewformPayloadNavTitleMain')
				);
				
				foreach($this->_controller->getTranscripts()->get() as $index => $symbol)
				{
					$currentPayload = $payloads->getTranscriptNamePrefix().'_'.$symbol;
					
					$args = array(
						#Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
						Sitengine_Env::PARAM_ACTION => Sitengine_Sitemap_Backend_Controller::ACTION_UPDATEPAGE,
						Sitengine_Env::PARAM_ID => $stored['id'],
						Sitengine_Env::PARAM_PAYLOAD_NAME => $currentPayload
					);
					
					$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
					$uri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
					if(sizeof($this->_controller->getTranscripts()->get()) > 1) {
						$label = 'Meta Info ('.$this->_controller->getTranslate()->translate('languages'.ucfirst($symbol)).')';
					}else {
						$label = 'Meta Info';#.$this->_controller->getTranslate()->translate('labelsViewformContentSectionTitleDefault');
					}
					
					$payloadNav[$currentPayload] = array(
						'uri' => $uri,
						'label' => $label
					);
				}
			}
			
            
            ########################################################################
            #### COLLECT ALL DATA
            ########################################################################
            foreach($hiddens as $k => $v) {
                $hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
            }
            
            return array(
                'payloadName' => $payloads->getName(),
            	'payloadIsMain' => $payloads->isMain(),
            	'payloadIsDefaultTranscript' => $payloads->isDefaultTranscript(),
                'title' => $title,
                'contentSectionTitle' => $contentSectionTitle,
                'inputMode' => $this->_inputMode,
                'hiddens' => implode('', $hiddens),
                'submitUri' => $submitUri,
                'ELEMENTS' => $elements,
                'PAYLOADNAV' => $payloadNav,
                'DATA' => $data
            );
        }
        catch (Exception $exception) {
			require_once 'Sitengine/Sitemap/Backend/Exception.php';
			throw new Sitengine_Sitemap_Backend_Exception('form page error', $exception);
		}
    }
}


?>