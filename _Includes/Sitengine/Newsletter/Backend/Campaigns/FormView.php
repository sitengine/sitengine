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


require_once 'Zend/Date.php';
require_once 'Sitengine/Mime/Type.php';
require_once 'Sitengine/String.php';
require_once 'Sitengine/Form/Element.php';



require_once 'Sitengine/View.php';


abstract class Sitengine_Newsletter_Backend_Campaigns_FormView extends Sitengine_View
{
    
	protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    protected $_inputMode = null;
    
    
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
            $this->_settings['editorSnippet'] = $this->_controller->getEditorSnippet();
            return $this;
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/Newsletter/Backend/Campaigns/Exception.php';
        	throw new Sitengine_Newsletter_Backend_Campaigns_Exception('build page error', $exception);
        }
    }
    
    
    
    
    
    public function getData()
    {
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
            
            $table = $this->_controller->getFrontController()->getNewsletterPackage()->getCampaignsTable();
            
            
            ########################################################################
            #### FILTER INPUT
            ########################################################################
            $fieldsNormal = array(
                'id' => '',
                'mdate' => '',
                'type' => '',
                'title' => '',
                'body' => ''
            );
            
            $fieldsOnOff = array(
                'publish' => 1
            );
            
            if($this->_inputMode == Sitengine_Env::INPUTMODE_UPDATE)
            {
            	$stored = $this->_controller->getFrontController()->getNewsletterPackage()->getCampaignsTable()->complementRow($this->_controller->getEntity()->getRow());
                
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
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $valueSort,
                    #Sitengine_Env::PARAM_ORDER => $valueOrder,
                    Sitengine_Env::PARAM_ID => $stored['id']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Backend_Front::ROUTE_CAMPAIGNS_SHARP);
                $uri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                $queryUpdate = $uri;
                
                
                $childActions = array();
                
                $args = array(
                    Sitengine_Env::PARAM_ANCESTORID => $stored['id']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Backend_Front::ROUTE_CAMPAIGNS_ATTACHMENTS);
                $uri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                $childActions['attachmentsIndex'] = array(
                    'uri' => $uri,
                    'label' => $this->_controller->getTranslate()->translate('formViewChildActionsSectionAttachmentsIndex'),
                    'postfix' => ' ('.$this->_controller->getViewHelper()->countAttachments($stored['id']).')'
                );
                
                $uri = $this->_controller->getViewHelper()->getFrontendCampaignViewUrl($this->_controller->getEntity()->getRow());
                $childActions['frontendCampaignView'] = array(
                    'uri' => $uri,
                    'label' => $this->_controller->getTranslate()->translate('formViewChildActionsFrontendCampaignViewUrl')
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
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Backend_Front::ROUTE_CAMPAIGNS_SHARP);
                $submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                
                $args = array(
					Sitengine_Env::PARAM_ANCESTORID => $stored['id'],
				);
				$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Backend_Front::ROUTE_CAMPAIGNS_ATTACHMENTS_JSURLS);
				$jsUrlsUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $title = $stored['title'];
            }
            else
            {
                $data = Sitengine_Controller_Request_Http::filterInsertDeprecated(
                    sizeof($input),
                    $input,
                    $fieldsNormal,
                    $fieldsOnOff
                );
                
                $hiddens = array(
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $valueSort,
                    #Sitengine_Env::PARAM_ORDER => $valueOrder,
                );
                
                $args = array();
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Backend_Front::ROUTE_CAMPAIGNS_NEW);
                $submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $title = $this->_controller->getTranslate()->translate('formViewInsertTitle');
                $jsUrlsUri = '';
            }
            #Sitengine_Debug::print_r($data);
            
            
            
            ########################################################################
            #### ELEMENTS
            ########################################################################
			$n = 'title';
			$e = new Sitengine_Form_Element($n, $data[$n]);
			$e->setClass('viewFormInput');
			$e->setId('viewForm'.$n);
			$elements[$n] = $e->getText(40);
			
			$n = 'publish';
			$e = new Sitengine_Form_Element($n, '1');
			$e->setClass('viewFormCheckbox');
			$e->setId('viewForm'.$n);
			$elements[$n] = $e->getCheckbox($data[$n]);
			
			$n = 'type';
			$e = new Sitengine_Form_Element($n, $data[$n]);
			$e->setClass('viewFormSelect');
			$e->setId('viewForm'.$n);
			$elements[$n] = $e->getSelect($this->_controller->getTranslate()->translateGroup('fieldValsType'));
			
			$n = 'body';
			$e = new Sitengine_Form_Element($n, $data[$n]);
			$e->setClass('viewFormTextarea');
			$e->setId('editor1');
			$elements[$n] = $e->getTextarea(40, 10);
            
            
            
            
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
                'jsUrlsUri' => $jsUrlsUri,
                'ELEMENTS' => $elements,
                'CHILDACTIONS' => (isset($childActions)) ? $childActions : array(),
                'DATA' => $data
            );
        }
        catch (Exception $exception) {
			require_once 'Sitengine/Newsletter/Backend/Campaigns/Exception.php';
			throw new Sitengine_Newsletter_Backend_Campaigns_Exception('form page error', $exception);
		}
    }
}


?>