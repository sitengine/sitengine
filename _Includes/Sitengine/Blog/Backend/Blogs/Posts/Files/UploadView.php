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


require_once 'Zend/Date.php';
require_once 'Sitengine/Mime/Type.php';
require_once 'Sitengine/String.php';
require_once 'Sitengine/Form/Element.php';



require_once 'Sitengine/View.php';


abstract class Sitengine_Blog_Backend_Blogs_Posts_Files_UploadView extends Sitengine_View {
    
    
    protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    protected $_inputMode = null;
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Blog_Backend_Blogs_Posts_Files_Controller)
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
            $this->setSection('VIEWFORM', $this->_getMainSection());
            return $this;
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/Blog/Backend/Blogs/Posts/Files/Exception.php';
        	throw new Sitengine_Blog_Backend_Blogs_Posts_Files_Exception('build page error', $exception);
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
			require_once 'Sitengine/Blog/Backend/Blogs/Posts/Files/Exception.php';
			throw new Sitengine_Blog_Backend_Blogs_Posts_Files_Exception('build page error', $exception);
		}
    }
    
    
    public function setSection($name, array $data)
    {
        $this->_sections[$name] = $data;
    }
    
    
    
    
    
    protected function _getMainSection()
    {
        try {
			$args = array(
				Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorSlug(),
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$query = array(
				Sitengine_Env::PARAM_SESSIONID => Zend_Session::getId(),
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_POSTS_FILES_UPLOAD);
			$submitUri  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			$submitUri .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
			
			require_once $this->_controller->getEnv()->getContribDir().'/FlexUpload/class.flexupload.inc.php';
			$flex = new FlexUpload($submitUri);
			$flex->setWidth('100%');
			#$flex->setHeight('100%');
			$flex->setMaxFiles(20);
			$flex->setMaxFileSize(15*1024*1024);
			
			$type = $this->_controller->getEntity()->getAncestorType();
			if($type == Sitengine_Blog_Posts_Table::TYPE_GALLERY) {
				$types = '*.gif;*.jpg;*.jpeg';
			}
			else {
				$types = '*.zip;*.mp3;*.wav;*.gif;*.jpg;*.jpeg;*.pdf;*.doc;*.xls';
			}
			$flex->setFileExtensions($types);
			$flex->setPathToSWF($this->_controller->getEnv()->getContribRequestDir().'/FlexUpload/');
			$flex->setPathToSWFObject($this->_controller->getEnv()->getContribRequestDir().'/FlexUpload/js/');
			$flex->setLocale($this->_controller->getEnv()->getContribRequestDir().'/FlexUpload/locale/en.xml');
			
			return array(
				'flex' => $flex->getHTML()
			);
        }
        catch (Exception $exception) {
			require_once 'Sitengine/Blog/Backend/Blogs/Posts/Files/Exception.php';
			throw new Sitengine_Blog_Backend_Blogs_Posts_Files_Exception('form page error', $exception);
		}
    }
    
}


?>