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
 * @package    Sitengine_Sitemap
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */



abstract class Sitengine_Sitemap_Backend_Entity
{
    
    protected $_controller = null;
    protected $_started = false;
    protected $_data = null;
    protected $_parentId = '';
    protected $_threadData = array();
    protected $_isRootLevel = true;
    protected $_isRootListing = true;
    
    
    public function __construct(Sitengine_Sitemap_Backend_Controller $controller)
    {
        $this->_controller = $controller;
    }
    
    
    
    public function getData()
    {
        return $this->_data;
    }
    
    
    
    public function refreshData(array $updatedData)
    {
        $this->_data = array_merge($this->_data, $updatedData);
    }
    
    
    
    public function getParentId()
    {
        return $this->_parentId;
    }
    
    
    
    public function isRootLevel()
    {
        return $this->_isRootLevel;
    }
    
    
    
    public function isRootListing()
    {
        return $this->_isRootListing;
    }
    
    
    
    public function getThreadData()
    {
        return $this->_threadData;
    }
    
    
    
    public function start($id, $parentId='')
    {
        try {
            if($this->_started) { return true; }
            else { $this->_started = true; }
            
            if($id) {
                $q  = 'SELECT * FROM '.$this->_controller->getFrontController()->getSitemapPackage()->getTableSitemap();
                $q .= ' WHERE id = '.$this->_controller->getDatabase()->quote($id);
                #$q .= ' AND '.Sitengine_Permiso::FIELD_OID.' = "'.$this->_controller->getPermiso()->getOrganization()->getId().'"';
                $statement = $this->_controller->getDatabase()->prepare($q);
				$statement->execute();
				$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
				if(sizeof($result)) { $this->_data = $result[0]; }
				else {
					require_once 'Zend/Log.php';
                    require_once 'Sitengine/Sitemap/Backend/Exception.php';
                    throw new Sitengine_Sitemap_Backend_Exception(
                        'resource not found',
                        Sitengine_Env::ERROR_NOT_FOUND,
						null,
						Zend_Log::INFO
                    );
                }
                if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_data)) { return false; }
                else {
                    $this->_threadData = $this->_getThread($this->_data['pid']);
                    if(!is_array($this->_threadData))
                    {
                    	require_once 'Zend/Log.php';
                        require_once 'Sitengine/Sitemap/Backend/Exception.php';
                        throw new Sitengine_Sitemap_Backend_Exception(
                            'resource not found',
                            Sitengine_Env::ERROR_NOT_FOUND,
						null,
						Zend_Log::INFO
                        );
                    }
                }
                if(sizeof($this->_threadData)) {
                    $this->_parentId = $this->_data['id'];
                    $this->_isRootLevel = false;
                }
                $this->_isRootListing = false;
            }
            else if($parentId)
            {
                $this->_threadData = $this->_getThread($parentId);
                
                if(!is_array($this->_threadData))
                {
                	require_once 'Zend/Log.php';
                    require_once 'Sitengine/Sitemap/Backend/Exception.php';
                    throw new Sitengine_Sitemap_Backend_Exception(
                        'resource not found',
                        Sitengine_Env::ERROR_NOT_FOUND,
						null,
						Zend_Log::INFO
                    );
                }
                $this->_data = $this->_threadData[sizeof($this->_threadData)-1];
                unset($this->_threadData[sizeof($this->_threadData)-1]);
                $this->_parentId = $parentId;
                $this->_isRootLevel = false;
                $this->_isRootListing = false;
            }
            else
            {
                $this->_threadData = array();
                $this->_isRootListing = true;
            }
            foreach($this->_threadData as $k => $data)
            {
                if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($data)) {
                    return false;
                }
            }
            return true;
        }
        catch (Sitengine_Sitemap_Backend_Exception $exception) {
            throw $exception;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Sitemap/Backend/Exception.php';
            throw new Sitengine_Sitemap_Backend_Exception('start entity error', $exception);
        }
    }
    
    
    
    
    
    protected function _getThread($pid)
    {
        try {
            $found = true;
            $thread = array();
            
            while($pid)
            {
                $q  = 'SELECT * FROM '.$this->_controller->getFrontController()->getSitemapPackage()->getTableSitemap();
                $q .= ' WHERE id = '.$this->_controller->getDatabase()->quote($pid);
                #$q .= ' AND '.Sitengine_Permiso::FIELD_OID.' = "'.$this->_controller->getPermiso()->getOrganization()->getId().'"';
                $statement = $this->_controller->getDatabase()->prepare($q);
				$statement->execute();
				$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
				if(sizeof($result)) {
                    $pid =  $result[0]['pid'];
                    $thread[] = $result[0];
                }
                else {
                    $found = false;
                    break;
                }
            }
            return ($found) ? array_reverse($thread) : false;
        }
        catch (Exception $exception) { throw $exception; }
    }
    
}
?>