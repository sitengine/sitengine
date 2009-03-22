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
 * @package    Sitengine_Error
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/View.php';


abstract class Sitengine_Error_IndexView extends Sitengine_View
{
    
	protected $_controller = null;
    protected $_queries = array();
    protected $_sections = array();
    protected $_stylesheets = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Error_Controller)
    	{
    		$this->_controller = $config['controller'];
    	}
    	else {
    		require_once 'Sitengine/Exception.php';
        	throw new Sitengine_Exception('construct error');
    	}
    }
    
    
    public function build()
    {
        try { return $this; }
        catch (Exception $exception) {
            require_once 'Sitengine/Error/Exception.php';
            throw new Sitengine_Error_Exception('build page error', $exception);
        }
    }
    
    
    public function getData()
    {
    	try {
			return array(
				'STYLESHEETS' => $this->_stylesheets,
				'QUERIES' => $this->_queries,
				'SECTIONS' => $this->_sections,
				#'ENV' => $this->_controller->getEnv()->getData(),
				#'Env' => $this->_controller->getEnv(),
				#'STATUS' => $this->_controller->getStatus()->getData(),
				#'DICTIONARY' => $this->_controller->getTranslate()->translateGroup('data')->toArray()
			);
		}
        catch (Exception $exception) {
			require_once 'Sitengine/Error/Exception.php';
			throw new Sitengine_Error_Exception('build page error', $exception);
		}
    }
    
}


?>