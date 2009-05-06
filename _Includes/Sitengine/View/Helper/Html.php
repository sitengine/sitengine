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

require_once 'Zend/View/Helper/Abstract.php';


class Sitengine_View_Helper_Html extends Zend_View_Helper_Abstract
{
    
    public function html($val)
    {
    	require_once 'Sitengine/String.php';
        return Sitengine_String::html($val);
    }
}
