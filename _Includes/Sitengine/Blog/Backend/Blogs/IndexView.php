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


abstract class Sitengine_Blog_Backend_Blogs_IndexView extends Sitengine_View
{
    
	protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Blog_Backend_Blogs_Controller)
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
            $this->setSection('BLOGS', $this->_getMainSection());
            return $this;
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
        	throw new Sitengine_Blog_Backend_Blogs_Exception('build page error', $exception);
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
			require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
			throw new Sitengine_Blog_Backend_Blogs_Exception('build page error', $exception);
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
            $table = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTable();
			$table->setTranscript($this->_controller->getPreferences()->getTranscript());
			
            $filter = $table->getFilterInstance(
            	$this->_controller->getRequest(),
            	array(
					'find' => Sitengine_Blog_Backend_Blogs_Controller::PARAM_FILTER_BY_FIND,
					'reset' => Sitengine_Blog_Backend_Blogs_Controller::PARAM_FILTER_RESET
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
            
            
            ### transcript element ###
            $transcripts = $table->getTranscripts();
            $transcripts->setLanguage($this->_controller->getPreferences()->getTranscript());
            if(!$transcripts->isDefault()) { $settingsIsActive = true; }
            # set html input element
            $languages = array();
            foreach($transcripts->get() as $symbol) {
            	$languages[$symbol] = $this->_controller->getTranslate()->translate('languages'.ucfirst($symbol));
            }
            $e = new Sitengine_Form_Element(Sitengine_Env::PARAM_TRANSCRIPT, $transcripts->getSymbol());
            $e->setId('settings'.Sitengine_Env::PARAM_TRANSCRIPT);
            $e->setClass('settingsSelect');
            $settingsElements[Sitengine_Env::PARAM_TRANSCRIPT] = $e->getSelect($languages);
            
            
            
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
            $e = new Sitengine_Form_Element(Sitengine_Blog_Backend_Blogs_Controller::PARAM_FILTER_BY_FIND, $filter->getVal(Sitengine_Blog_Backend_Blogs_Controller::PARAM_FILTER_BY_FIND));
            $e->setId('filter'.Sitengine_Blog_Backend_Blogs_Controller::PARAM_FILTER_BY_FIND);
            $e->setClass('filterText');
            $filter->setElement(Sitengine_Blog_Backend_Blogs_Controller::PARAM_FILTER_BY_FIND, $e->getText(20));
            
            
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
                Sitengine_Blog_Backend_Blogs_Controller::PARAM_FILTER_RESET => 1
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS);
            $uriReset  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble();
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
                $query = array(
                    Sitengine_Env::PARAM_SORT => $field,
                    Sitengine_Env::PARAM_ORDER => $order
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS);
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
            $pager = new Sitengine_Grid_Pager($valuePage, $valueIpp);
            
			
			
			########################################################################
            #### LISTQUERY
            ########################################################################
			$name = $this->_controller->getFrontController()->getBlogPackage()->getBlogsTableName();
			
			$whereClauses = array(
        		$this->_controller->getPermiso()->getDac()->getReadAccessSql($this->_controller->getFrontController()->getBlogPackage()->getAuthorizedGroups(), $name, false),
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
            	
                # row selector checkbox
				$p = 'SELECTROWITEM'.$row['id'];
				$s = (sizeof($markedRows) && isset($markedRows[$row['id']])) ? $this->_controller->getRequest()->getPost($p) : 0;
				$e = new Sitengine_Form_Element($p, 1);
				$e->setClass('listformCheckbox');
				$row['rowSelectCheckbox'] = $e->getCheckbox($s);
                
                $n = 'publish';
                $p = 'UPDATEROWITEM'.$n.'ITEMID'.$row['id'];
                $h = 'UPDATEROWITEMCURRENT'.$n.'ITEMID'.$row['id'];
                $s = (sizeof($markedRows) && isset($markedRows[$row['id']])) ? $this->_controller->getRequest()->getPost($p) : $row[$n];
                $e = new Sitengine_Form_Element($p, 1);
                $e->setClass('listformCheckbox');
                $publish  = $e->getCheckbox($s);
                $publish .= Sitengine_Form_Element::getHidden($h, $row[$n]);
                
                # uris
                $uriUpdate = $this->_makeUpdateUri($row);
                
                $args = array(
                    Sitengine_Env::PARAM_ANCESTORID => $row['slug']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_POSTS);
                $uriPostIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $args = array(
                    Sitengine_Env::PARAM_ANCESTORID => $row['slug']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_POSTS_NEW);
                $uriPostInsert = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                
                $row['isMarked'] = (isset($markedRows[$row['id']])) ? $markedRows[$row['id']] : 0;
                $row['publishCheckbox'] = $publish;
                $row['postCount'] = $this->_controller->getViewHelper()->countPosts($row['id']);
                $row['uriPostIndex'] = $uriPostIndex;
                $row['uriPostInsert'] = $uriPostInsert;
                $row['uriUpdate'] = $uriUpdate;
                
                
                $name = 'cdate';
                $date = new Zend_Date($row[$name], Zend_Date::ISO_8601, $this->_controller->getLocale());
                $date->setTimezone($this->_controller->getPreferences()->getTimezone());
                $row[$name]  = $date->get(Zend_Date::DATE_LONG, $this->_controller->getLocale()).' ';
                $row[$name] .= $date->get(Zend_Date::TIME_LONG, $this->_controller->getLocale());
                
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
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS);
            $uriPrevPage  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble();
            $uriPrevPage .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
            
            $query = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder(),
                Sitengine_Env::PARAM_PAGE => $pager->getNextPage()
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS);
            $uriNextPage = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble();
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
            $args = array();
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_BATCH);
            $uriDoBatchDelete = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $args = array();
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_BATCH);
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
                'title' => $this->_controller->getTranslate()->translate('labelsListformTitle'),
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
			require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
			throw new Sitengine_Blog_Backend_Blogs_Exception('list page error', $exception);
		}
    }
    
    
    
    
    protected function _makeUpdateUri(array $data)
    {
		$args = array(
			Sitengine_Env::PARAM_ID => $data['slug']
			#Sitengine_Env::PARAM_PAGE => $valuePage,
			#Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
			#Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder()
		);
		$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_SHARP);
		return $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
    }
}


?>