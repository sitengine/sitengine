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


abstract class Sitengine_Proto_Backend_Goodies_IndexView extends Sitengine_View
{
    
	protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
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
    
    
    
    
    
    
    public function build()
    {
        try {
            $this->_controller->getViewHelper()->build();
			$this->_queries = $this->_controller->getViewHelper()->getQueries();
            $this->_settings = $this->_controller->getViewHelper()->getSettings();
            $this->_sections = $this->_controller->getViewHelper()->getSections();
            $this->setSection('GOODIES', $this->_getMainSection());
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
        	require_once 'Sitengine/Form/Element.php';
            $valuePage = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAGE);
        	$table = $this->_controller->getFrontController()->getProtoPackage()->getGoodiesTable();
			$table->setTranslation($this->_controller->getPreferences()->getTranslation());
            
            $sorting = $table->getSortingInstance(
            	$this->_controller->getRequest()->get(Sitengine_Env::PARAM_SORT),
    			$this->_controller->getRequest()->get(Sitengine_Env::PARAM_ORDER)
            );
            
            
            
            ########################################################################
            #### SETTINGS
            ########################################################################
            $settingsIsActive = false;
            
            
            ### translation ###
            $translations = $table->getTranslations();
            $translations->setLanguage($this->_controller->getPreferences()->getTranslation());
            if(!$translations->isDefault()) { $settingsIsActive = true; }
            
            $languages = array();
            foreach($translations->get() as $symbol)
            {
            	$languages[$symbol] = $this->_controller->getTranslate()->translate('languages'.ucfirst($symbol));
            }
            
            ### ipp ###
            $defaultIpp = 20;
            $valueIpp = $this->_controller->getPreferences()->getItemsPerPage();
            $valueIpp = (is_numeric($valueIpp)) ? $valueIpp : $defaultIpp;
        	$valueIpp = ($valueIpp <= 100 && $valueIpp >= 1) ? $valueIpp : $defaultIpp;
            
            $ippValues = array(
                '' => $this->_controller->getTranslate()->translate('indexViewSettingsSectionItemsPerPage'),
                5 => 5,
                10 => 10,
                20 => 20,
                50 => 50,
                100 => 100
            );
            
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
                #'ELEMENTS' => $settingsElements,
                'TRANSLATIONS' => $languages,
                'IPPS' => $ippValues,
                Sitengine_Env::PARAM_TRANSLATION => $translations->getSymbol(),
                Sitengine_Env::PARAM_IPP => $valueIpp,
            );
            
            ########################################################################
            #### FILTER
            ########################################################################
            
            $filter = $table->getFilterInstance(
            	$this->_controller->getRequest(),
            	array(
					'uid' => Sitengine_Proto_Backend_Goodies_Controller::PARAM_FILTER_BY_UID,
					'gid' => Sitengine_Proto_Backend_Goodies_Controller::PARAM_FILTER_BY_GID,
					'type' => Sitengine_Proto_Backend_Goodies_Controller::PARAM_FILTER_BY_TYPE,
					'find' => Sitengine_Proto_Backend_Goodies_Controller::PARAM_FILTER_BY_FIND,
					'reset' => Sitengine_Proto_Backend_Goodies_Controller::PARAM_FILTER_RESET
				),
				$this->_controller->getNamespace()
            );
            
            $types = $this->_controller->getTranslate()->translateGroup('fieldValsFilterByType');
            $users = $this->_controller->getPermiso()->getDirectory()->getAllUsers();
            $users = array_merge($this->_controller->getTranslate()->translateGroup('fieldValsFilterByUid'), $users);
            $groups = $this->_controller->getPermiso()->getDirectory()->getAllGroups();
            $groups = array_merge($this->_controller->getTranslate()->translateGroup('fieldValsFilterByGid'), $groups);
            
            $hiddens = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder()
            );
            foreach($hiddens as $k => $v) {
                $hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
            }
            
            $query = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder(),
                Sitengine_Proto_Backend_Goodies_Controller::PARAM_FILTER_RESET => 1
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES);
            $uriReset  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble();
            $uriReset .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
            
            $filterData = array(
                'isActive' => $filter->isActive(),
                'uriReset' => $uriReset,
                'hiddens' => implode('', $hiddens),
                'DATA' => $filter->getData(),
                'TYPES' => $types,
                'USERS' => $users,
                'GROUPS' => $groups
            );
            
            
            
            ########################################################################
            #### SORTING
            ########################################################################
            $queries = array();
            
            foreach($sorting->getOrdering() as $field => $order)
            {
                $query = array(
                    Sitengine_Env::PARAM_SORT => $field,
                    Sitengine_Env::PARAM_ORDER => $order
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES);
                $queries[$field]  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble();
                $queries[$field] .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
            }
            
            $sortingData = array(
                'QUERIES' => $queries,
                'COLUMNS' => $sorting->getColumns()
            );
            
            
            ########################################################################
            #### PAGER
            ########################################################################
            require_once 'Sitengine/Grid/Pager.php';
            $pager = new Sitengine_Grid_Pager($valuePage, $valueIpp);
            
            
            
            ########################################################################
            #### LISTQUERY
            ########################################################################
			$name = $this->_controller->getFrontController()->getProtoPackage()->getGoodiesTableName();
			
			$whereClauses = array(
        		$this->_controller->getPermiso()->getDac()->getReadAccessSql($this->_controller->getFrontController()->getProtoPackage()->getAuthorizedGroups(), $name, false),
        		$filter->getSql('')
        	);
			
        	$select = $table
        		->select()
        		->order($sorting->getClause())
        		->limit($valueIpp, $pager->getOffset())
        	;
        	foreach($whereClauses as $clause)
        	{
        		if($clause) { $select->where($clause); }
        	}
        	$items = $table->fetchAll($select);
        	
        	
        	
        	# if current page is out of bounds - go to beginning of list
        	if(!$items->count() && $pager->getCurrPage() > 1)
			{
				$pager = new Sitengine_Grid_Pager(1, $valueIpp);
				$select = $table
					->select()
					->order($sorting->getClause())
					->limit($valueIpp, 0)
				;
				foreach($whereClauses as $clause)
				{
					if($clause) { $select->where($clause); }
				}
				$items = $table->fetchAll($select);
			}
			
			
			
			# count total number of records
			$select = $table->select()->from($table, array('COUNT(*) AS count'));
			foreach($whereClauses as $clause) { if($clause) { $select->where($clause); } }
			$count = $table->fetchRow($select);
			$pager->calculate($count->count);
			
			
        	
            
            ########################################################################
            #### LISTDATA
            ########################################################################
            $markedRows = $this->_controller->getMarkedRows();
            $list = array();
            
            foreach($items as $item)
            {
            	$row = $table->complementRow($item);
            	#Sitengine_Debug::print_r($row);
            	
                # row selector checkbox
                $row['rowSelectCheckbox'] = array();
                
                if(!$row['locked'])
                {
                	$name = 'SELECTROWITEM'.$row['id'];
					$row['rowSelectCheckbox'] = array(
						'name' => $name,
						'checked' => (sizeof($markedRows) && isset($markedRows[$row['id']])) ? ($this->_controller->getRequest()->getPost($name)) : 0
					);
                }
                
                $n = 'sorting';
                $name = 'UPDATEROWITEM'.$n.'ITEMID'.$row['id'];
                $current = 'UPDATEROWITEMCURRENT'.$n.'ITEMID'.$row['id'];
				$row['sortingText'] = array(
					'name' => $name,
					'value' => (sizeof($markedRows) && isset($markedRows[$row['id']])) ? ($this->_controller->getRequest()->getPost($name)) : $row[$n],
					'current' => Sitengine_Form_Element::getHidden($current, $row[$n])
				);
                
                $n = 'displayThis';
                $name = 'UPDATEROWITEM'.$n.'ITEMID'.$row['id'];
                $current = 'UPDATEROWITEMCURRENT'.$n.'ITEMID'.$row['id'];
				$row['displayThisSelect'] = array(
					'name' => $name,
					'value' => (sizeof($markedRows) && isset($markedRows[$row['id']])) ? ($this->_controller->getRequest()->getPost($name)) : $row[$n],
					'OPTIONS' => $this->_controller->getTranslate()->translateGroup('fieldValsDisplayThis'),
					'current' => Sitengine_Form_Element::getHidden($current, $row[$n])
				);
                
                $n = 'locked';
                $name = 'UPDATEROWITEM'.$n.'ITEMID'.$row['id'];
                $current = 'UPDATEROWITEMCURRENT'.$n.'ITEMID'.$row['id'];
				$row['lockedCheckbox'] = array(
					'name' => $name,
					'checked' => (sizeof($markedRows) && isset($markedRows[$row['id']])) ? ($this->_controller->getRequest()->getPost($name)) : $row[$n],
					'current' => Sitengine_Form_Element::getHidden($current, $row[$n])
				);
                
                $n = 'publish';
                $name = 'UPDATEROWITEM'.$n.'ITEMID'.$row['id'];
                $current = 'UPDATEROWITEMCURRENT'.$n.'ITEMID'.$row['id'];
				$row['publishCheckbox'] = array(
					'name' => $name,
					'checked' => (sizeof($markedRows) && isset($markedRows[$row['id']])) ? ($this->_controller->getRequest()->getPost($name)) : $row[$n],
					'current' => Sitengine_Form_Element::getHidden($current, $row[$n])
				);
				
                # uris
                $args = array(
                    Sitengine_Env::PARAM_ID => $row['id']
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                    #Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder()
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHARP);
                $row['uriUpdate'] = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $args = array(
                    Sitengine_Env::PARAM_ANCESTORID => $row['id']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHOULDIES);
                $row['uriShouldyIndex'] = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $row['isMarked'] = (isset($markedRows[$row['id']])) ? $markedRows[$row['id']] : 0;
                $row['shouldyCount'] = $this->_controller->getViewHelper()->countShouldies($row['id']);
                
                $list[] = $row;
            }
            
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
            
            $query = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder(),
                Sitengine_Env::PARAM_PAGE => $pager->getPrevPage()
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES);
            $uriPrevPage  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble();
            $uriPrevPage .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
            
            $query = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder(),
                Sitengine_Env::PARAM_PAGE => $pager->getNextPage()
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES);
            $uriNextPage = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble();
            $uriNextPage .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
            
            $pagerData = array(
                'hiddens' => implode('', $hiddens),
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
            $args = array();
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_BATCH);
            $uriDoBatchDelete = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $args = array();
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_BATCH);
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
            
            return array(
                'hiddens' => implode('', $hiddens),
                'title' => $this->_controller->getTranslate()->translate('indexViewTitle'),
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
			require_once 'Sitengine/Proto/Backend/Goodies/Exception.php';
			throw new Sitengine_Proto_Backend_Goodies_Exception('list page error', $exception);
		}
    }
}


?>