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
 * @package    Sitengine
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Zend/Db/Table/Abstract.php';


class Sitengine_Db_Table extends Zend_Db_Table_Abstract
{
    
    
    protected $_now = null;
    
    
    public function getNow()
    {
    	if($this->_now === null)
    	{
			require_once 'Zend/Date.php';
			$date = new Zend_Date('en_US');
			$date->setTimezone('UTC');
			$this->_now = $date->get('YYYY-MM-dd HH:mm:ss');
		}
		return $this->_now;
    }

}