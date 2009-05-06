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


abstract class Sitengine_Permiso_Backend_Groups_Entity
{
	
	protected $_controller = null;
    protected $_started = false;
    protected $_row = null;
    
    
    public function __construct(Sitengine_Permiso_Backend_Groups_Controller $controller)
    {
        $this->_controller = $controller;
    }
    
    
    
    public function getRow()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Permiso/Backend/Groups/Exception.php';
    		throw new Sitengine_Permiso_Backend_Groups_Exception('entity not started');
    	}
		return $this->_row;
    }
    
    
    
    public function refresh(array $updatedData)
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Permiso/Backend/Groups/Exception.php';
    		throw new Sitengine_Permiso_Backend_Groups_Exception('entity not started');
    	}
    	foreach($updatedData as $field => $val)
    	{
    		$this->_row->$field = $val;
    	}
    }
    
    
    
    public function start()
    {
        try {
            if($this->_started) { return true; }
            else { $this->_started = true; }
            
            $id = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ID);
            $table = $this->_controller->getFrontController()->getPermiso()->getGroupsTable();
            $select = $table->select()->where('id = ?', $id);
        	$row = $table->fetchRow($select);
        	if($row !== null) { $this->_row = $row; }
			else {
				require_once 'Zend/Log.php';
				require_once 'Sitengine/Permiso/Backend/Groups/Exception.php';
				throw new Sitengine_Permiso_Backend_Groups_Exception(
                    'resource not found',
                    Sitengine_Env::ERROR_NOT_FOUND,
                    null,
                    Zend_Log::INFO
                );
			}
            return true;
        }
        catch (Sitengine_Permiso_Backend_Groups_Exception $exception) {
            throw $exception;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Permiso/Backend/Groups/Exception.php';
            throw new Sitengine_Permiso_Backend_Groups_Exception('start entity error', $exception);
        }
    }
}
?>