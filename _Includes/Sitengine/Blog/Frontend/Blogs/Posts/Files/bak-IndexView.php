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
require_once 'Sitengine/Mime/Type.php';
require_once 'Sitengine/String.php';
require_once 'Sitengine/Form/Element.php';
require_once 'Sitengine/Grid/Pager.php';



require_once 'Sitengine/View.php';


abstract class Sitengine_Blog_Frontend_Blogs_Posts_Files_IndexView extends Sitengine_View {
    
    
    protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller)
    	{
    		$this->_controller = $config['controller'];
    	}
    	else {
    		require_once 'Sitengine/Exception.php';
        	throw new Sitengine_Exception('construct error');
    	}
    }
    
    
    
    
    
    
    public function build()
    {
        try {
            $this->_controller->getViewHelper()->build();
			$this->_queries = $this->_controller->getViewHelper()->getQueries();
            $this->_settings = $this->_controller->getViewHelper()->getSettings();
            $this->_sections = $this->_controller->getViewHelper()->getSections();
            $this->setSection('FILES', $this->_getMainSection());
            return $this;
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
        	throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('build page error', $exception);
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
			require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
			throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('build page error', $exception);
		}
    }
    
    
    
    public function setSection($name, array $data)
    {
        $this->_sections[$name] = $data;
    }
    
    
    
    
    
    protected function _getMainSection()
    {
        try {
            $valuePage = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAGE);
            
            require_once 'Sitengine/Blog/File.php';
        	$fileObj = new Sitengine_Blog_File(
        		$this->_controller->getDatabase(),
        		$this->_controller->getFrontController()->getBlogPackage()
        	);
        	
        	require_once 'Sitengine/Blog/Files.php';
        	$filesObj = new Sitengine_Blog_Files(
        		$this->_controller->getDatabase(),
        		$this->_controller->getFrontController()->getBlogPackage(),
        		$this->_controller->getPermiso()
        	);
        	$filesObj->setTranslation($this->_controller->getPreferences()->getLanguage());
        	
            $filter = $filesObj->getFilterInstance(
            	$this->_controller->getRequest(),
            	array(
					'find' => Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller::PARAM_FILTER_BY_FIND,
					'type' => Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller::PARAM_FILTER_BY_TYPE,
					'reset' => Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller::PARAM_FILTER_RESET
				),
				$this->_controller->getNamespace()
            );
            
            $sorting = $filesObj->getSortingInstance(
            	$this->_controller->getRequest()->get(Sitengine_Env::PARAM_SORT),
    			$this->_controller->getRequest()->get(Sitengine_Env::PARAM_ORDER)
            );
            
            
            ########################################################################
            #### SETTINGS
            ########################################################################
            $settingsIsActive = false;
            $settingsElements = array();
            
            
            ### translation element ###
            $translations = $fileObj->getTranslations();
            $translations->setLanguage($this->_controller->getPreferences()->getLanguage());
            if(!$translations->isDefault()) { $settingsIsActive = true; }
            # set html input element
            $languages = array();
            foreach($translations->get() as $symbol) {
            	$languages[$symbol] = $this->_controller->getTranslate()->translate('languages'.ucfirst($symbol));
            }
            $e = new Sitengine_Form_Element(Sitengine_Env::PARAM_TRANSLATION, $translations->getSymbol());
            $e->setId('settings'.Sitengine_Env::PARAM_TRANSLATION);
            $e->setClass('settingsSelect');
            $settingsElements[Sitengine_Env::PARAM_TRANSLATION] = $e->getSelect($languages);
            
            
            ### ipp element ###
            $defaultIpp = 20;
            $valueIpp = $this->_controller->getPreferences()->getItemsPerPage();
            $valueIpp = (is_numeric($valueIpp)) ? $valueIpp : $defaultIpp;
        	$valueIpp = ($valueIpp <= 100 && $valueIpp >= 1) ? $valueIpp : $defaultIpp;
            # set html input element
            $ippValues = array(
                '' => $this->_controller->getTranslate()->translate('labelsSettingsSectionItemsPerPage'),
                5 => 5,
                10 => 10,
                20 => 20,
                50 => 50,
                100 => 100
            );
            $e = new Sitengine_Form_Element(Sitengine_Env::PARAM_IPP, $valueIpp);
            $e->setId('settings'.Sitengine_Env::PARAM_IPP);
            $e->setClass('settingsSelect');
            $settingsElements[Sitengine_Env::PARAM_IPP] = $e->getSelect($ippValues);
            
            
            $hiddens = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder()
            );
            foreach($hiddens as $k => $v) {
                $hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
            }
            
            $settingsData = array(
                'isActive' => $settingsIsActive,
                'hiddens' => implode('', $hiddens),
                'ELEMENTS' => $settingsElements
            );
            
            
            ########################################################################
            #### FILTER
            ########################################################################
            /*
            $users = $this->_controller->getPermiso()->getDirectory()->getAllUsers();
            $values = array_merge($this->_controller->getTranslate()->translateGroup('fieldValsFilterByUid')->toArray(), $users);
            $e = new Sitengine_Form_Element(Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller::PARAM_FILTER_BY_UID, $filter->getVal(Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller::PARAM_FILTER_BY_UID));
            $e->setId('filter'.Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller::PARAM_FILTER_BY_UID);
            $e->setClass('filterSelect');
            $filter->setElement(Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller::PARAM_FILTER_BY_UID, $e->getSelect($values));
            
            $groups = $this->_controller->getPermiso()->getDirectory()->getAllGroups();
            $values = array_merge($this->_controller->getTranslate()->translateGroup('fieldValsFilterByGid')->toArray(), $groups);
            $e = new Sitengine_Form_Element(Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller::PARAM_FILTER_BY_GID, $filter->getVal(Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller::PARAM_FILTER_BY_GID));
            $e->setId('filter'.Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller::PARAM_FILTER_BY_GID);
            $e->setClass('filterSelect');
            $filter->setElement(Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller::PARAM_FILTER_BY_GID, $e->getSelect($values));
            */
            $e = new Sitengine_Form_Element(Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller::PARAM_FILTER_BY_FIND, $filter->getVal(Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller::PARAM_FILTER_BY_FIND));
            $e->setId('filter'.Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller::PARAM_FILTER_BY_FIND);
            $e->setClass('filterText');
            $filter->setElement(Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller::PARAM_FILTER_BY_FIND, $e->getText(20));
            
            
            $hiddens = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder()
            );
            foreach($hiddens as $k => $v) {
                $hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
            }
            
            $args = array(
                Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
                Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
            );
            $query = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder(),
                Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller::PARAM_FILTER_RESET => 1
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_FILES);
            $uriReset  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            $uriReset .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
            
            $filterData = array(
                'isActive' => $filter->isActive(),
                'uriReset' => $uriReset,
                'hiddens' => implode('', $hiddens),
                'ELEMENTS' => $filter->getElements()
            );
            
            
            
            ########################################################################
            #### SORTING
            ########################################################################
            $queries = array();
            
            foreach($sorting->getOrdering() as $field => $order)
            {
                $args = array(
					Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
					Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
				);
                $query = array(
                    Sitengine_Env::PARAM_SORT => $field,
                    Sitengine_Env::PARAM_ORDER => $order
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_FILES);
                $queries[$field]  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                $queries[$field] .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
            }
            
            $sortingData = array(
                'QUERIES' => $queries,
                'COLUMNS' => $sorting->getColumns()
            );
            
            
            ########################################################################
            #### PAGER
            ########################################################################
            $pager = new Sitengine_Grid_Pager($valuePage, $valueIpp);
            
            
            ########################################################################
            #### QUERY
            ########################################################################
            $table = $this->_controller->getFrontController()->getBlogPackage()->getFilesTableName();
            
        	$whereClauses = array(
        		"$table.parentId = '".$this->_controller->getEntity()->getAncestorId()."'",
        		$this->_controller->getPermiso()->getDac()->getReadAccessSql($this->_controller->getFrontController()->getBlogPackage()->getAuthorizedGroups(), $table, false),
        		$filter->getSql('')
        	);
        	
        	$orderClause = $sorting->getClause(true);
        	$items = $filesObj->get($whereClauses, $orderClause, $valueIpp, $pager->getOffset());
        	
        	if($pager->getCurrPage() > 1 && !sizeof($items))
			{
				# current page is out of bounds - go to beginning of list
				$pager = new Sitengine_Grid_Pager(1, $valueIpp);
				$items = $filesObj->get($whereClauses, $orderClause, $valueIpp, 0);
			}
			
			$pager->calculate($filesObj->count($whereClauses));
            
            
            ########################################################################
            #### LISTDATA
            ########################################################################
            $markedRows = $this->_controller->getMarkedRows();
            $list = array();
            
            foreach($items as $item)
            {
            	$row = $item->getData();
                # row selector checkbox
				$p = 'SELECTROWITEM'.$row['id'];
				$s = (sizeof($markedRows) && isset($markedRows[$row['id']])) ? $this->_controller->getRequest()->getPost($p) : 0;
				$e = new Sitengine_Form_Element($p, 1);
				$e->setClass('listformCheckbox');
				$row['rowSelectCheckbox'] = $e->getCheckbox($s);
                
                $n = 'sorting';
                $p = 'UPDATEROWITEM'.$n.'ITEMID'.$row['id'];
                $h = 'UPDATEROWITEMCURRENT'.$n.'ITEMID'.$row['id'];
                $v = (sizeof($markedRows) && isset($markedRows[$row['id']])) ? $this->_controller->getRequest()->getPost($p) : $row[$n];
                $e = new Sitengine_Form_Element($p, $v);
                $e->setClass('listformText');
                $sort  = $e->getText(5);
                $sort .= Sitengine_Form_Element::getHidden($h, $row[$n]);
                
                $n = 'publish';
                $p = 'UPDATEROWITEM'.$n.'ITEMID'.$row['id'];
                $h = 'UPDATEROWITEMCURRENT'.$n.'ITEMID'.$row['id'];
                $s = (sizeof($markedRows) && isset($markedRows[$row['id']])) ? $this->_controller->getRequest()->getPost($p) : $row[$n];
                $e = new Sitengine_Form_Element($p, 1);
                $e->setClass('listformCheckbox');
                $publish  = $e->getCheckbox($s);
                $publish .= Sitengine_Form_Element::getHidden($h, $row[$n]);
                
                # uris
                $args = array(
                    Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
					Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
					Sitengine_Env::PARAM_ID => $row['id']
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                    #Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder()
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_FILES_SHARP);
                $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $row['isMarked'] = (isset($markedRows[$row['id']])) ? $markedRows[$row['id']] : 0;
                $row['sortingText'] = $sort;
                $row['publishCheckbox'] = $publish;
                $row['uriUpdate'] = $uriUpdate;
                
                $name = 'cdate';
                $date = new Zend_Date($row[$name], Zend_Date::ISO_8601, $this->_controller->getLocale());
                $date->setTimezone($this->_controller->getPreferences()->getTimezone());
                $row[$name]  = $date->get(Zend_Date::DATE_LONG, $this->_controller->getLocale()).' ';
                $row[$name] .= $date->get(Zend_Date::TIME_LONG, $this->_controller->getLocale());
                
                $list[] = $row;
            }
            #Sitengine_Debug::print_r($list);
            
            ########################################################################
            #### PAGER DATA
            ########################################################################
            $hiddens = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder()
            );
            foreach($hiddens as $k => $v) {
                $hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
            }
            
            $args = array(
				Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
            $query = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder(),
                Sitengine_Env::PARAM_PAGE => $pager->getPrevPage()
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_FILES);
            $uriPrevPage  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            $uriPrevPage .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
            
            $args = array(
				Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
            $query = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder(),
                Sitengine_Env::PARAM_PAGE => $pager->getNextPage()
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_FILES);
            $uriNextPage  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            $uriNextPage .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
            
            $currPageInput = new Sitengine_Form_Element(Sitengine_Env::PARAM_PAGE, $pager->getCurrPage());
            $currPageInput->setClass('pagerInput');
            
            $pagerData = array(
                'hiddens' => implode('', $hiddens),
                'currPageInput' => $currPageInput->getText(2),
                'currPage' => $pager->getCurrPage(),
                'nextPage' => $pager->getNextPage(),
                'prevPage' => $pager->getPrevPage(),
                'numPages' => $pager->getNumPages(),
                'numItems' => $pager->getNumItems(),
                'firstItem' => $pager->getFirstItem(),
                'lastItem' => $pager->getLastItem(),
                'uriPrevPage' => $uriPrevPage,
                'uriNextPage' => $uriNextPage
            );
            
            
            
            ########################################################################
            #### URIS
            ########################################################################
            $args = array(
            	Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_FILES_BATCH);
            $uriDoBatchDelete = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $args = array(
            	Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_FILES_BATCH);
            $uriDoBatchUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $uris = array(
            	'submitDoBatchDelete' => $uriDoBatchDelete,
            	'submitDoBatchUpdate' => $uriDoBatchUpdate
            );
            
            
            ########################################################################
            #### METHODS
            ########################################################################
            $methods = array(
            	'doBatchDelete' => Sitengine_Env::METHOD_DELETE,
            	'doBatchUpdate' => Sitengine_Env::METHOD_PUT
            );
            
            
            ########################################################################
            #### COLLECT ALL DATA
            ########################################################################
            $hiddens = array(
                Sitengine_Env::PARAM_METHOD => '',
                Sitengine_Env::PARAM_PAGE => $valuePage,
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder()
            );
            foreach($hiddens as $k => $v) {
                $hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
            }
            
            
            $type = $this->_controller->getEntity()->getAncestorType();
			if($type == Sitengine_Blog_Posts_Table::TYPE_GALLERY) {
				$title = $this->_controller->getTranslate()->translate('labelsListformPhotoTitle');
			}
			else {
				$title = $this->_controller->getTranslate()->translate('labelsListformFileTitle');
			}
            
            return array(
                'hiddens' => implode('', $hiddens),
                'title' => $title,
                'URIS' => $uris,
                'METHODS' => $methods,
                'FILTER' => $filterData,
                'SETTINGS' => $settingsData,
                'SORTING' => $sortingData,
                'DATA' => $list,
                'PAGER' => $pagerData
            );
        }
        catch (Exception $exception) {
			require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
			throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('list page error', $exception);
		}
    }
    
}


?>