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



require_once 'Zend/Date.php';
require_once 'Sitengine/Mime/Type.php';
require_once 'Sitengine/String.php';
require_once 'Sitengine/Form/Element.php';



require_once 'Sitengine/View.php';


abstract class Sitengine_Permiso_Backend_Users_FormView extends Sitengine_View {
    
    
    protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    protected $_inputMode = null;
    protected $_availableLanguages = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Permiso_Backend_Users_Controller)
    	{
    		$this->_controller = $config['controller'];
    	}
    	else {
    		require_once 'Sitengine/Exception.php';
        	throw new Sitengine_Exception('construct error');
    	}
    	$this->_availableLanguages = array(Sitengine_Env::LANGUAGE_EN, Sitengine_Env::LANGUAGE_DE);
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
        	throw $this->_controller->getExceptionInstance('build page error', $exception);
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
				#'DICTIONARY' => $this->_controller->getTranslate()->translateGroup('data')
			);
       	}
        catch (Exception $exception) {
			throw $this->_controller->getExceptionInstance('build page error', $exception);
		}
    }
    
    
    public function setSection($name, array $data)
    {
        $this->_sections[$name] = $data;
    }
    
    
    
    
    
    
    protected function _getMainSection()
    {
        try {
        	$elements = array();
            $stored = $this->_controller->getEntity()->getData();
            $id = ($stored) ? $stored['id'] : '';
            $locked = ($stored) ? $stored['locked'] : 0;
            
            $input = $this->_controller->getRequest()->getPost(null);
            #$valueSort = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_SORT);
            #$valueOrder = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ORDER);
            #$valuePage = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAGE);
            
            
            ########################################################################
            #### FILTER INPUT
            ########################################################################
            $fieldsNormal = array(
                'id' => '',
                'changeRequestId' => '',
                'language' => '',
                'dst' => '-1',
                'timezone' => 'UTC',
                'name' => '',
                'nickname' => '',
                'firstname' => '',
                'lastname' => '',
                'password' => '',
                'email' => '',
                'description' => ''
            );
            
            $fieldsOnOff = array(
                'enabled' => 1,
                'locked' => 0,
                'notifyNewUser' => 0
            );
            
            if($this->_controller->getRequest()->getActionName()==Sitengine_Permiso_Backend_Users_Controller::ACTION_UPDATE || $this->_controller->getRequest()->getActionName()==Sitengine_Permiso_Backend_Users_Controller::ACTION_ME)
            {
            	require_once 'Sitengine/Form/Payloads.php';
				$payloads = new Sitengine_Form_Payloads();
				$payloads->start($this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME));
				
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
                
                
                $args = array(
                    #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                    Sitengine_Env::PARAM_ACTION => Sitengine_Permiso_Backend_Users_Controller::ACTION_DOUPDATE,
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $valueSort,
                    #Sitengine_Env::PARAM_ORDER => $valueOrder,
                    Sitengine_Env::PARAM_ID => $stored['id']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS);
                $uri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                $queryUpdate = $uri;
                
                
                $childActions = array();
                
                if($this->_controller->getPermiso()->getDirectory()->userIsMember($this->_controller->getPermiso()->getAuth()->getId(), Sitengine_Permiso::GID_ADMINISTRATORS))
                {
					$args = array(
						#Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
						Sitengine_Env::PARAM_ACTION => Sitengine_Permiso_Backend_Users_Controller::ACTION_INDEX,
						Sitengine_Env::PARAM_ANCESTORID => $stored['id']
					);
					$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS_MEMBERSHIPS);
					$uri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
					$childActions['membershipList'] = array(
							'uri' => $uri,
							'label' => $this->_controller->getTranslate()->translate('labelsChildActionsSectionMembershipsIndex'),
							'postfix' => ' ('.$this->_controller->getViewHelper()->countMemberships($stored['id']).')'
						);
					
					$args = array(
						#Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
						Sitengine_Env::PARAM_ACTION => Sitengine_Permiso_Backend_Users_Controller::ACTION_INSERT,
						Sitengine_Env::PARAM_ANCESTORID => $stored['id']
					);
					$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS_MEMBERSHIPS);
					$uri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
					$childActions['membershipInsert'] = array(
						'uri' => $uri,
						'label' => $this->_controller->getTranslate()->translate('labelsChildActionsSectionMembershipsInsert')
					);
                }
                $this->setSection('CHILDACTIONS', $childActions);
                
                
                switch($this->_controller->getRequest()->getActionName()) {
                    case Sitengine_Permiso_Backend_Users_Controller::ACTION_ME: $action = Sitengine_Permiso_Backend_Users_Controller::ACTION_DOME; break;
                    default: $action = Sitengine_Permiso_Backend_Users_Controller::ACTION_DOUPDATE; break;
                }
                
                $hiddens = array(
                    #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                    #Sitengine_Env::PARAM_ACTION => $action,
                    Sitengine_Env::PARAM_PAYLOAD_NAME => $payloads->getName(),
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $valueSort,
                    #Sitengine_Env::PARAM_ORDER => $valueOrder,
                    Sitengine_Env::PARAM_MDATE => $stored['mdate']
                );
                
                $args = array(
                	Sitengine_Env::PARAM_ID => $stored['id'],
                	Sitengine_Env::PARAM_ACTION => $action
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS);
                $submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $title = $stored['name'];
                if($stored['firstname'] || $stored['lastname']) { $title .= ' ('; }
                if($stored['firstname']) { $title .= $stored['firstname']; }
                if($stored['firstname'] && $stored['lastname']) { $title .= ' '; }
                if($stored['lastname']) { $title .= $stored['lastname']; }
                if($stored['firstname'] || $stored['lastname']) { $title .= ')'; }
            }
            else
            {
            	require_once 'Sitengine/Form/Payloads.php';
            	$payloads = new Sitengine_Form_Payloads();
            	$payloads->start();
            	
                $data = Sitengine_Controller_Request_Http::filterInsertDeprecated(
                    sizeof($input),
                    $input,
                    $fieldsNormal,
                    $fieldsOnOff
                );
                
                $hiddens = array(
                    #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                    Sitengine_Env::PARAM_PAYLOAD_NAME => $payloads->getName(),
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $valueSort,
                    #Sitengine_Env::PARAM_ORDER => $valueOrder
                );
                
                $args = array(
                	Sitengine_Env::PARAM_ACTION => Sitengine_Permiso_Backend_Users_Controller::ACTION_DOINSERT
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS);
                $submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $title = $this->_controller->getTranslate()->translate('labelsViewformInsertTitle');
            }
            #Sitengine_Debug::print_r($data);
            
            
            ########################################################################
            #### ELEMENTS
            ########################################################################
            if($payloads->isMain())
            {
				if(
					$id!=Sitengine_Permiso::UID_ROOT &&
					$id!=Sitengine_Permiso::UID_GUEST &&
					$id!=Sitengine_Permiso::UID_LOSTFOUND
				) {
					$n = 'enabled';
					$e = new Sitengine_Form_Element($n, '1');
					$e->setClass('viewFormCheckbox');
					$e->setId('viewForm'.$n);
					$elements[$n] = $e->getCheckbox($data[$n]);
					
					$n = 'locked';
					$e = new Sitengine_Form_Element($n, '1');
					$e->setClass('viewFormCheckbox');
					$e->setId('viewForm'.$n);
					$elements[$n] = $e->getCheckbox($data[$n]);
					
					$n = 'notifyNewUser';
					$e = new Sitengine_Form_Element($n, '1');
					$e->setClass('viewFormCheckbox');
					$e->setId('viewForm'.$n);
					$elements[$n] = $e->getCheckbox($data[$n]);
				}
				
				$n = 'name';
				$e = new Sitengine_Form_Element($n, $data[$n]);
				$e->setClass('viewFormInput');
				$e->setId('viewForm'.$n);
				# make name field readonly for system users or if account is locked
				if(
					$id==Sitengine_Permiso::UID_ROOT ||
					$id==Sitengine_Permiso::UID_GUEST ||
					$id==Sitengine_Permiso::UID_LOSTFOUND ||
					$locked=='1'
				) {
					$e->readonly();
				}
				$elements[$n] = $e->getText(20);
				
				
				
				# don't show these fields for guest and lostfound
				if(
					$id!=Sitengine_Permiso::UID_GUEST &&
					$id!=Sitengine_Permiso::UID_LOSTFOUND
				) {
					# users can choose language from available languages of organization
					$n = 'language';
					$nlanguages = $this->_controller->getTranslate()->translateGroup('languages');
					foreach($this->_availableLanguages as $symbol) {
						if(array_key_exists($symbol, $nlanguages)) {
							$languages[$symbol] = $nlanguages[$symbol];
						}
					}
					$languages = array_merge($this->_controller->getTranslate()->translateGroup('fieldValsLanguage'), $languages);
					$e = new Sitengine_Form_Element($n, $data[$n]);
					$e->setClass('viewFormSelect');
					$e->setId('viewForm'.$n);
					$elements[$n] = $e->getSelect($languages);
					
					$n = 'timezone';
					$e = new Sitengine_Form_Element($n, $data[$n]);
					$e->setClass('viewFormSelect');
					$e->setId('viewForm'.$n);
					$elements[$n] = $e->getSelect($this->_controller->getEnv()->getTimezones());
				
					$n = 'password';
					$e = new Sitengine_Form_Element($n, $this->_controller->getRequest()->getPost('password'));
					$e->setClass('viewFormInput');
					$e->setId('viewForm'.$n);
					$elements[$n] = $e->getText(20, 'password');
					
					$n = 'passwordConfirm';
					$e = new Sitengine_Form_Element($n, $this->_controller->getRequest()->getPost('passwordConfirm'));
					$e->setClass('viewFormInput');
					$e->setId('viewForm'.$n);
					$elements[$n] = $e->getText(20, 'password');
					
					$n = 'nickname';
					$e = new Sitengine_Form_Element($n, $data[$n]);
					$e->setClass('viewFormInput');
					$e->setId('viewForm'.$n);
					$elements[$n] = $e->getText(20);
					
					$n = 'firstname';
					$e = new Sitengine_Form_Element($n, $data[$n]);
					$e->setClass('viewFormInput');
					$e->setId('viewForm'.$n);
					$elements[$n] = $e->getText(40);
					
					$n = 'lastname';
					$e = new Sitengine_Form_Element($n, $data[$n]);
					$e->setClass('viewFormInput');
					$e->setId('viewForm'.$n);
					$elements[$n] = $e->getText(40);
					
					$n = 'email';
					$e = new Sitengine_Form_Element($n, $data[$n]);
					$e->setClass('viewFormInput');
					$e->setId('viewForm'.$n);
					$elements[$n] = $e->getText(40);
				}
				
				$n = 'description';
				$e = new Sitengine_Form_Element($n, $data[$n]);
				$e->setClass('viewFormTextarea');
				$e->setId('viewForm'.$n);
				$elements[$n] = $e->getTextarea(40, 10);
            }
            
            
            ########################################################################
            #### PAYLOAD NAV DATA
            ########################################################################
            $payloadNav = array();
            
            
            ########################################################################
            #### COLLECT ALL DATA
            ########################################################################
            foreach($hiddens as $k => $v) {
                $hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
            }
            
            return array(
            	'payloadName' => $payloads->getName(),
            	'payloadIsMain' => $payloads->isMain(),
            	'queryUpdate' => ((isset($queryUpdate)) ? $queryUpdate : ''),
                'title' => $title,
                'inputMode' => $this->_inputMode,
                'hiddens' => implode('', $hiddens),
                'submitUri' => $submitUri,
                'ELEMENTS' => $elements,
                'PAYLOADNAV' => $payloadNav,
                'DATA' => $data
            );
        }
        catch (Exception $exception) {
			throw $this->_controller->getExceptionInstance('form page error', $exception);
		}
    }
    
}


?>