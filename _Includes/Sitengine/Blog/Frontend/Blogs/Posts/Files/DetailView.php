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



require_once 'Sitengine/View.php';


abstract class Sitengine_Blog_Frontend_Blogs_Posts_Files_DetailView extends Sitengine_View {
    
    
    protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    protected $_inputMode = null;
    
    
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
            $this->setSection('FILE', $this->_getMainSection());
            $this->setSection('SLIDE', $this->_getSlideSection());
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
				#'DICTIONARY' => $this->_controller->getTranslate()->translateGroup('data')
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
        	$data = $this->_controller->getFrontController()->getBlogPackage()->getFilesTable()->complementRow($this->_controller->getEntity()->getRow());
        	
        	$name = 'cdate';
			$date = new Zend_Date($data[$name], Zend_Date::ISO_8601, $this->_controller->getLocale());
			$date->setTimezone($this->_controller->getPreferences()->getTimezone());
			$data[$name]  = $date->get(Zend_Date::DATE_FULL).' ';
			$data[$name] .= $date->get(Zend_Date::TIME_FULL);
			
			
			$name = 'mdate';
			$date = new Zend_Date($data[$name], Zend_Date::ISO_8601, $this->_controller->getLocale());
			$date->setTimezone($this->_controller->getPreferences()->getTimezone());
			$data[$name]  = $date->get(Zend_Date::DATE_LONG).' ';
			$data[$name] .= $date->get(Zend_Date::TIME_LONG);
			return $data;
        }
        catch (Exception $exception) {
			require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
			throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('main section error', $exception);
		}
    }
    
    
    
    protected function _getSlideSection()
    {
        try {
        	$id = $this->_controller->getRequest()->getParam(Sitengine_Env::PARAM_ID);
        	$parentId = $this->_controller->getRequest()->getParam(Sitengine_Env::PARAM_ANCESTORID);
        	$table = $this->_controller->getFrontController()->getBlogPackage()->getFilesTable();
			/*
			$whereClauses = array(
				"publish = '1'",
				$this->_controller->getDatabase()->quoteInto('parentId = ?', $this->_controller->getEntity()->getAncestorId())
        	);
			*/
        	$select = $table
        		->select()
        		#->from($table, array('id'))
        		->order('sorting ASC')
        		->where("publish = '1'")
        		->where('parentId = ?', $this->_controller->getEntity()->getAncestorId())
        	;
        	/*
        	foreach($whereClauses as $clause)
        	{
        		if($clause) { $select->where($clause); }
        	}
        	*/
        	$items = $table->fetchAll($select);
			
			
			/*
			# count total number of records
			$select = $table->select()->from($table, array('COUNT(*) AS count'));
			#foreach($whereClauses as $clause) { if($clause) { $select->where($clause); } }
			$count = $table->fetchRow($select);
			$pager->calculate($count->count);
			
        	$id = $this->_controller->getRequest()->getParam(Sitengine_Env::PARAM_ID);
        	$parentId = $this->_controller->getRequest()->getParam(Sitengine_Env::PARAM_ANCESTORID);
        	
        	require_once 'Sitengine/Blog/Files.php';
        	$filesObj = new Sitengine_Blog_Files(
        		$this->_controller->getDatabase(),
        		$this->_controller->getFrontController()->getBlogPackage()
        	);
        	$filesObj->setTranslation($this->_controller->getPreferences()->getLanguage());
            $table = $this->_controller->getFrontController()->getBlogPackage()->getFilesTableName();
            
        	$whereClauses = array(
        		"$table.parentId = '".$parentId."'",
        		"$table.publish = '1'"
        	);
        	
        	$orderClause = "ORDER BY $table.sorting ASC";
        	$items = $filesObj->get($whereClauses, $orderClause);
			*/
            $idFirst = null;
			$idLast = null;
			$idPrev = null;
			$idNext = null;
			$currItem = 1;
			$index = 1;
			
			foreach($items as $row)
			{
				if($row->id == $id) {
					$currItem = $index;
					break;
				}
				$index++;
			}
			#Sitengine_Debug::print_r($items);
			require_once 'Sitengine/Grid/Pager.php';
			$pager = new Sitengine_Grid_Pager($currItem, 1);
			$pager->calculate($items->count());
			
			$items->seek(0);
			$itemFirst = $table->complementRow($items->current());
			
			$items->seek($items->count()-1);
			$itemLast = $table->complementRow($items->current());
			
			$items->seek($pager->getPrevPage()-1);
			$itemPrev = $table->complementRow($items->current());
			
			$items->seek($pager->getNextPage()-1);
			$itemNext = $table->complementRow($items->current());
			
			$items->seek($index-1);
			$itemCurrent = $table->complementRow($items->current());
			
			return array(
				'PREV' => $itemPrev,
				'NEXT' => $itemNext,
				'FIRST' => $itemFirst,
				'LAST' => $itemLast,
				'CURRENT' => $itemCurrent,
				'idPrev' => $idPrev,
				'idNext' => $idNext,
				'idFirst' => $idFirst,
				'idLast' => $idLast,
				'currItem' => $currItem,
				'numItems' => sizeof($items),
				'uriPrev' => $this->_getSlideUri($parentId, $itemPrev['id']),
				'uriNext' => $this->_getSlideUri($parentId, $itemNext['id']),
				'uriFirst' => $this->_getSlideUri($parentId, $itemFirst['id']),
				'uriLast' => $this->_getSlideUri($parentId, $itemLast['id'])
			);
        }
        catch (Exception $exception) {
			require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
			throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('slide section error', $exception);
		}
    }
    
    
    
    protected function _getSlideUri($parentId, $id)
    {
    	$args = array(
			Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
			Sitengine_Env::PARAM_ANCESTORID => $parentId,
			Sitengine_Env::PARAM_ID => $id
		);
		$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_FILES_SHARP);
		return $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
    }
    
}


?>