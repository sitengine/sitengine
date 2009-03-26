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
 * @package    Sitengine_ScaffoldSimple
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/View.php';


class Sitengine_ScaffoldSimple_Frontend_Homies_ViewHelper extends Sitengine_View
{
    
	protected $_controller = null;
    
    
    public function __construct(array $config)
    {
    	if(!isset($config['controller']) || !$config['controller'] instanceof Sitengine_ScaffoldSimple_Frontend_Homies_Controller)
    	{
    		require_once 'Sitengine/Exception.php';
        	throw new Sitengine_Exception('construct error');
    	}
    	
    	$this->_controller = $config['controller'];
    }
}

?>