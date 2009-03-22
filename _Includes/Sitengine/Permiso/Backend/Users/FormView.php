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


abstract class Sitengine_Permiso_Backend_Users_FormView extends Sitengine_View
{
    
	protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    protected $_inputMode = null;
    
    
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
        	require_once 'Sitengine/Permiso/Backend/Users/Exception.php';
        	throw new Sitengine_Permiso_Backend_Users_Exception('build page error', $exception);
        }
    }
    
    
    
    
    
    public function getData()
    {
		return array(
			'QUERIES' => $this->_queries,
			'SECTIONS' => $this->_sections,
			'SETTINGS' => $this->_settings,
			#'DICTIONARY' => $this->_controller->getTranslate()->translateGroup('data')->toArray()
		);
    }
    
    
    public function setSection($name, array $data)
    {
        $this->_sections[$name] = $data;
    }
    
    
    
    protected function _getMainSection()
    {
        try {
        	$elements = array();
            $input = $this->_controller->getRequest()->getPost(null);
            #$valueSort = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_SORT);
            #$valueOrder = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ORDER);
            #$valuePage = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAGE);
            
            $table = $this->_controller->getFrontController()->getPermisoPackage()->getUsersTable();
            
            
            ########################################################################
            #### FILTER INPUT
            ########################################################################
            $fields = array(
                'id' => '',
                'mdate' => '',
                'locked' => '',
                'enabled' => 1,
                'language' => '',
                'timezone' => 'UTC',
                'name' => '',
                'nickname' => '',
                'firstname' => '',
                'lastname' => '',
                'country' => '',
                'description' => '',
                'password' => '',
                'newsletter' => 1
            );
            
            if($this->_inputMode == Sitengine_Env::INPUTMODE_UPDATE)
            {
				$stored = $this->_controller->getFrontController()->getPermisoPackage()->getUsersTable()->complementRow($this->_controller->getEntity()->getRow());
                
                $data = Sitengine_Controller_Request_Http::filterUpdate(
                    sizeof($input),
                    $input,
                    $fields,
                    $stored
                );
                $data = array_merge($stored, $data);
                
                $args = array(
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $valueSort,
                    #Sitengine_Env::PARAM_ORDER => $valueOrder,
                    Sitengine_Env::PARAM_ID => $stored['id']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS_SHARP);
                $uri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                $queryUpdate = $uri;
                
                
                $childActions = array();
                
                $args = array(
                    Sitengine_Env::PARAM_ANCESTORID => $stored['id']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS_MEMBERSHIPS);
                $uri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                $childActions['membershipsIndex'] = array(
                    'uri' => $uri,
                    'label' => $this->_controller->getTranslate()->translate('formViewChildActionsSectionMembershipsIndex'),
                    'postfix' => ' ('.$this->_controller->getViewHelper()->countMemberships($stored['id']).')'
                );
                
                
                $hiddens = array(
                	Sitengine_Env::PARAM_METHOD => Sitengine_Env::METHOD_PUT,
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $valueSort,
                    #Sitengine_Env::PARAM_ORDER => $valueOrder,
                    Sitengine_Env::PARAM_MDATE => $stored['mdate']
                );
                
                $args = array(
                	Sitengine_Env::PARAM_ID => $stored['id'],
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS_SHARP);
                $submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $title = $stored['name'].' ('.$stored['firstname'].' '.$stored['lastname'].')';
            }
            else
            {
                $data = Sitengine_Controller_Request_Http::filterInsert(
                    sizeof($input),
                    $input,
                    $fields
                );
                
                $hiddens = array(
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $valueSort,
                    #Sitengine_Env::PARAM_ORDER => $valueOrder,
                );
                
                $args = array();
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_USERS_NEW);
                $submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $title = $this->_controller->getTranslate()->translate('formViewInsertTitle');
                $displayPermissionSettings = true;
            }
            #Sitengine_Debug::print_r($data);
            
            $data['countryOptions'] = array_merge(
            	$this->_controller->getTranslate()->translateGroup('fieldValsCountry')->toArray(),
            	$this->_controller->getTranslate()->translateGroup('countries')->toArray()
            );
            
            
            ########################################################################
            #### COLLECT ALL DATA
            ########################################################################
            foreach($hiddens as $k => $v) {
                $hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
            }
            
            return array(
            	'queryUpdate' => ((isset($queryUpdate)) ? $queryUpdate : ''),
                'title' => $title,
                'inputMode' => $this->_inputMode,
                'hiddens' => implode('', $hiddens),
                'submitUri' => $submitUri,
                'ELEMENTS' => $elements,
                'CHILDACTIONS' => (isset($childActions)) ? $childActions : array(),
                'DATA' => $data
            );
        }
        catch (Exception $exception) {
			require_once 'Sitengine/Permiso/Backend/Users/Exception.php';
			throw new Sitengine_Permiso_Backend_Users_Exception('form page error', $exception);
		}
    }
}


?>