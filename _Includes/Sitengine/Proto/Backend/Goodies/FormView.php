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


abstract class Sitengine_Proto_Backend_Goodies_FormView extends Sitengine_View
{
    
	protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    protected $_inputMode = null;
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Proto_Backend_Goodies_Controller)
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
        	require_once 'Sitengine/Proto/Backend/Goodies/Exception.php';
        	throw new Sitengine_Proto_Backend_Goodies_Exception('build page error', $exception);
        }
    }
    
    
    
    
    
    public function getData()
    {
		return array(
			'QUERIES' => $this->_queries,
			'SECTIONS' => $this->_sections,
			'SETTINGS' => $this->_settings,
			'DICTIONARY' => $this->_controller->getDictionary()->getData()
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
            
            $table = $this->_controller->getFrontController()->getProtoPackage()->getGoodiesTable();
            $table->setTranslation($this->_controller->getPreferences()->getTranslation());
            $translations = $table->getTranslations();
            $displayPermissionSettings = false;
            
            
            ########################################################################
            #### FILTER INPUT
            ########################################################################
            $fields = array(
                'id' => '',
                Sitengine_Permiso::FIELD_UID => '',
                Sitengine_Permiso::FIELD_GID => '',
                Sitengine_Permiso::FIELD_RAG => 1,
                Sitengine_Permiso::FIELD_RAW => 1,
                Sitengine_Permiso::FIELD_UAG => 1,
                Sitengine_Permiso::FIELD_UAW => 0,
                Sitengine_Permiso::FIELD_DAG => 1,
                Sitengine_Permiso::FIELD_DAW => 0,
                'mdate' => '',
                'type' => 'fan',
                'sorting' => '',
                'displayThis' => 1,
                'publish' => 1,
                'locked' => 0
            );
            
            foreach($translations->get() as $index => $symbol) {
            	$fields['titleLang'.$index] = '';
            	$fields['textLang'.$index] = '';
            }
            
            if($this->_inputMode == Sitengine_Env::INPUTMODE_UPDATE)
            {
            	require_once 'Sitengine/Form/TranslationPayloads.php';
            	$payloads = new Sitengine_Form_TranslationPayloads($translations);
				$payloads->start($this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME));
				$stored = $this->_controller->getFrontController()->getProtoPackage()->getGoodiesTable()->complementRow($this->_controller->getEntity()->getRow());
                
                $data = Sitengine_Controller_Request_Http::filterUpdate(
                    sizeof($input),
                    $input,
                    $fields,
                    $stored
                );
                #$data['transColor'] = $fields['transColor'];
                $data = array_merge($stored, $data);
                
                $args = array(
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $valueSort,
                    #Sitengine_Env::PARAM_ORDER => $valueOrder,
                    Sitengine_Env::PARAM_ID => $stored['id']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHARP);
                $uri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                $queryUpdate = $uri;
                
                
                $childActions = array();
                
                $args = array(
                    Sitengine_Env::PARAM_ANCESTORID => $stored['id']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHOULDIES);
                $uri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                $childActions['shouldiesIndex'] = array(
                    'uri' => $uri,
                    'label' => $this->_controller->getDictionary()->getFromFormView('childActionsSectionShouldiesIndex'),
                    'postfix' => ' ('.$this->_controller->getViewHelper()->countShouldies($stored['id']).')'
                );
                
                
                $hiddens = array(
                	Sitengine_Env::PARAM_METHOD => Sitengine_Env::METHOD_PUT,
                    Sitengine_Env::PARAM_PAYLOAD_NAME => $payloads->getName(),
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $valueSort,
                    #Sitengine_Env::PARAM_ORDER => $valueOrder,
                    Sitengine_Env::PARAM_MDATE => $stored['mdate']
                );
                
                $args = array(
                	Sitengine_Env::PARAM_ID => $stored['id'],
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHARP);
                $submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $title = $stored['titleLang'.$translations->getIndex()];
                $title = ($title!='') ? $title : $stored['titleLang'.$translations->getDefaultIndex()];
                
                if(
					$data[Sitengine_Permiso::FIELD_UID] == $this->_controller->getPermiso()->getAuth()->getId() || # owners ok
					$this->_controller->getPermiso()->getDirectory()->userIsMember($this->_controller->getPermiso()->getAuth()->getId(), Sitengine_Permiso::GID_ADMINISTRATORS)
				)
				{
					$displayPermissionSettings = true;
				}
            }
            else
            {
            	require_once 'Sitengine/Form/TranslationPayloads.php';
            	$payloads = new Sitengine_Form_TranslationPayloads($translations);
            	$payloads->start();
            	
                $data = Sitengine_Controller_Request_Http::filterInsert(
                    sizeof($input),
                    $input,
                    $fields
                );
                
                $gid = $this->_controller->getPermiso()->getDirectory()->getGroupId($this->_controller->getFrontController()->getProtoPackage()->getOwnerGroup());
                $data[Sitengine_Permiso::FIELD_GID] = (!is_null($gid)) ? $gid : Sitengine_Permiso::GID_ADMINISTRATORS;
                $data[Sitengine_Permiso::FIELD_UID] = $this->_controller->getPermiso()->getAuth()->getId();
                
                # set some defaults...
                $n = 'sorting';
                if(array_key_exists($n, $input)) { $data[$n] = $input[$n]; }
                else {
                    $q  = 'SELECT MAX(sorting) AS maxSorting FROM '.$this->_controller->getFrontController()->getProtoPackage()->getGoodiesTableName();
                    #$q .= ' WHERE '.Sitengine_Permiso::FIELD_OID.' = "'.$this->_controller->getPermiso()->getOrganization()->getId().'"';
                    $statement = $this->_controller->getDatabase()->prepare($q);
					$statement->execute();
					$result = $statement->fetchAll();
					$data[$n] = $result[0]['maxSorting']+10;
                }
				
				$n = 'titleLang'.$translations->getDefaultIndex();
				if(array_key_exists($n, $input)) { $data[$n] = $input[$n]; }
				else { $data[$n] = 'Goody Record (Language '.$translations->getDefaultIndex().')'; }
                
                $hiddens = array(
                    Sitengine_Env::PARAM_PAYLOAD_NAME => $payloads->getName(),
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $valueSort,
                    #Sitengine_Env::PARAM_ORDER => $valueOrder,
                );
                
                $args = array();
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_NEW);
                $submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $title = $this->_controller->getDictionary()->getFromFormView('insertTitle');
                $displayPermissionSettings = true;
            }
            #Sitengine_Debug::print_r($data);
            
            $data['uidOptions'] = $this->_controller->getPermiso()->getDirectory()->getAllUsers();
			$data['gidOptions'] = array_merge(
				$this->_controller->getDictionary()->getFromFieldvals(Sitengine_Permiso::FIELD_GID),
				$this->_controller->getPermiso()->getDirectory()->getAllGroups()
			);
			
            
            
            ########################################################################
            #### CONTENT PAYLOAD SECTION TITLE
            ########################################################################
            $contentSectionTitle = $this->_controller->getDictionary()->getFromFormView('contentSectionTitleDefault');
            
            if(sizeof($translations->get()) > 1)
            {
            	if(!$payloads->isMain()) { $symbol = $payloads->getTranslationSymbol(); }
            	else { $symbol = $translations->getDefaultSymbol(); }
            	$contentSectionTitle .= ' ('.$this->_controller->getDictionary()->getFromLanguages($symbol).')';
            }
            
            
            
            ########################################################################
            #### PAYLOAD NAV DATA
            ########################################################################
            $payloadNav = array();
            
            if($this->_inputMode == Sitengine_Env::INPUTMODE_UPDATE)
			{
				$args = array(
					Sitengine_Env::PARAM_ID => $stored['id']
				);
				
				$query = array(
					Sitengine_Env::PARAM_PAYLOAD_NAME => $payloads->getMainName()
				);
				
				$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHARP);
                $uri  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                $uri .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
				
				$payloadNav[$payloads->getMainName()] = array(
					'uri' => $uri,
					'label' => $this->_controller->getDictionary()->getFromFormView('payloadNavTitleMain')
				);
				
				foreach($translations->get() as $index => $symbol)
				{
					$currentPayload = $payloads->getTranslationNamePrefix().'_'.$symbol;
					
					$args = array(
						Sitengine_Env::PARAM_ID => $stored['id']
					);
					
					$query = array(
						Sitengine_Env::PARAM_PAYLOAD_NAME => $currentPayload
					);
					
					$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHARP);
					$uri  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
					$uri .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
					
					if(sizeof($translations->get()) > 1) {
						$label = $this->_controller->getDictionary()->getFromLanguages($symbol);
					}
					else {
						$label = $this->_controller->getDictionary()->getFromFormView('contentSectionTitleDefault');
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
            	'displayPermissionSettings' => $displayPermissionSettings,
            	'queryUpdate' => ((isset($queryUpdate)) ? $queryUpdate : ''),
                'title' => $title,
                'contentSectionTitle' => $contentSectionTitle,
                'inputMode' => $this->_inputMode,
                'hiddens' => implode('', $hiddens),
                'submitUri' => $submitUri,
                'ELEMENTS' => $elements,
                'CHILDACTIONS' => (isset($childActions)) ? $childActions : array(),
                'PAYLOADNAV' => $payloadNav,
                'Payloads' => $payloads,
                'DATA' => $data
            );
        }
        catch (Exception $exception) {
			require_once 'Sitengine/Proto/Backend/Goodies/Exception.php';
			throw new Sitengine_Proto_Backend_Goodies_Exception('form page error', $exception);
		}
    }
}


?>