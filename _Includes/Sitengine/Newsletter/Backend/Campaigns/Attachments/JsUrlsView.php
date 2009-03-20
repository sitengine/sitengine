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
 * @package    Sitengine_Newsletter
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Zend/Date.php';
require_once 'Sitengine/String.php';
require_once 'Sitengine/Form/Element.php';
require_once 'Sitengine/Grid/Pager.php';



require_once 'Sitengine/View.php';


abstract class Sitengine_Newsletter_Backend_Campaigns_Attachments_JsUrlsView extends Sitengine_View {
    
    
    protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Newsletter_Backend_Campaigns_Attachments_Controller)
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
        try {
            $this->setSection('ATTACHMENTS', $this->_getAttachmentsSection());
            return $this;
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/Newsletter/Backend/Campaigns/Attachments/Exception.php';
        	throw new Sitengine_Newsletter_Backend_Campaigns_Attachments_Exception('build page error', $exception);
        }
    }
    
    
    
    
    
    public function getData()
    {
		return array(
			'QUERIES' => $this->_queries,
			'SECTIONS' => $this->_sections,
			'SETTINGS' => $this->_settings,
			#'ENV' => $this->_controller->getEnv()->getData(),
			#'Env' => $this->_controller->getEnv(),
			#'STATUS' => $this->_controller->getStatus()->getData(),
			#'ORGANIZATION' => $this->_controller->getPermiso()->getOrganization()->getData(),
			#'USER' => $this->_controller->getPermiso()->getAuth()->getData(),
			#'Auth' => $this->_controller->getPermiso()->getAuth(),
			#'DICTIONARY' => $this->_controller->getTranslate()->translateGroup('data')
		);
    }
    
    
    
    public function setSection($name, array $data)
    {
        $this->_sections[$name] = $data;
    }
    
    
    
    
    
    
    protected function _getAttachmentsSection()
    {
        try {
			$table = $this->_controller->getFrontController()->getNewsletterPackage()->getAttachmentsTable();
			$name = $this->_controller->getFrontController()->getNewsletterPackage()->getAttachmentsTableName();
			
			$whereClauses = array(
				$this->_controller->getDatabase()->quoteInto('campaignId = ?', $this->_controller->getEntity()->getAncestorId()),
        		$this->_controller->getPermiso()->getDac()->getReadAccessSql($this->_controller->getFrontController()->getNewsletterPackage()->getAuthorizedGroups(), $name, false)
        	);
			
        	$select = $table
        		->select()
        		->order('title ASC')
        	;
        	foreach($whereClauses as $clause)
        	{
        		if($clause) { $select->where($clause); }
        	}
        	$items = $table->fetchAll($select);
			
            
            ########################################################################
            #### LISTDATA
            ########################################################################
            $list = array();
            
            foreach($items as $item)
            {
            	$row = $table->complementRow($item);
            	$url = $row['file1OriginalUri'];
                $row['url'] = preg_replace('/&amp;/', '&', $url);
                $list[] = $row;
            }
            #Sitengine_Debug::print_r($list);
            
            return array(
                'DATA' => $list
            );
        }
        catch (Exception $exception) {
			require_once 'Sitengine/Newsletter/Backend/Campaigns/Attachments/Exception.php';
			throw new Sitengine_Newsletter_Backend_Campaigns_Attachments_Exception('list page error', $exception);
		}
    }
    
}


?>