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


abstract class Sitengine_Newsletter_Backend_Campaigns_Attachments_Entity
{
    
    protected $_controller = null;
    protected $_started = false;
    protected $_campaignRow = null;
    protected $_row = null;
    
    
    public function __construct(Sitengine_Newsletter_Backend_Campaigns_Attachments_Controller $controller)
    {
        $this->_controller = $controller;
    }
    
    
    
    public function getRow()
    {
        if(!$this->_started) {
    		require_once 'Sitengine/Newsletter/Backend/Campaigns/Attachments/Exception.php';
    		throw new Sitengine_Newsletter_Backend_Campaigns_Attachments_Exception('entity not started');
    	}
		return $this->_row;
    }
    
    
    
    public function refresh(array $updatedData)
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Newsletter/Backend/Campaigns/Attachments/Exception.php';
    		throw new Sitengine_Newsletter_Backend_Campaigns_Attachments_Exception('entity not started');
    	}
        foreach($updatedData as $field => $val)
    	{
    		$this->_row->$field = $val;
    	}
    }
    
    
    public function getAncestorId()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Newsletter/Backend/Campaigns/Attachments/Exception.php';
    		throw new Sitengine_Newsletter_Backend_Campaigns_Attachments_Exception('entity not started');
    	}
        return $this->_campaignRow->id;
    }
    
    
    
    public function getBreadcrumbs()
    {
    	if(!$this->_started) {
    		require_once 'Sitengine/Newsletter/Backend/Campaigns/Attachments/Exception.php';
    		throw new Sitengine_Newsletter_Backend_Campaigns_Attachments_Exception('entity not started');
    	}
    	return array(
    		'campaign' => $this->_campaignRow,
    		'attachment' => $this->_row
    	);
    }
    
    
    
    public function start()
    {
        try {
            if($this->_started) { return true; }
            else { $this->_started = true; }
            
            $id = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ID);
            $aid = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ANCESTORID);
            
            if(!$id) { $this->_row = null; }
            else {
            	$table = $this->_controller->getFrontController()->getNewsletterPackage()->getAttachmentsTable();
				$select = $table->select()->where('id = ?', $id);
				$row = $table->fetchRow($select);
				if($row !== null) { $this->_row = $row; }
				else {
					require_once 'Zend/Log.php';
                    require_once 'Sitengine/Newsletter/Backend/Campaigns/Attachments/Exception.php';
                    throw new Sitengine_Newsletter_Backend_Campaigns_Attachments_Exception(
                        'resource not found',
                        Sitengine_Env::ERROR_NOT_FOUND,
						null,
						Zend_Log::INFO
                    );
                }
                if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_row->toArray())) { return false; }
                $this->_ancestorId = $this->_row->campaignId;
            }
            
            
            $table = $this->_controller->getFrontController()->getNewsletterPackage()->getCampaignsTable();
            $select = $table->select()->where('id = ?', $aid);
        	$row = $table->fetchRow($select);
        	if($row !== null) { $this->_campaignRow = $row; }
        	else {
        		require_once 'Zend/Log.php';
                require_once 'Sitengine/Newsletter/Backend/Campaigns/Attachments/Exception.php';
                throw new Sitengine_Newsletter_Backend_Campaigns_Attachments_Exception(
                    'resource not found',
                    Sitengine_Env::ERROR_NOT_FOUND,
                    null,
                    Zend_Log::INFO
                );
            }
            if(!$this->_controller->getPermiso()->getDac()->readAccessGranted($this->_campaignRow->toArray())) { return false; }
            return true;
        }
        catch (Sitengine_Newsletter_Backend_Campaigns_Attachments_Exception $exception) {
            throw $exception;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Newsletter/Backend/Campaigns/Attachments/Exception.php';
            throw new Sitengine_Newsletter_Backend_Campaigns_Attachments_Exception('start entity error', $exception);
        }
    }
}
?>