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
 * @package    Sitengine_Amazon_S3
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */

require_once 'Sitengine/Amazon/S3/Response.php';


class Sitengine_Amazon_S3_Bucket_Response_Acl extends Sitengine_Amazon_S3_Response
{
    
   public function getOwnerId()
    {
    	if(isset($this->_xml->Owner->ID))
		{
			return $this->_xml->Owner->ID;
		}
		return null;
    }
    
    
    public function getOwnerDisplayName()
    {
    	if(isset($this->_xml->Owner->DisplayName))
		{
			return $this->_xml->Owner->DisplayName;
		}
		return null;
    }
    
}

?>