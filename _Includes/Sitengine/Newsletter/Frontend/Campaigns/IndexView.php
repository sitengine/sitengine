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
require_once 'Sitengine/Grid/Pager.php';



require_once 'Sitengine/View.php';


abstract class Sitengine_Newsletter_Frontend_Campaigns_IndexView extends Sitengine_View
{
    
	protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Newsletter_Frontend_Campaigns_Controller)
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
            $this->setSection('CAMPAIGNS', $this->_getMainSection());
            
            require_once 'Sitengine/Sitemap/Page.php';
        	$page = new Sitengine_Sitemap_Page($this->_controller->getDatabase());
			$page->fetch($this->_controller->getSitemapPathCampaignsPage());
			$this->headTitle($page->getTitle());
			$this->headMeta()->appendName('keywords', $page->getMetaKeywords());
			$this->headMeta()->appendName('description', $page->getMetaDescription());
            $this->setSection('SNIPPETS', $page->getSnippets());
            return $this;
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/Newsletter/Frontend/Campaigns/Exception.php';
        	throw new Sitengine_Newsletter_Frontend_Campaigns_Exception('build page error', $exception);
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
            $valuePage = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAGE);
        	$table = $this->_controller->getFrontController()->getNewsletterPackage()->getCampaignsTable();
			
			$filter = $table->getFilterInstance(
            	$this->_controller->getRequest(),
            	array(
					'type' => Sitengine_Newsletter_Frontend_Campaigns_Controller::PARAM_FILTER_BY_TYPE,
					'find' => Sitengine_Newsletter_Frontend_Campaigns_Controller::PARAM_FILTER_BY_FIND,
					'reset' => Sitengine_Newsletter_Frontend_Campaigns_Controller::PARAM_FILTER_RESET
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
                '' => $this->_controller->getTranslate()->translate('indexViewSettingsSectionItemsPerPage'),
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
            
            $e = new Sitengine_Form_Element(Sitengine_Newsletter_Frontend_Campaigns_Controller::PARAM_FILTER_BY_FIND, $filter->getVal(Sitengine_Newsletter_Frontend_Campaigns_Controller::PARAM_FILTER_BY_FIND));
            $e->setId('filter'.Sitengine_Newsletter_Frontend_Campaigns_Controller::PARAM_FILTER_BY_FIND);
            $e->setClass('filterText');
            $filter->setElement(Sitengine_Newsletter_Frontend_Campaigns_Controller::PARAM_FILTER_BY_FIND, $e->getText(20));
            
            
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
                Sitengine_Newsletter_Frontend_Campaigns_Controller::PARAM_FILTER_RESET => 1
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Frontend_Front::ROUTE_CAMPAIGNS);
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
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Frontend_Front::ROUTE_CAMPAIGNS);
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
			$name = $this->_controller->getFrontController()->getNewsletterPackage()->getCampaignsTableName();
			
			$whereClauses = array(
        		$this->_controller->getPermiso()->getDac()->getReadAccessSql($this->_controller->getFrontController()->getNewsletterPackage()->getAuthorizedGroups(), $name, false),
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
            $list = array();
            
            foreach($items as $item)
            {
            	$row = $table->complementRow($item);
            	#Sitengine_Debug::print_r($row);
            	
                # uris
                $args = array(
                    Sitengine_Env::PARAM_ID => $row['id']
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                    #Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder()
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Frontend_Front::ROUTE_CAMPAIGNS_SHARP);
                $row['uriUpdate'] = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
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
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Frontend_Front::ROUTE_CAMPAIGNS);
            $uriPrevPage  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble();
            $uriPrevPage .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
            
            $query = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder(),
                Sitengine_Env::PARAM_PAGE => $pager->getNextPage()
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Newsletter_Frontend_Front::ROUTE_CAMPAIGNS);
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
            #### COLLECT ALL DATA
            ########################################################################
            
            return array(
                'FILTER' => $filterData,
                'SETTINGS' => $settingsData,
                'SORTING' => $sortingData,
                'DATA' => $list,
                'PAGER' => $pagerData
            );
        }
        catch (Exception $exception) {
			require_once 'Sitengine/Newsletter/Frontend/Campaigns/Exception.php';
			throw new Sitengine_Newsletter_Frontend_Campaigns_Exception('list page error', $exception);
		}
    }
}


?>