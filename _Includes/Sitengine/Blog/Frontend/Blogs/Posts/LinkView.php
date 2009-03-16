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
 * @package    Sitengine_Blog
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/View.php';


abstract class Sitengine_Blog_Frontend_Blogs_Posts_LinkView extends Sitengine_View {
    
    
    protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    protected $_inputMode = null;
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Blog_Frontend_Blogs_Posts_Controller)
    	{
    		$this->_controller = $config['controller'];
    	}
    	else {
    		require_once 'Sitengine/Exception.php';
        	throw new Sitengine_Exception('construct error');
    	}
    }
    
    
    
    public function setInputMode($inputMode)
    {
    	$this->_inputMode = $inputMode;
    }
    
    
    
    public function build()
    {
        try {
            $this->_controller->getViewHelper()->build();
			$this->_queries = $this->_controller->getViewHelper()->getQueries();
            $this->_settings = $this->_controller->getViewHelper()->getSettings();
            $this->_sections = $this->_controller->getViewHelper()->getSections();
            $this->setSection('POST', $this->_getMainSection());
            $this->setSection(
            	'COMMENTS',
            	$this->_controller->getViewHelper()->getComments(
            		$this->_controller->getEntity()->getId()
            	)
            );
            $this->setSection(
            	'COMMENTACTIONS',
            	$this->_controller->getViewHelper()->getCommentActions(
            		$this->_controller->getEntity()->getId()
            	)
            );
            return $this;
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
        	throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('build page error', $exception);
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
				'DICTIONARY' => $this->_controller->getDictionary()->getData()
			);
       	}
        catch (Exception $exception) {
			require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
			throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('build page error', $exception);
		}
    }
    
    
    public function setSection($name, array $data)
    {
        $this->_sections[$name] = $data;
    }
    
    
    
    protected function _getMainSection()
    {
        try {
        	$table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
			$table->setTranslation($this->_controller->getPreferences()->getTranslation());
        	$data = $table->complementRow($this->_controller->getEntity()->getRow());
        	#$data['cdate'] = $this->_controller->getViewHelper()->formatDate($data['cdate']);
            #$data['mdate'] = $this->_controller->getViewHelper()->formatDate($data['mdate']);
            $data = array_merge($data, $this->_controller->getViewHelper()->fetchAuthor($data['uid']));
			return $data;
        }
        catch (Exception $exception) {
			require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
			throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('form page error', $exception);
		}
    }
}


?>