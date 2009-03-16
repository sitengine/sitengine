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


abstract class Sitengine_Permiso_Backend_Groups_Members_FormView extends Sitengine_View {
    
    
    protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    protected $_inputMode = null;
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Permiso_Backend_Groups_Members_Controller)
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
				'DICTIONARY' => $this->_controller->getDictionary()->getData()
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
				'groupId' => '',
				'userId' => ''
			);
			
			$fieldsOnOff = array(
				'locked' => 0
			);
			
			if($this->_inputMode == Sitengine_Env::INPUTMODE_UPDATE)
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
				$name = 'cdate';
				$date = new Zend_Date($stored[$name], Zend_Date::ISO_8601, $this->_controller->getLocale());
				$date->setTimezone($this->_controller->getPreferences()->getTimezone());
				$data[$name]  = $date->get(Zend_Date::DATE_FULL).' ';
				$data[$name] .= $date->get(Zend_Date::TIME_FULL);
				
				
				$name = 'mdate';
				$date = new Zend_Date($stored[$name], Zend_Date::ISO_8601, $this->_controller->getLocale());
				$date->setTimezone($this->_controller->getPreferences()->getTimezone());
				$data[$name]  = $date->get(Zend_Date::DATE_LONG).' ';
				$data[$name] .= $date->get(Zend_Date::TIME_LONG);
				
				
				$args = array(
					#Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
					Sitengine_Env::PARAM_ACTION => Sitengine_Permiso_Backend_Groups_Members_Controller::ACTION_UPDATE,
					#Sitengine_Env::PARAM_PAGE => $valuePage,
					#Sitengine_Env::PARAM_SORT => $valueSort,
					#Sitengine_Env::PARAM_ORDER => $valueOrder,
					Sitengine_Env::PARAM_ID => $stored['id']
				);
				$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS_MEMBERS);
				$uri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
				$queryUpdate = $uri;
				
				$hiddens = array(
					#Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
					Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
					Sitengine_Env::PARAM_PAYLOAD_NAME => $payloads->getName(),
					#Sitengine_Env::PARAM_PAGE => $valuePage,
					#Sitengine_Env::PARAM_SORT => $valueSort,
					#Sitengine_Env::PARAM_ORDER => $valueOrder,
					Sitengine_Env::PARAM_MDATE => $stored['mdate']
				);
				
				$args = array(
					Sitengine_Env::PARAM_ID => $stored['id'],
                	Sitengine_Env::PARAM_ACTION => Sitengine_Permiso_Backend_Groups_Members_Controller::ACTION_DOUPDATE
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS_MEMBERS);
                $submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
				
				$title = $this->_controller->getDictionary()->getFromLabels('viewformUpdateTitle');
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
					Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
                	Sitengine_Env::PARAM_ACTION => Sitengine_Permiso_Backend_Groups_Members_Controller::ACTION_DOINSERT
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS_MEMBERS);
                $submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
				
				$title = $this->_controller->getDictionary()->getFromLabels('viewformInsertTitle');
			}
			#Sitengine_Debug::print_r($data);
			
			
			########################################################################
			#### ELEMENTS
			########################################################################
			if($payloads->isMain())
            {
				# hide locked checkbox for system user
				if(
					$id!=Sitengine_Permiso::GID_ADMINISTRATORS &&
					$id!=Sitengine_Permiso::GID_LOSTFOUND
				) {
					$n = 'locked';
					$e = new Sitengine_Form_Element($n, '1');
					$e->setClass('viewformCheckbox');
					$e->setId('viewform'.$n);
					$elements[$n] = $e->getCheckbox($data[$n]);
				}
				
				$n = 'userId';
				$users = $this->_controller->getPermiso()->getDirectory()->getAllUsers();
				unset($users[Sitengine_Permiso::UID_GUEST]); # remove guest
				unset($users[Sitengine_Permiso::UID_LOSTFOUND]); # remove lostfound
				$users = array_merge($this->_controller->getDictionary()->getFromFieldvals('select'), $users);
				
				$e = new Sitengine_Form_Element($n, $data[$n]);
				$e->setClass('viewformSelect');
				$e->setId('viewform'.$n);
				$elements[$n] = $e->getSelect($users);
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