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
 * @package    Sitengine_Blog
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */



require_once 'Zend/Date.php';
require_once 'Sitengine/String.php';
require_once 'Sitengine/Form/Element.php';



require_once 'Sitengine/View.php';


abstract class Sitengine_Blog_Backend_Blogs_Posts_Files_FormView extends Sitengine_View {
    
    
    protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    protected $_inputMode = null;
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Blog_Backend_Blogs_Posts_Files_Controller)
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
        	require_once 'Sitengine/Blog/Backend/Blogs/Posts/Files/Exception.php';
        	throw new Sitengine_Blog_Backend_Blogs_Posts_Files_Exception('build page error', $exception);
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
			require_once 'Sitengine/Blog/Backend/Blogs/Posts/Files/Exception.php';
			throw new Sitengine_Blog_Backend_Blogs_Posts_Files_Exception('build page error', $exception);
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
            $input = $this->_controller->getRequest()->getPost(null);
            #$valueSort = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_SORT);
            #$valueOrder = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ORDER);
            #$valuePage = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAGE);
            
            $table = $this->_controller->getFrontController()->getBlogPackage()->getFilesTable();
            $table->setTranscript($this->_controller->getPreferences()->getTranscript());
            $transcripts = $table->getTranscripts();
            
            
            ########################################################################
            #### FILTER INPUT
            ########################################################################
            $fieldsNormal = array(
                'id' => '',
                #Sitengine_Permiso::FIELD_UID => '',
                #Sitengine_Permiso::FIELD_GID => '',
                'mdate' => '',
                'sorting' => ''
            );
            
            foreach($transcripts->get() as $index => $symbol) {
            	$fieldsNormal['titleLang'.$index] = '';
            	$fieldsNormal['markupLang'.$index] = '';
            }
            
            $fieldsOnOff = array(
                #Sitengine_Permiso::FIELD_RAG => 1,
                #Sitengine_Permiso::FIELD_RAW => 1,
                #Sitengine_Permiso::FIELD_UAG => 0,
                #Sitengine_Permiso::FIELD_UAW => 0,
                #Sitengine_Permiso::FIELD_DAG => 0,
                #Sitengine_Permiso::FIELD_DAW => 0,
                'publish' => 1
            );
            
            if($this->_inputMode == Sitengine_Env::INPUTMODE_UPDATE)
            {
            	require_once 'Sitengine/Form/TranscriptsPayloads.php';
            	$payloads = new Sitengine_Form_TranscriptsPayloads($transcripts);
				$payloads->start($this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME));
                $stored = $this->_controller->getFrontController()->getBlogPackage()->getFilesTable()->complementRow($this->_controller->getEntity()->getRow());
                
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
                    Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
                    Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
                    Sitengine_Env::PARAM_ID => $stored['id']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_POSTS_FILES_SHARP);
                $queryUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $hiddens = array(
                    Sitengine_Env::PARAM_METHOD => Sitengine_Env::METHOD_PUT,
                    Sitengine_Env::PARAM_PAYLOAD_NAME => $payloads->getName(),
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $valueSort,
                    #Sitengine_Env::PARAM_ORDER => $valueOrder,
                    Sitengine_Env::PARAM_MDATE => $stored['mdate']
                );
                
                $args = array(
                	Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
                    Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
                    Sitengine_Env::PARAM_ID => $stored['id']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_POSTS_FILES_SHARP);
                $submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $title = $stored['titleLang'.$transcripts->getIndex()];
                $title = ($title!='') ? $title : $stored['titleLang0'];
				
                $n = 'uri';
				$e = new Sitengine_Form_Element($n, preg_replace('/&amp;/', '&', $stored['file1OriginalUri']));
				$e->setClass('viewFormInput');
				$e->setId('viewForm'.$n);
				$elements[$n] = $e->getText(60);
            }
            else
            {
            	require_once 'Sitengine/Form/TranscriptsPayloads.php';
            	$payloads = new Sitengine_Form_TranscriptsPayloads($transcripts);
            	$payloads->start();
            	
                $data = Sitengine_Controller_Request_Http::filterInsertDeprecated(
                    sizeof($input),
                    $input,
                    $fieldsNormal,
                    $fieldsOnOff
                );
                
                $gid = $this->_controller->getPermiso()->getDirectory()->getGroupId($this->_controller->getFrontController()->getBlogPackage()->getOwnerGroup());
                $data[Sitengine_Permiso::FIELD_GID] = (!is_null($gid)) ? $gid : Sitengine_Permiso::GID_ADMINISTRATORS;
                $data[Sitengine_Permiso::FIELD_UID] = $this->_controller->getPermiso()->getAuth()->getId();
                
                # set some defaults...
                $n = 'sorting';
                if(array_key_exists($n, $input)) { $data[$n] = $input[$n]; }
                else {
                    $q  = 'SELECT MAX(sorting) AS maxSorting FROM '.$this->_controller->getFrontController()->getBlogPackage()->getFilesTableName();
                    $q .= ' WHERE ';#.Sitengine_Permiso::FIELD_OID.' = "'.$this->_controller->getPermiso()->getOrganization()->getId().'"';
                    $q .= ' parentId = "'.$this->_controller->getEntity()->getAncestorId().'"';
                    $statement = $this->_controller->getDatabase()->prepare($q);
					$statement->execute();
					$result = $statement->fetchAll();
					$data[$n] = $result[0]['maxSorting']+10;
                }
                
                
                $hiddens = array(
                    Sitengine_Env::PARAM_PAYLOAD_NAME => $payloads->getName(),
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $valueSort,
                    #Sitengine_Env::PARAM_ORDER => $valueOrder,
                );
                
                $args = array(
                	Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
                    Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_POSTS_FILES_NEW);
                $submitUri = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                
                $type = $this->_controller->getEntity()->getAncestorType();
				if($type == Sitengine_Blog_Posts_Table::TYPE_GALLERY) {
					$title = $this->_controller->getTranslate()->translate('labelsViewformInsertPhotoTitle');
				}
				else {
					$title = $this->_controller->getTranslate()->translate('labelsViewformInsertFileTitle');
				}
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
				$n = 'titleLang'.$transcripts->getDefaultIndex();
				$e = new Sitengine_Form_Element($n, $data[$n]);
				$e->setClass('viewFormInput');
				$e->setId('viewForm'.$n);
				$elements['title'] = $e->getText(40);
				
				$n = 'publish';
				$e = new Sitengine_Form_Element($n, '1');
				$e->setClass('viewFormCheckbox');
				$e->setId('viewForm'.$n);
				$elements[$n] = $e->getCheckbox($data[$n]);
				
				$n = 'sorting';
				$e = new Sitengine_Form_Element($n, $data[$n]);
				$e->setClass('viewFormInput');
				$e->setId('viewForm'.$n);
				$elements[$n] = $e->getText(5);
				
				$n = 'file1Original';
				$e = new Sitengine_Form_Element($n);
				$e->setClass('viewFormFile');
				$e->setId('viewForm'.$n);
				$elements[$n] = $e->getFile(40);
            }
			$n = 'titleLang'.$payloads->getTranscriptIndex();
			$e = new Sitengine_Form_Element($n, $data[$n]);
			$e->setClass('viewFormInput');
			$e->setId('viewForm'.$n);
			$elements['title'] = $e->getText(40);
			
			#$n = 'markupLang'.$payloads->getTranscriptIndex();
			#$elements['markup'] = $this->_makeTextarea($n, $data[$n]);
			
			$n = 'markupLang'.$payloads->getTranscriptIndex();
			$e = new Sitengine_Form_Element($n, $data[$n]);
			$e->setClass('viewFormTextarea');
			$e->setId('markupTextarea');
			$elements['markup'] = $e->getTextarea(40, 10);
            
            
            
            
            ########################################################################
            #### CONTENT PAYLOAD SECTION TITLE
            ########################################################################
            $contentSectionTitle = $this->_controller->getTranslate()->translate('labelsViewformContentSectionTitleDefault');
            
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
					Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
					Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
					Sitengine_Env::PARAM_ID => $stored['id']
				);
				$query = array(
					Sitengine_Env::PARAM_PAYLOAD_NAME => $payloads->getMainName()
				);
				$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_POSTS_FILES_SHARP);
                $uri  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                $uri .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
				
				$payloadNav[$payloads->getMainName()] = array(
					'uri' => $uri,
					'label' => $this->_controller->getTranslate()->translate('labelsViewformPayloadNavTitleMain')
				);
				
				$count = 0;
				
				foreach($transcripts->get() as $index => $symbol)
				{
					# skip default transcript because all fields are available in the overview
					if($count)
					{
						$currentPayload = $payloads->getTranscriptNamePrefix().'_'.$symbol;
						
						$args = array(
							Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
							Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
							Sitengine_Env::PARAM_ID => $stored['id']
						);
						$query = array(
							Sitengine_Env::PARAM_PAYLOAD_NAME => $currentPayload
						);
						$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_POSTS_FILES_SHARP);
						$uri  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
						$uri .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
						
						if(sizeof($transcripts->get()) > 1) {
							$label = $this->_controller->getTranslate()->translate('languages'.ucfirst($symbol));
						}else {
							$label = $this->_controller->getTranslate()->translate('labelsViewformContentSectionTitleDefault');
						}
						
						$payloadNav[$currentPayload] = array(
							'uri' => $uri,
							'label' => $label
						);
					}
					$count++;
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
            	'queryUpdate' => ((isset($queryUpdate)) ? $queryUpdate : ''),
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
			require_once 'Sitengine/Blog/Backend/Blogs/Posts/Files/Exception.php';
			throw new Sitengine_Blog_Backend_Blogs_Posts_Files_Exception('form page error', $exception);
		}
    }
    
    
    
    
    /*
    protected function _makeTextarea($n, $v)
    {
    	require_once $this->_controller->getEnv()->getContribDir().'/Fck/fckeditor_php5.php';
    	
        $fck = new FCKeditor($n);
        $fck->BasePath = $this->_controller->getEnv()->getContribRequestDir().'/Fck/'; # trailing slash
        $fck->Value = $v;
        $fck->Width = 700;
        $fck->Height = 300;
        $fck->ToolbarSet = 'SitengineNoImages';
        #$fck->Config['DefaultLanguage'] = Sitengine_Env::LANGUAGE_EN;
        #$fck->Config['AutoDetectLanguage'] = false;
        $fck->Config['CustomConfigurationsPath'] = $this->_controller->getEnv()->getMediaRequestDir().'/Fck/Custom.js'; # custom script config file
        $fck->Config['StylesXmlPath'] = $this->_controller->getEnv()->getMediaRequestDir().'/Fck/Custom.xml'; # custom items for style menu
        $fck->Config['ToolbarComboPreviewCSS'] = $this->_controller->getEnv()->getMediaRequestDir().'/Fck/Custom.css'; # stylesheet for styles popup
        $fck->Config['EditorAreaCSS'] = $this->_controller->getEnv()->getMediaRequestDir().'/Fck/Custom.css'; # stylesheet for preview area
        return $fck->CreateHtml();
    }
    */
}


?>