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
 * @package    Sitengine_Proto
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


abstract class Sitengine_Proto_Backend_Goodies_Entity
{
	
	protected $_controller = null;
    protected $_started = false;
    protected $_row = null;
    
    
    public function __construct(Sitengine_Proto_Backend_Goodies_Controller $controller)
    {
        $this->_controller = $controller;
    }
    
    
    
    public function getRow()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Proto/Backend/Goodies/Exception.php';
    		throw new Sitengine_Proto_Backend_Goodies_Exception('entity not started');
    	}
		return $this->_row;
    }
    
    
    
    public function refresh(array $updatedData)
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Proto/Backend/Goodies/Exception.php';
    		throw new Sitengine_Proto_Backend_Goodies_Exception('entity not started');
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
            $table = $this->_controller->getFrontController()->getProtoPackage()->getGoodiesTable();
            $select = $table->select()->where('id = ?', $id);
        	$row = $table->fetchRow($select);
        	if($row !== null) { $this->_row = $row; }
			else {
				require_once 'Zend/Log.php';
				require_once 'Sitengine/Proto/Backend/Goodies/Exception.php';
				throw new Sitengine_Proto_Backend_Goodies_Exception(
                    'resource not found',
                    Sitengine_Env::ERROR_NOT_FOUND,
                    null,
                    Zend_Log::INFO
                );
			}
            if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_row->toArray())) { return false; }
            else { return true; }
        }
        catch (Sitengine_Proto_Backend_Goodies_Exception $exception) {
            throw $exception;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Proto/Backend/Goodies/Exception.php';
            throw new Sitengine_Proto_Backend_Goodies_Exception('start entity error', $exception);
        }
    }
}
?>