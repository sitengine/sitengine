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
    protected $_userRow = null;
    protected $_row = null;
    
    
    public function __construct(Sitengine_Permiso_Backend_Users_Memberships_Controller $controller)
    {
        $this->_controller = $controller;
    }
    
    
    
    public function getRow()
    {
        if(!$this->_started) {
    		require_once 'Sitengine/Permiso/Backend/Users/Memberships/Exception.php';
    		throw new Sitengine_Permiso_Backend_Users_Memberships_Exception('entity not started');
    	}
		return $this->_row;
    }
    
    
    
    public function refresh(array $updatedData)
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Permiso/Backend/Users/Memberships/Exception.php';
    		throw new Sitengine_Permiso_Backend_Users_Memberships_Exception('entity not started');
    	}
        foreach($updatedData as $field => $val)
    	{
    		$this->_row->$field = $val;
    	}
    }
    
    
    public function getAncestorId()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Permiso/Backend/Users/Memberships/Exception.php';
    		throw new Sitengine_Permiso_Backend_Users_Memberships_Exception('entity not started');
    	}
        return $this->_userRow->id;
    }
    
    
    
    public function getBreadcrumbs()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Permiso/Backend/Users/Memberships/Exception.php';
    		throw new Sitengine_Permiso_Backend_Users_Memberships_Exception('entity not started');
    	}
    	return array(
    		'user' => $this->_userRow,
    		'membership' => $this->_row
    	);
    }
    
    
    
    public function start()
    {
        try {
            if($this->_started) { return true; }
            else { $this->_started = true; }
            
            $id = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ID);
            $aid = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ANCESTORID);
            
            if(!$id) { $this->_row = null; }
            else {
            	$table = $this->_controller->getFrontController()->getPermisoPackage()->getMembershipsTable();
				$select = $table->select()->where('id = ?', $id);
				$row = $table->fetchRow($select);
				if($row !== null) { $this->_row = $row; }
				else {
					require_once 'Zend/Log.php';
                    require_once 'Sitengine/Permiso/Backend/Users/Memberships/Exception.php';
                    throw new Sitengine_Permiso_Backend_Users_Memberships_Exception(
                        'resource not found',
                        Sitengine_Env::ERROR_NOT_FOUND,
						null,
						Zend_Log::INFO
                    );
                }
                $this->_ancestorId = $this->_row->userId;
            }
            
            
            $table = $this->_controller->getFrontController()->getPermisoPackage()->getUsersTable();
            $select = $table->select()->where('id = ?', $aid);
        	$row = $table->fetchRow($select);
        	if($row !== null) { $this->_userRow = $row; }
        	else {
        		require_once 'Zend/Log.php';
                require_once 'Sitengine/Permiso/Backend/Users/Memberships/Exception.php';
                throw new Sitengine_Permiso_Backend_Users_Memberships_Exception(
                    'resource not found',
                    Sitengine_Env::ERROR_NOT_FOUND,
                    null,
                    Zend_Log::INFO
                );
            }
            return true;
        }
        catch (Sitengine_Permiso_Backend_Users_Memberships_Exception $exception) {
            throw $exception;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Permiso/Backend/Users/Memberships/Exception.php';
            throw new Sitengine_Permiso_Backend_Users_Memberships_Exception('start entity error', $exception);
        }
    }
}
?>