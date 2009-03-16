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
 * @package    Sitengine_Proto
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Zend/Date.php';
require_once 'Sitengine/String.php';
require_once 'Sitengine/Form/Element.php';
require_once 'Sitengine/Grid/Pager.php';



require_once 'Sitengine/View.php';


abstract class Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_AssignView extends Sitengine_View {
    
    
    protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    
    
    public function __construct(array $config)
    {
    	if(isset($config['controller']) && $config['controller'] instanceof Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Controller)
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
            $this->setSection('FILELIST', $this->_getMainSection());
            return $this;
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Couldies/Exception.php';
        	throw new Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Exception('build page error', $exception);
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
			'DICTIONARY' => $this->_controller->getDictionary()->getData()
		);
    }
    
    
    
    public function setSection($name, array $data)
    {
        $this->_sections[$name] = $data;
    }
    
    
    
    
    
    
    protected function _getMainSection()
    {
        try {
        	$list = array();
			$markedRows = $this->_controller->getMarkedRows();
			#Sitengine_Debug::print_r($markedRows);
			$dir = $this->_controller->getTempDir();
			
			if(is_dir($dir))
			{
				$dirIterator = new DirectoryIterator($dir);
				$date = new Zend_Date(null, Zend_Date::ISO_8601, $this->_controller->getLocale());
				$date->setTimezone($this->_controller->getPreferences()->getTimezone());
				
				foreach($dirIterator as $count => $file)
				{
					if(is_file($dir.'/'.$file->getFilename()) && !preg_match('/^(\.|\.\.|\.DS_Store)$/', $file->getFilename()))
					{
						$filename = $file->getFilename();
						$id = 'item'.$count;
						
						$size = round($file->getSize()/1024/1024, 1);
						if($size >= 1) { $size .= 'MB'; }
						else { $size = round($file->getSize()/1024, 0).'KB'; }
						
						$date->setTimestamp($file->getCTime());
						$udate  = $date->get(Zend_Date::DATE_FULL).' ';
						$udate .= $date->get(Zend_Date::TIME_FULL);
						
						$p = 'SELECTROWITEM'.$id;
						$s = (sizeof($markedRows) && isset($markedRows[$id])) ? $this->_controller->getRequest()->getPost($p) : 0;
						$e = new Sitengine_Form_Element($p, 1);
						$e->setClass('listformCheckbox');
						$checkbox  = $e->getCheckbox($s);
						$checkbox .= Sitengine_Form_Element::getHidden('FILENAME'.$id, $filename);
						
						$n = 'type';
						$p = 'UPDATEROWITEM'.$n.'ITEMID'.$id;
						$h = 'UPDATEROWITEMCURRENT'.$n.'ITEMID'.$id;
						$v = (sizeof($markedRows) && isset($markedRows[$id])) ? $this->_controller->getRequest()->getPost($p) : Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Controller::VALUE_NONESELECTED;
						$e = new Sitengine_Form_Element($p, $v);
						$e->setClass('listformSelect');
						$type  = $e->getSelect($this->_controller->getDictionary()->getFromFieldvals($n));
						$type .= Sitengine_Form_Element::getHidden($h, Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Controller::VALUE_NONESELECTED);
						
						$args = array(
							Sitengine_Env::PARAM_CONTROLLER => 'couldiesUploads',
							Sitengine_Env::PARAM_FILE => $filename
						);
						$uriDownload  = $this->_controller->getFrontController()->getProtoPackage()->getDownloadHandler();
						$uriDownload .= Sitengine_Controller_Request_Http::makeNameValueQuery($args);
						
						$list[] = array(
							'id' => $id,
							'name' => $filename,
							'size' => $size,
							'udate' => $udate,
							'rowSelectCheckbox' => $checkbox,
							'type' => $type,
							'uriDownload' => $uriDownload,
							'isMarked' => (isset($markedRows[$id])) ? $markedRows[$id] : 0
						);
					}
				}
			}
			#Sitengine_Debug::print_r($list);
			
			########################################################################
            #### PAGER DATA
            #######################################################################
            $pagerData = array(
                'hiddens' => '',
                'currPageInput' => '',
                'currPage' => 1,
                'nextPage' => 1,
                'prevPage' => 1,
                'numPages' => 1,
                'numItems' => sizeof($list),
                'firstItem' => 1,
                'lastItem' => sizeof($list),
                'uriPrevPage' => '',
                'uriNextPage' => ''
            );
            
            
            
            ########################################################################
            #### URIS
            ########################################################################
            $args = array(
            	Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorId(),
            	Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHOULDIES_COULDIES_UPLOAD);
            $uriDoBatchUnlink = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            
            $args = array(
            	Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getGreatAncestorId(),
            	Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorId()
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHOULDIES_COULDIES_ASSIGN);
            $uriDoBatchAssign = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $uris = array(
            	'submitDoBatchAssign' => $uriDoBatchAssign,
            	'submitDoBatchUnlink' => $uriDoBatchUnlink
            );
            
            
            ########################################################################
            #### METHODS
            ########################################################################
            $methods = array(
            	'doBatchUnlink' => Sitengine_Env::METHOD_DELETE,
            	'doBatchAssign' => Sitengine_Env::METHOD_POST
            );
            
            
            ########################################################################
            #### COLLECT ALL DATA
            ########################################################################
            $hiddens = array(
            	Sitengine_Env::PARAM_METHOD => ''
            );
            foreach($hiddens as $k => $v) {
                $hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
            }
            
            return array(
                'hiddens' => implode('', $hiddens),
                'title' => $this->_controller->getDictionary()->getFromAssignView('assignFormTitle'),
                'URIS' => $uris,
                'METHODS' => $methods,
                'DATA' => $list,
                'PAGER' => $pagerData
            );
        }
        catch (Exception $exception) {
			require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Couldies/Exception.php';
			throw new Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Exception('list page error', $exception);
		}
    }
    
}


?>