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


abstract class Sitengine_Permiso_Backend_Groups_Members_IndexView extends Sitengine_View {
    
    
    protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
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
    
    
    
    
    
    
    public function build()
    {
        try {
            $this->_controller->getViewHelper()->build();
			$this->_queries = $this->_controller->getViewHelper()->getQueries();
            $this->_settings = $this->_controller->getViewHelper()->getSettings();
            $this->_sections = $this->_controller->getViewHelper()->getSections();
            $this->setSection('MEMBERS', $this->_getMainSection());
            return $this;
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/Permiso/Backend/Groups/Members/Exception.php';
        	throw new Sitengine_Permiso_Backend_Groups_Members_Exception('build page error', $exception);
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
        	require_once 'Sitengine/Form/Element.php';
            $valuePage = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAGE);
        	$table = $this->_controller->getFrontController()->getPermisoPackage()->getMembershipsTable();
            
            $sorting = $table->getUserJoinSortingInstance(
            	$this->_controller->getRequest()->get(Sitengine_Env::PARAM_SORT),
    			$this->_controller->getRequest()->get(Sitengine_Env::PARAM_ORDER)
            );
            
            
            ########################################################################
            #### SETTINGS
            ########################################################################
            $settingsIsActive = false;
            
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
                'IPPS' => $ippValues,
                Sitengine_Env::PARAM_IPP => $valueIpp,
            );
            
            
            ########################################################################
            #### FILTER
            ########################################################################
            
            $filter = $table->getUserJoinFilterInstance(
            	$this->_controller->getRequest(),
            	array(
					'find' => Sitengine_Permiso_Backend_Groups_Members_Controller::PARAM_FILTER_BY_FIND,
					'reset' => Sitengine_Permiso_Backend_Groups_Members_Controller::PARAM_FILTER_RESET
				),
				$this->_controller->getNamespace()
            );
            
            $hiddens = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder()
            );
            foreach($hiddens as $k => $v) {
                $hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
            }
            
            $args = array(
                Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
            );
            
            $query = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder(),
                Sitengine_Permiso_Backend_Groups_Members_Controller::PARAM_FILTER_RESET => 1
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS_MEMBERS);
            $uriReset = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            $uriReset .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
            
            $filterData = array(
                'isActive' => $filter->isActive(),
                'uriReset' => $uriReset,
                'hiddens' => implode('', $hiddens),
                'DATA' => $filter->getData()
            );
            
            
            ########################################################################
            #### SORTING
            ########################################################################
            $queries = array();
            
            foreach($sorting->getOrdering() as $field => $order)
            {
                $args = array(
                    Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
                );
                $query = array(
                    Sitengine_Env::PARAM_SORT => $field,
                    Sitengine_Env::PARAM_ORDER => $order
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS_MEMBERS);
                $queries[$field] = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
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
            $membershipsTableName = $this->_controller->getFrontController()->getPermisoPackage()->getMembershipsTableName();
			$usersTableName = $this->_controller->getFrontController()->getPermisoPackage()->getUsersTableName();
			
			
			$whereClauses = array(
				$this->_controller->getDatabase()->quoteInto("$membershipsTableName.groupId = ?", $this->_controller->getEntity()->getAncestorId()),
				"$membershipsTableName.userId = $usersTableName.id",
        		$filter->getSql('')
        	);
			
        	$select = $table
        		->select()
        		->setIntegrityCheck(false)
        		->from($membershipsTableName)
        		->from($usersTableName, array('name', 'nickname', 'firstname', 'lastname'))
        		->order($sorting->getClause())
        		->limit($valueIpp, $pager->getOffset())
        	;
        	foreach($whereClauses as $clause)
        	{
        		if($clause) { $select->where($clause); }
        	}
        	
        	#print $select->__toString();
        	$items = $table->fetchAll($select);
        	#Sitengine_Debug::print_r($items->toArray());
        	
        	
        	# if current page is out of bounds - go to beginning of list
        	if(!$items->count() && $pager->getCurrPage() > 1)
			{
				$pager = new Sitengine_Grid_Pager(1, $valueIpp);
				$select = $table
					->select()
					->setIntegrityCheck(false)
					->from($membershipsTableName)
        			->from($usersTableName, array('name', 'nickname', 'firstname', 'lastname'))
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
			$select = $table
				->select()
				->setIntegrityCheck(false)
				->from($membershipsTableName, array('id'))
        		->from($usersTableName, array('name'))
			;
			
			foreach($whereClauses as $clause) { if($clause) { $select->where($clause); } }
			$count = $table->fetchAll($select);
			$pager->calculate(sizeof($count));
			
            
            ########################################################################
            #### LISTDATA
            ########################################################################
            $markedRows = $this->_controller->getMarkedRows();
            $list = array();
            
            foreach($items as $item)
            {
            	$row = $table->complementRow($item);
            	
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
                
                $n = 'locked';
                $name = 'UPDATEROWITEM'.$n.'ITEMID'.$row['id'];
                $current = 'UPDATEROWITEMCURRENT'.$n.'ITEMID'.$row['id'];
				$row['lockedCheckbox'] = array(
					'name' => $name,
					'checked' => (sizeof($markedRows) && isset($markedRows[$row['id']])) ? ($this->_controller->getRequest()->getPost($name)) : $row[$n],
					'current' => Sitengine_Form_Element::getHidden($current, $row[$n])
				);
				
                # uris
                $args = array(
                    Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
                    Sitengine_Env::PARAM_ID => $row['id']
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                    #Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder()
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS_MEMBERS_SHARP);
                $row['uriUpdate'] = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $row['isMarked'] = (isset($markedRows[$row['id']])) ? $markedRows[$row['id']] : 0;
                
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
            
            $args = array(
                Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
            );
            $query = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder(),
                Sitengine_Env::PARAM_PAGE => $pager->getPrevPage()
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS_MEMBERS);
            $uriPrevPage  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            $uriPrevPage .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
            
            $args = array(
                Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
            );
            $query = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder(),
                Sitengine_Env::PARAM_PAGE => $pager->getNextPage()
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS_MEMBERS);
            $uriNextPage = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
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
            $args = array(
            	Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS_MEMBERS_BATCH);
            $uriDoBatchDelete = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $args = array(
            	Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS_MEMBERS_BATCH);
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
			require_once 'Sitengine/Permiso/Backend/Groups/Members/Exception.php';
			throw new Sitengine_Permiso_Backend_Groups_Members_Exception('list page error', $exception);
		}
    }
    
}


?>