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



require_once 'Zend/Date.php';
require_once 'Sitengine/Mime/Type.php';
require_once 'Sitengine/String.php';
require_once 'Sitengine/Form/Element.php';
require_once 'Sitengine/Grid/Pager.php';



require_once 'Sitengine/View.php';


abstract class Sitengine_Permiso_Backend_Groups_IndexView extends Sitengine_View {
    
    
    protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Permiso_Backend_Groups_Controller)
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
            $this->setSection('GROUPLIST', $this->_getMainSection());
            return $this;
        }
        catch (Exception $exception) {
        	throw $this->_controller->getExceptionInstance('build page error', $exception);
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
				#'DICTIONARY' => $this->_controller->getTranslate()->translateGroup('data')
			);
		}
        catch (Exception $exception) {
			throw $this->_controller->getExceptionInstance('build page error', $exception);
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
            $filter = $this->_controller->getViewHelper()->getFilterInstance();
            $sorting = $this->_controller->getViewHelper()->getSortingInstance();
            
            
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
                #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
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
            
            $e = new Sitengine_Form_Element(Sitengine_Permiso_Backend_Groups_Controller::PARAM_FILTER_BY_FIND, $filter->getVal(Sitengine_Permiso_Backend_Groups_Controller::PARAM_FILTER_BY_FIND));
            $e->setId('filter'.Sitengine_Permiso_Backend_Groups_Controller::PARAM_FILTER_BY_FIND);
            $e->setClass('filterText');
            $filter->setElement(Sitengine_Permiso_Backend_Groups_Controller::PARAM_FILTER_BY_FIND, $e->getText(20));
            
            
            $hiddens = array(
                #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder()
            );
            foreach($hiddens as $k => $v) {
                $hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
            }
            
            $args = array(
                #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                Sitengine_Env::PARAM_ACTION => $this->_controller->getRequest()->getActionName(),
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder(),
                Sitengine_Permiso_Backend_Groups_Controller::PARAM_FILTER_RESET => 1
            );
            
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS);
            
            $uriReset = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
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
                    #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                    Sitengine_Env::PARAM_ACTION => $this->_controller->getRequest()->getActionName(),
                    Sitengine_Env::PARAM_SORT => $field,
                    Sitengine_Env::PARAM_ORDER => $order
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS);
                $queries[$field] = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
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
            $q  = 'SELECT';
            $q .= ' id,';
            $q .= ' enabled,';
            $q .= ' locked,';
            $q .= ' cdate,';
            $q .= ' mdate,';
            $q .= ' name';
            
            $q .= ' FROM';
            $q .= ' '.$this->_controller->getPermiso()->getGroupsTableName();
            
            $q .= ' WHERE 1 = 1';
            #$q .= ' '.Sitengine_Permiso::FIELD_OID.' = "'.$this->_controller->getPermiso()->getOrganization()->getId().'"';
            $q .= $filter->getSql();
            $q .= $sorting->getClause(true);
            
            $sql = $this->_controller->getDatabase()->limit($q, $valueIpp, $pager->getOffset());
			$statement = $this->_controller->getDatabase()->prepare($sql);
			$statement->execute();
			$list = $statement->fetchAll();
			
			if($pager->getCurrPage() > 1 && !sizeof($list))
			{
				# current page is out of bounds - go to beginning of list
				$pager = new Sitengine_Grid_Pager(1, $valueIpp);
				$sql = $this->_controller->getDatabase()->limit($q, $valueIpp, 0);
				$statement = $this->_controller->getDatabase()->prepare($sql);
				$statement->execute();
				$list = $statement->fetchAll();
			}
			#print $sql;
			#Sitengine_Debug::print_r($list);
			
			# count full result
			$q = preg_replace('/SELECT .* FROM/', 'SELECT COUNT(*) AS count FROM', $q);
			$q = preg_replace('/ORDER BY .*/', '', $q);
			$statement = $this->_controller->getDatabase()->prepare($q);
			$statement->execute();
			$result = $statement->fetchAll();
			$pager->calculate($result[0]['count']);
            
            
            ########################################################################
            #### LISTDATA
            ########################################################################
            $markedRows = $this->_controller->getMarkedRows();
            
            foreach($list as $count => $row)
            {
                # row selector checkbox
                if($row['locked']) { $row['rowSelectCheckbox'] = '&nbsp;'; }
                else {
                    $p = 'SELECTROWITEM'.$row['id'];
                    $s = (sizeof($markedRows) && isset($markedRows[$row['id']])) ? $this->_controller->getRequest()->getPost($p) : 0;
                    $e = new Sitengine_Form_Element($p, 1);
                    $e->setClass('listformCheckbox');
                    $row['rowSelectCheckbox'] = $e->getCheckbox($s);
                }
                
                $e = $this->_controller->getTranslate()->translateGroup('fieldValsEnabled');
                $l = $this->_controller->getTranslate()->translateGroup('fieldValsLocked');
                    
                if($row['id']==Sitengine_Permiso::GID_ADMINISTRATORS) {
                    $enabled = $e[1];
                    $locked = $l[1];
                }
                else if($row['id']==Sitengine_Permiso::GID_LOSTFOUND) {
                    $enabled = $e[1];
                    $locked = $l[1];
                }
                else {
                    $n = 'locked';
                    $v = 'UPDATEROWITEM'.$n.'ITEMID'.$row['id'];
                    $h = 'UPDATEROWITEMCURRENT'.$n.'ITEMID'.$row['id'];
                    $e = new Sitengine_Form_Element($v, 1);
                    $e->setClass('listformCheckbox');
                    $locked  = $e->getCheckbox($row[$n]);
                    $locked .= Sitengine_Form_Element::getHidden($h, $row[$n]);
                    
                    
                    $n = 'enabled';
                    $v = 'UPDATEROWITEM'.$n.'ITEMID'.$row['id'];
                    $h = 'UPDATEROWITEMCURRENT'.$n.'ITEMID'.$row['id'];
                    $e = new Sitengine_Form_Element($v, 1);
                    $e->setClass('listformCheckbox');
                    $enabled  = $e->getCheckbox($row[$n]);
                    $enabled .= Sitengine_Form_Element::getHidden($h, $row[$n]);
                }
                
                # uris
                $args = array(
                    #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                    Sitengine_Env::PARAM_ACTION => Sitengine_Permiso_Backend_Groups_Controller::ACTION_UPDATE,
                    Sitengine_Env::PARAM_ID => $row['id']
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                    #Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder()
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS);
                $uriUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $args = array(
                    #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                    Sitengine_Env::PARAM_ACTION => Sitengine_Permiso_Backend_Groups_Controller::ACTION_INDEX,
                    Sitengine_Env::PARAM_ANCESTORID => $row['id']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS_MEMBERS);
                $uriChildIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $args = array(
                    #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                    Sitengine_Env::PARAM_ACTION => Sitengine_Permiso_Backend_Groups_Controller::ACTION_INSERT,
                    Sitengine_Env::PARAM_ANCESTORID => $row['id']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS_MEMBERS);
                $uriChildInsert = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $row['isMarked'] = (isset($markedRows[$row['id']])) ? $markedRows[$row['id']] : 0;
                $row['enabledCheckbox'] = $enabled;
                $row['lockedCheckbox'] = $locked;
                $row['childCount'] = $this->_controller->getViewHelper()->countMembers($row['id']);
                $row['uriChildIndex'] = $uriChildIndex;
                $row['uriChildInsert'] = $uriChildInsert;
                $row['uriUpdate'] = $uriUpdate;
                
                $name = 'cdate';
                $date = new Zend_Date($row[$name], Zend_Date::ISO_8601, $this->_controller->getLocale());
                $date->setTimezone($this->_controller->getPreferences()->getTimezone());
                $row[$name]  = $date->get(Zend_Date::DATE_LONG, $this->_controller->getLocale()).' ';
                $row[$name] .= $date->get(Zend_Date::TIME_LONG, $this->_controller->getLocale());
                
                $list[$count] = $row;
            }
            
            
            ########################################################################
            #### PAGER DATA
            ########################################################################
            $hiddens = array(
                #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder()
            );
            foreach($hiddens as $k => $v) {
                $hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
            }
            
            $args = array(
                #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                Sitengine_Env::PARAM_ACTION => $this->_controller->getRequest()->getActionName(),
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder(),
                Sitengine_Env::PARAM_PAGE => $pager->getPrevPage()
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS);
            $uriPrevPage = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $args = array(
                #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
                Sitengine_Env::PARAM_ACTION => $this->_controller->getRequest()->getActionName(),
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder(),
                Sitengine_Env::PARAM_PAGE => $pager->getNextPage()
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS);
            $uriNextPage = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
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
            	Sitengine_Env::PARAM_ACTION => Sitengine_Permiso_Backend_Groups_Controller::ACTION_DOBATCHDELETE
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS);
            $uriDoBatchDelete = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $args = array(
            	Sitengine_Env::PARAM_ACTION => Sitengine_Permiso_Backend_Groups_Controller::ACTION_DOBATCHUPDATE
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Backend_Front::ROUTE_GROUPS);
            $uriDoBatchUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $uris = array(
            	'submitDoBatchDelete' => $uriDoBatchDelete,
            	'submitDoBatchUpdate' => $uriDoBatchUpdate
            );
            
            
            ########################################################################
            #### COLLECT ALL DATA
            ########################################################################
            $hiddens = array(
                #Sitengine_Env::PARAM_ORG => $this->_controller->getPermiso()->getOrganization()->getNameNoDefault(),
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
                'FILTER' => $filterData,
                'SETTINGS' => $settingsData,
                'SORTING' => $sortingData,
                'DATA' => $list,
                'PAGER' => $pagerData
            );
        }
        catch (Exception $exception) {
			throw $this->_controller->getExceptionInstance('list page error', $exception);
		}
    }
    
}


?>