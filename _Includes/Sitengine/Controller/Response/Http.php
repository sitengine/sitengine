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


require_once 'Zend/Controller/Response/Http.php';


class Sitengine_Controller_Response_Http extends Zend_Controller_Response_Http
{
 	
 	public function sendResponse()
    {
        $this->sendHeaders();

        if ($this->isException() && $this->renderExceptions()) {
            $exceptions = '';
            foreach ($this->getException() as $e) {
                $exceptions .= $e->__toString() . "\n";
            }
            echo preg_replace('/(#\d)/', '<br />\1', $exceptions);
            return;
        }

        $this->outputBody();
    }
}


?>