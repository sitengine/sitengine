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


require_once 'Sitengine/View.php';


abstract class Sitengine_Newsletter_Frontend_Subscriptions_ConfirmUnsubscribeView extends Sitengine_View
{
    
	protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Newsletter_Frontend_Subscriptions_Controller)
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
            $this->_controller->getViewHelper()->build();
			$this->_queries = $this->_controller->getViewHelper()->getQueries();
            $this->_settings = $this->_controller->getViewHelper()->getSettings();
            $this->_sections = $this->_controller->getViewHelper()->getSections();
            
            require_once 'Sitengine/Sitemap/Page.php';
        	$page = new Sitengine_Sitemap_Page($this->_controller->getDatabase());
			$page->fetch($this->_controller->getSitemapPathConfirmUnsubscribeView());
			$this->headTitle($page->getTitle());
			$this->headMeta()->appendName('keywords', $page->getMetaKeywords());
			$this->headMeta()->appendName('description', $page->getMetaDescription());
            $this->setSection('SNIPPETS', $page->getSnippets());
            return $this;
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/Newsletter/Frontend/Subscriptions/Exception.php';
        	throw new Sitengine_Newsletter_Frontend_Subscriptions_Exception('build page error', $exception);
        }
    }
    
    
    
    
    
    public function getData()
    {
    	try {
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
				#'DICTIONARY' => $this->_controller->getTranslate()->translateGroup('data')->toArray()
			);
		}
        catch (Exception $exception) {
			require_once 'Sitengine/Newsletter/Frontend/Subscriptions/Exception.php';
			throw new Sitengine_Newsletter_Frontend_Subscriptions_Exception('build page error', $exception);
		}
    }
    
    
    
    public function setSection($name, array $data)
    {
        $this->_sections[$name] = $data;
    }
    
    
    
    
    
    
    protected function _getMainSection()
    {
        try {
        	
        }
        catch (Exception $exception) {
			require_once 'Sitengine/Newsletter/Frontend/Subscriptions/Exception.php';
			throw new Sitengine_Newsletter_Frontend_Subscriptions_Exception('list page error', $exception);
		}
    }
}


?>