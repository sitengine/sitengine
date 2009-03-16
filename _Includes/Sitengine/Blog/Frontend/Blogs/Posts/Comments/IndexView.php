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
require_once 'Sitengine/Grid/Pager.php';



require_once 'Sitengine/View.php';


abstract class Sitengine_Blog_Frontend_Blogs_Posts_Comments_IndexView extends Sitengine_View {
    
    
    protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Blog_Frontend_Blogs_Posts_Comments_Controller)
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
            $this->setSection('COMMENTS', $this->_getMainSection());
            return $this;
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
        	throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('build page error', $exception);
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
			require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
			throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('build page error', $exception);
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
            $table = $this->_controller->getFrontController()->getBlogPackage()->getCommentsTable();
        	
            $filter = $table->getFilterInstance(
            	$this->_controller->getRequest(),
            	array(
					'find' => Sitengine_Blog_Frontend_Blogs_Posts_Comments_Controller::PARAM_FILTER_BY_FIND,
					'reset' => Sitengine_Blog_Frontend_Blogs_Posts_Comments_Controller::PARAM_FILTER_RESET
				),
				$this->_controller->getNamespace()
            );
            
            $sorting = $table->getSortingInstance(
            	$this->_controller->getRequest()->get(Sitengine_Env::PARAM_SORT),
    			$this->_controller->getRequest()->get(Sitengine_Env::PARAM_ORDER)
            );
            
            
            ########################################################################
            #### SETTINGS
            ########################################################################
            $settingsIsActive = false;
            $settingsElements = array();
            
            
            ### ipp element ###
            $defaultIpp = 20;
            $valueIpp = $this->_controller->getPreferences()->getItemsPerPage();
            $valueIpp = (is_numeric($valueIpp)) ? $valueIpp : $defaultIpp;
        	$valueIpp = ($valueIpp <= 100 && $valueIpp >= 1) ? $valueIpp : $defaultIpp;
            # set html input element
            $ippValues = array(
                '' => $this->_controller->getDictionary()->getFromLabels('settingsSectionItemsPerPage'),
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
            
            $e = new Sitengine_Form_Element(Sitengine_Blog_Frontend_Blogs_Posts_Comments_Controller::PARAM_FILTER_BY_FIND, $filter->getVal(Sitengine_Blog_Frontend_Blogs_Posts_Comments_Controller::PARAM_FILTER_BY_FIND));
            $e->setId('filter'.Sitengine_Blog_Frontend_Blogs_Posts_Comments_Controller::PARAM_FILTER_BY_FIND);
            $e->setClass('filterText');
            $filter->setElement(Sitengine_Blog_Frontend_Blogs_Posts_Comments_Controller::PARAM_FILTER_BY_FIND, $e->getText(20));
            
            
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
                Sitengine_Blog_Frontend_Blogs_Posts_Comments_Controller::PARAM_FILTER_RESET => 1
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_COMMENTS);
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
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_COMMENTS);
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
            
            
            /*
            ########################################################################
            #### QUERY
            ########################################################################
            $table = $this->_controller->getFrontController()->getBlogPackage()->getCommentsTableName();
            
        	$whereClauses = array(
        		"$table.approve = '1'",
        		"$table.parentId = '".$this->_controller->getEntity()->getAncestorId()."'",
        		$this->_controller->getPermiso()->getDac()->getReadAccessSql($this->_controller->getFrontController()->getBlogPackage()->getAuthorizedGroups(), $table, false),
        		$filter->getSql('')
        	);
        	
        	$orderClause = $sorting->getClause(true);
        	$items = $table->get($whereClauses, $orderClause, $valueIpp, $pager->getOffset());
        	
        	if($pager->getCurrPage() > 1 && !sizeof($items))
			{
				# current page is out of bounds - go to beginning of list
				$pager = new Sitengine_Grid_Pager(1, $valueIpp);
				$items = $table->get($whereClauses, $orderClause, $valueIpp, 0);
			}
			
			$pager->calculate($table->count($whereClauses));
            */
            
            
            
            ########################################################################
            #### LISTQUERY
            ########################################################################
			$name = $this->_controller->getFrontController()->getBlogPackage()->getCommentsTableName();
			$usersTableName = $this->_controller->getPermiso()->getUsersTableName();
			
			$whereClauses = array(
				"$name.approve = '1'",
				"$name.uid = $usersTableName.id",
				$this->_controller->getDatabase()->quoteInto('parentId = ?', $this->_controller->getEntity()->getAncestorId()),
        		#$this->_controller->getPermiso()->getDac()->getReadAccessSql($this->_controller->getFrontController()->getBlogPackage()->getAuthorizedGroups(), $name, false),
        		$filter->getSql('')
        	);
			
        	$select = $table
        		->select()
        		->setIntegrityCheck(false)
        		->from($table)
        		->from($usersTableName, array('name', 'firstname', 'lastname', 'nickname'))
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
            
            ########################################################################
            #### LISTDATA
            ########################################################################
            #$markedRows = $this->_controller->getMarkedRows();
            $list = array();
            
            foreach($items as $item)
            {
            	$row = $item->toArray();
            	/*
                # row selector checkbox
				$p = 'SELECTROWITEM'.$row['id'];
				$s = (sizeof($markedRows) && isset($markedRows[$row['id']])) ? $this->_controller->getRequest()->getPost($p) : 0;
				$e = new Sitengine_Form_Element($p, 1);
				$e->setClass('listformCheckbox');
				$row['rowSelectCheckbox'] = $e->getCheckbox($s);
                
                $n = 'approve';
                $p = 'UPDATEROWITEM'.$n.'ITEMID'.$row['id'];
                $h = 'UPDATEROWITEMCURRENT'.$n.'ITEMID'.$row['id'];
                $s = (sizeof($markedRows) && isset($markedRows[$row['id']])) ? $this->_controller->getRequest()->getPost($p) : $row[$n];
                $e = new Sitengine_Form_Element($p, 1);
                $e->setClass('listformCheckbox');
                $approve  = $e->getCheckbox($s);
                $approve .= Sitengine_Form_Element::getHidden($h, $row[$n]);
                
                # uris
                $args = array(
                    Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
					Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId(),
					Sitengine_Env::PARAM_ID => $row['id']
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                    #Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder()
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_COMMENTS_SHARP);
                $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $row['isMarked'] = (isset($markedRows[$row['id']])) ? $markedRows[$row['id']] : 0;
                $row['approveCheckbox'] = $approve;
                $row['uriUpdate'] = $uriUpdate;
                */
                #$row['cdate'] = $this->_controller->getViewHelper()->formatDate($row['cdate']);
                #$row['mdate'] = $this->_controller->getViewHelper()->formatDate($row['mdate']);
                
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
				Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
            $query = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder(),
                Sitengine_Env::PARAM_PAGE => $pager->getPrevPage()
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_COMMENTS);
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
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_COMMENTS);
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
            
            
            /*
            ########################################################################
            #### URIS
            ########################################################################
            $args = array(
            	Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_COMMENTS_BATCH);
            $uriDoBatchDelete = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $args = array(
            	Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_COMMENTS_BATCH);
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
            */
            return array(
                #'hiddens' => implode('', $hiddens),
                #'title' => $this->_controller->getDictionary()->getFromLabels('listformTitle'),
                #'URIS' => $uris,
                #'METHODS' => $methods,
                'FILTER' => $filterData,
                'SETTINGS' => $settingsData,
                'SORTING' => $sortingData,
                'DATA' => $list,
                'PAGER' => $pagerData
            );
        }
        catch (Exception $exception) {
			require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Comments/Exception.php';
			throw new Sitengine_Blog_Frontend_Blogs_Posts_Comments_Exception('list page error', $exception);
		}
    }
    
}


?>