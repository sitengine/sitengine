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


require_once 'Zend/Controller/Dispatcher/Standard.php';


class Sitengine_Controller_Dispatcher_Standard extends Zend_Controller_Dispatcher_Standard
{
	
	protected function _formatName($unformatted, $isAction = false)
    {
        return $unformatted;
    }
    
    
	public function formatControllerName($unformatted)
    {
        return $this->_formatName($unformatted);
    }
}


?>