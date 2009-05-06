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


abstract class Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_FormView extends Sitengine_View {
    
    
    protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    protected $_inputMode = null;
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Controller)
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
        	require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Couldies/Exception.php';
        	throw new Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Exception('build page error', $exception);
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
			$table = $this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable();
			$table->setTranscript($this->_controller->getPreferences()->getTranscript());
			$transcripts = $table->getTranscripts();
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
            
            foreach($transcripts->get() as $index => $symbol) {
            	$fields['titleLang'.$index] = '';
            	$fields['textLang'.$index] = '';
            }
            
            if($this->_inputMode == Sitengine_Env::INPUTMODE_UPDATE)
            {
            	require_once 'Sitengine/Form/TranscriptsPayloads.php';
            	$payloads = new Sitengine_Form_TranscriptsPayloads($transcripts);
				$payloads->start($this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME));
                $stored = $this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable()->complementRow($this->_controller->getEntity()->getRow());
                
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
                    Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorId(),
                    Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
                    Sitengine_Env::PARAM_ID => $stored['id']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHOULDIES_COULDIES_SHARP);
                $uri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                $queryUpdate = $uri;
                
                $hiddens = array(
                    Sitengine_Env::PARAM_METHOD => Sitengine_Env::METHOD_PUT,
                    Sitengine_Env::PARAM_PAYLOAD_NAME => $payloads->getName(),
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $valueSort,
                    #Sitengine_Env::PARAM_ORDER => $valueOrder,
                    Sitengine_Env::PARAM_MDATE => $stored['mdate']
                );
                
                $args = array(
                	Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorId(),
                    Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
                	Sitengine_Env::PARAM_ID => $stored['id'],
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHOULDIES_COULDIES_SHARP);
                $submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $title = $stored['titleLang'.$transcripts->getIndex()];
                $title = ($title!='') ? $title : $stored['titleLang0'];
                
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
            	require_once 'Sitengine/Form/TranscriptsPayloads.php';
            	$payloads = new Sitengine_Form_TranscriptsPayloads($transcripts);
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
                    $q  = 'SELECT MAX(sorting) AS maxSorting FROM '.$this->_controller->getFrontController()->getProtoPackage()->getCouldiesTableName();
                    $q .= ' WHERE ';#.Sitengine_Permiso::FIELD_OID.' = "'.$this->_controller->getPermiso()->getOrganization()->getId().'"';
                    $q .= ' shouldyId = "'.$this->_controller->getEntity()->getAncestorId().'"';
                    $statement = $this->_controller->getDatabase()->prepare($q);
					$statement->execute();
					$result = $statement->fetchAll();
					$data[$n] = $result[0]['maxSorting']+10;
                }
                
                $n = 'titleLang0';
                if(array_key_exists($n, $input)) { $data[$n] = $input[$n]; }
                else { $data[$n] = 'Couldy Record (Language 0/Default)'; }
                
                $n = 'titleLang1';
                if(array_key_exists($n, $input)) { $data[$n] = $input[$n]; }
                else { $data[$n] = 'Couldy Record (Language 1)'; }
                
                
                $hiddens = array(
                    Sitengine_Env::PARAM_PAYLOAD_NAME => $payloads->getName(),
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $valueSort,
                    #Sitengine_Env::PARAM_ORDER => $valueOrder,
                );
                
                $args = array(
                	Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorId(),
                    Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHOULDIES_COULDIES_NEW);
                $submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $title = $this->_controller->getTranslate()->translate('formViewInsertTitle');
                $displayPermissionSettings = true;
            }
            #Sitengine_Debug::print_r($data);
            
            $data['uidOptions'] = $this->_controller->getPermiso()->getDirectory()->getAllUsers();
			$data['gidOptions'] = array_merge(
				$this->_controller->getTranslate()->translateGroup('fieldValsGid')->toArray(),
				$this->_controller->getPermiso()->getDirectory()->getAllGroups()
			);
            
            
            ########################################################################
            #### CONTENT PAYLOAD SECTION TITLE
            ########################################################################
            $contentSectionTitle = $this->_controller->getTranslate()->translate('formViewContentSectionTitleDefault');
            
            if(sizeof($transcripts->get()) > 1)
            {
            	if(!$payloads->isMain()) { $symbol = $payloads->getTranscriptSymbol(); }
            	else { $symbol = $transcripts->getDefaultSymbol(); }
            	$contentSectionTitle .= ' ('.$this->_controller->getTranslate()->translate('languages'.ucfirst($symbol)).')';
            }
            
            
            
            ########################################################################
            #### PAYLOAD NAV DATA
            ########################################################################
            $payloadNav = array();
            
            if($this->_inputMode == Sitengine_Env::INPUTMODE_UPDATE)
			{
				$args = array(
					Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorId(),
                    Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
					Sitengine_Env::PARAM_ID => $stored['id']
				);
				
				$query = array(
					Sitengine_Env::PARAM_PAYLOAD_NAME => $payloads->getMainName()
				);
				
				$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHOULDIES_COULDIES_SHARP);
                $uri  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                $uri .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
				
				$payloadNav[$payloads->getMainName()] = array(
					'uri' => $uri,
					'label' => $this->_controller->getTranslate()->translate('formViewPayloadNavTitleMain')
				);
				
				foreach($transcripts->get() as $index => $symbol)
				{
					$currentPayload = $payloads->getTranscriptNamePrefix().'_'.$symbol;
					
					$args = array(
						Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorId(),
                    	Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
						Sitengine_Env::PARAM_ID => $stored['id']
					);
					
					$query = array(
						Sitengine_Env::PARAM_PAYLOAD_NAME => $currentPayload
					);
					
					$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHOULDIES_COULDIES_SHARP);
					$uri  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
					$uri .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
					
					if(sizeof($transcripts->get()) > 1) {
						$label = $this->_controller->getTranslate()->translate('languages'.ucfirst($symbol));
					}
					else {
						$label = $this->_controller->getTranslate()->translate('formViewContentSectionTitleDefault');
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
                'PAYLOADNAV' => $payloadNav,
                'Payloads' => $payloads,
                'DATA' => $data
            );
        }
        catch (Exception $exception) {
			require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Couldies/Exception.php';
			throw new Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Exception('form page error', $exception);
		}
    }
}


?>