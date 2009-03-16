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



abstract class Sitengine_Permiso_Backend_Users_Memberships_Entity
{
    
    protected $_controller = null;
    protected $_started = false;
    protected $_ancestorId = '';
    protected $_breadcrumbsData = array();
    protected $_userData = null;
    protected $_data = null;
    
    
    
    public function __construct(Sitengine_Permiso_Backend_Users_Memberships_Controller $controller)
    {
        $this->_controller = $controller;
    }
    
    
    
    public function refreshData(array $updatedData)
    {
        $this->_data = array_merge($this->_data, $updatedData);
    }
    
    
    
    public function getData()
    {
        return $this->_data;
    }
    
    
    
    public function getAncestorId()
    {
        return $this->_ancestorId;
    }
    
    
    
    public function getBreadcrumbsData()
    {
        return $this->_breadcrumbsData;
    }
    
    
    
    public function start($id, $aid)
    {
    	try {
			if($this->_started) { return true; }
			else { $this->_started = true; }
			$this->_ancestorId = $aid;
			
			if(!$id) { $this->_data = array(); }
			else {
				$q  = 'SELECT * FROM '.$this->_controller->getPermiso()->getMembershipsTableName();
				$q .= ' WHERE id = '.$this->_controller->getDatabase()->quote($id);
				#$q .= ' AND '.Sitengine_Permiso::FIELD_OID.' = "'.$this->_controller->getPermiso()->getOrganization()->getId().'"';
				$statement = $this->_controller->getDatabase()->prepare($q);
				$statement->execute();
				$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
				if(sizeof($result)) { $this->_data = $result[0]; }
				else {
					require_once 'Zend/Log.php';
					throw $this->_controller->getExceptionInstance(
						'resource not found',
						Sitengine_Env::ERROR_NOT_FOUND,
						null,
						Zend_Log::INFO
					);
				}
				$this->_ancestorId = $this->_data['userId'];
			}
			
			$q  = 'SELECT * FROM '.$this->_controller->getPermiso()->getUsersTableName();
			$q .= ' WHERE id = '.$this->_controller->getDatabase()->quote($this->_ancestorId);
			#$q .= ' AND '.Sitengine_Permiso::FIELD_OID.' = "'.$this->_controller->getPermiso()->getOrganization()->getId().'"';
			$statement = $this->_controller->getDatabase()->prepare($q);
			$statement->execute();
			$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
			if(sizeof($result)) { $this->_userData = $result[0]; }
			else {
				require_once 'Zend/Log.php';
				throw $this->_controller->getExceptionInstance(
					'resource not found',
					Sitengine_Env::ERROR_NOT_FOUND,
                    null,
                    Zend_Log::INFO
				);
			}
			$this->_breadcrumbsData = array(
				$this->_userData
			);
			return true;
		}
		catch (Sitengine_Permiso_Backend_Users_Memberships_Exception $exception) {
            throw $exception;
        }
        catch (Exception $exception) {
            throw $this->_controller->getExceptionInstance('start entity error', $exception);
        }
    }
}
?>