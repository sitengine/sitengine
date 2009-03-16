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



require_once 'Sitengine/Permiso/Exception.php';


class Sitengine_Permiso_Audit
{
    
    protected $_permiso = null;
    protected $_database = null;
    
    
    public function __construct(
    	Sitengine_Permiso $permiso,
    	Zend_Db_Adapter_Abstract $database
    )
    {
    	$this->_permiso = $permiso;
        $this->_database = $database;
    }
    
    
    
    public function log($source, $action, $code = null, $message = null)
    {
    	try {
    		/*
    		if(
    			!$this->_permiso->getAuth()->hasIdentity() &&
    			($source != 'permiso' && $action != 'login')
    		) {
    			return false;
    		}
    		*/
    		require_once 'Sitengine_String.php';
    		require_once 'Zend/Date.php';
        	$date = new Zend_Date();
			$date->setTimezone('UTC');
			
    		$data = array(
    			'id' => Sitengine_String::createId(),
    			'uid' => $this->_permiso->getAuth()->getId(),
    			'name' => $this->_permiso->getAuth()->getIdentity(),
    			'cdate' => $date->get(Zend_Date::ISO_8601),
    			'ip' => $_SERVER['REMOTE_ADDR'],
    			'source' => $source,
    			'action' => $action,
    			'code' => $code,
    			'message' => $message
    		);
    		return ($this->_database->insert($this->_permiso->getAuditTableName(), $data));
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Permiso/Exception.php';
			throw new Sitengine_Permiso_Exception('log error', $exception);
		}
    }
    
}


?>