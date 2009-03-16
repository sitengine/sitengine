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
require_once 'Sitengine/String.php';
require_once 'Sitengine/Form/Element.php';
require_once 'Sitengine/Grid/Pager.php';



require_once 'Sitengine/View.php';


abstract class Sitengine_Blog_Frontend_Blogs_Posts_IndexView extends Sitengine_View {
    
    
    protected $_controller = null;
    protected $_queries = array();
    protected $_settings = array();
    protected $_sections = array();
    protected $_defaultIpp = 20;
    
    
    
    public function setDefaultIpp($defaultIpp)
    {
    	$this->_defaultIpp = $defaultIpp;
    	return $this;
    }
    
    public function getDefaultIpp() { return $this->_defaultIpp; }
    
    
    
    
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
    
    
    
    
    public function getAtomOld()
    {
    	$data = $this->_getMainSection();
    	#Sitengine_Debug::print_r($data['DATA']);
    	
    	
    	$data = array(
			'title' => 'KOMPAKT.fm // BLOG', //required
			'link' => 'http://feeds.kompakt.fm/blog/index', //required
			#'lastUpdate' => 'timestamp of the update date', // optional
			#'published' => 'timestamp of the publication date', //optional
			'charset' => 'UTF-8', // required
			#'description' => '', //optional
			#'author' => 'Sir Prize', //optional
			'email' => 'web@kompakt.fm', //optional
			'webmaster' => 'web@kompakt.fm', // optional, ignored if atom is used
			#'copyright' => 'All Rights Reserved', //optional
			#'image' => 'url to image', //optional
			'generator' => 'sitengine', // optional
			'language' => 'en', // optional
			#'ttl' => 'how long in minutes a feed can be cached before refreshing', // optional, ignored if atom is used
			#'rating' => 'The PICS rating for the channel.', // optional, ignored if atom is used
			/*'cloud' => array(
				'domain' => 'pantichrist.com', // required
				'port' => '80', // optional, default to 80
				'path' => '/RPC2', //required
				'registerProcedure' => 'procedure to call, e.g. myCloud.rssPleaseNotify', // required
				'protocol' => 'protocol to use, e.g. soap or xml-rpc', // required
			), // a cloud to be notified of updates // optional, ignored if atom is used
			
			'textInput' => array(
				'title' => 'the label of the Submit button in the text input area', // required,
				'description' => 'explains the text input area', // required
				'name' => 'the name of the text object in the text input area', // required
				'link' => 'the URL of the CGI script that processes text input requests', // required
			), // a text input box that can be displayed with the feed // optional, ignored if atom is used
			'skipHours' => array(
				'hour in 24 format', // e.g 13 (1pm)
				// up to 24 rows whose value is a number between 0 and 23
			), // Hint telling aggregators which hours they can skip // optional, ignored if atom is used
			'skipDays ' => array(
				'a day to skip', // e.g Monday
				// up to 7 rows whose value is a Monday, Tuesday, Wednesday, Thursday, Friday, Saturday or Sunday
			), // Hint telling aggregators which days they can skip // optional, ignored if atom is used
			/*
			'itunes' => array(
				'author' => 'Artist column', // optional, default to the main author value
				'owner' => array(
					'name' => 'name of the owner', // optional, default to main author value
					'email' => 'email of the owner', // optional, default to main email value
				), // Owner of the podcast // optional
				'image' => 'album/podcast art', // optional, default to the main image value
				'subtitle' => 'short description', // optional, default to the main description value
				'summary' => 'longer description', // optional, default to the main description value
				'block' => 'Prevent an episode from appearing (yes|no)', // optional
				'category' => array(
					array(
						'main' => 'main category', // required
						'sub' => 'sub category', // optional
					), // up to 3 rows
				), // 'Category column and in iTunes Music Store Browse', // required
				'explicit' => 'parental advisory graphic (yes|no|clean)', // optional
				'keywords' => 'a comma separated list of 12 keywords maximum', // optional
				'new-feed-url' => 'used to inform iTunes of new feed URL location', // optional
			), // Itunes extension data // optional, ignored if atom is used
			*/
			
			/*
			'entries' => array(
				array(
					'title' => 'title of the feed entry', //required
					'link' => 'url to a feed entry', //required
					'description' => 'short version of a feed entry', // only text, no html, required
					'guid' => 'id of the article, if not given link value will used', //optional
					'content' => 'long version', // can contain html, optional
					#'lastUpdate' => 'timestamp of the publication date', // optional
					'comments' => 'comments page of the feed entry', // optional
					'commentRss' => 'the feed url of the associated comments', // optional
					'source' => array(
						'title' => 'title of the original source', // required,
						'url' => 'url of the original source', // required
					), // original source of the feed entry // optional
					
					'category' => array(
						array(
							'term' => 'first category label', // required,
							'scheme' => 'url that identifies a categorization scheme', // optional
						),
						array(
							//data for the second category and so on
							)
					), // list of the attached categories // optional
					'enclosure' => array(
						array(
							'url' => 'url of the linked enclosure', // required
							'type' => 'mime type of the enclosure', // optional
							'length' => 'length of the linked content in octets', // optional
							),
						array(
							//data for the second enclosure and so on
							)
						), // list of the enclosures of the feed entry // optional
					),
				)
			),
			
			'entries' => array(
			
			)
			*/
		);
		
		$list = $this->_getMainSection();
		foreach($list['DATA'] as $item)
		{
			$data['entries'][] = array(
				'title' => $item['title'], //required
				'link' => 'http://'.$_SERVER['SERVER_NAME'].$item['uriView'], //required
				'description' => $item['teaser'], // only text, no html, required
				#'guid' => 'id of the article, if not given link value will used', //optional
				'content' => $item['markup'], // can contain html, optional
				#'lastUpdate' => $item['mdate'], // optional
				'comments' => 'http://'.$_SERVER['SERVER_NAME'].$item['uriView'], // optional
				#'commentRss' => 'the feed url of the associated comments', // optional
				/*
				'source' => array(
					'title' => 'title of the original source', // required,
					'url' => 'url of the original source', // required
				)
				*/
			);
		}
		
		require_once 'Zend/Uri/Http.php';
		require_once 'Zend/Feed.php';
		$atom = Zend_Feed::importArray($data, 'atom');
		return $atom->send();
    }
    
    
    
    
    protected $_rssTitle = 'KOMPAKT.fm // BLOG';
    protected $_rssLink = 'http://feeds.kompakt.fm/blog/index';
    protected $_rssEmail = 'web@kompakt.fm';
    protected $_rssWebmaster = 'web@kompakt.fm';
    
    
    
    public function getAtom()
    {
    	$table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
		
		$whereClauses = array(
			"publish = '1'",
			#'cdate = '.new Zend_Db_Expr("CURDATE()"),
			$this->_controller->getDatabase()->quoteInto('blogId = ?', $this->_controller->getEntity()->getAncestorId())
		);
		
		$select = $table
			->select()
			->order('cdate DESC')
			->limit(20, 0)
		;
		foreach($whereClauses as $clause)
		{
			if($clause) { $select->where($clause); }
		}
		$items = $table->fetchAll($select);
    	
    	
    	$data = array(
			'title' => $this->_rssTitle, //required
			'link' => $this->_rssLink, //required
			#'lastUpdate' => 'timestamp of the update date', // optional
			#'published' => 'timestamp of the publication date', //optional
			'charset' => 'UTF-8', // required
			#'description' => '', //optional
			#'author' => 'Sir Prize', //optional
			'email' => $this->_rssEmail, //optional
			'webmaster' => $this->_rssWebmaster, // optional, ignored if atom is used
			#'copyright' => 'All Rights Reserved', //optional
			#'image' => 'url to image', //optional
			'generator' => 'sitengine', // optional
			'language' => 'en', // optional
		);
		
		
		foreach($items as $item)
		{
			$row = $table->complementRow($item);
			#Sitengine_Debug::print_r($row);
			$args = array(
				Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorSlug(),
				Sitengine_Env::PARAM_ID => $row['id']
			);
			$route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_SHARP);
			$uriView = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
			
			require_once 'Zend/Date.php';
			$date = new Zend_Date($item->cdate, 'yyyy-MM-dd HH:mm:ss');
			$date->setTimezone('UTC');
			$cdate = $date->get(Zend_Date::TIMESTAMP);
			
			$post = array(
				'link' => 'http://'.$_SERVER['SERVER_NAME'].$uriView,
				'comments' => 'http://'.$_SERVER['SERVER_NAME'].$uriView,
				'lastUpdate' => $cdate
			);
			
			#print $item->cdate.'<br />';
			
			#html_entity_decode(strip_tags($row['info']), ENT_COMPAT, 'UTF-8')
			
			switch($row['type'])
			{
				case Sitengine_Blog_Posts_Table::TYPE_VIDEO:
				{
					$postDetail = array(
						'title' => $row['title'], //required
						'description' => '(Video) '.$row['teaser'], // only text, no html, required
						'content' => $row['teaser'] // can contain html, optional
					);
					break;
				}
				case Sitengine_Blog_Posts_Table::TYPE_AUDIO:
				{
					$postDetail = array(
						'title' => $row['title'], //required
						'description' => '(Audio) '.html_entity_decode(strip_tags($row['markup']), ENT_COMPAT, 'UTF-8'), // only text, no html, required
						'content' => $row['markup'], // can contain html, optional
						'enclosure' => array(
							array(
								'url' => $row['file1OriginalUri'], // required
								'type' => $row['file1OriginalMime'], // optional
								#'length' => 'length of the linked content in octets', // optional
							)
						) // list of the enclosures of the feed entry // optional
					);
					break;
				}
				case Sitengine_Blog_Posts_Table::TYPE_LINK:
				{
					$postDetail = array(
						'title' => $row['title'], //required
						'description' => $row['teaser'], // only text, no html, required
						'content' => $row['teaser'] // can contain html, optional
					);
					break;
				}
				case Sitengine_Blog_Posts_Table::TYPE_QUOTE:
				{
					$postDetail = array(
						'title' => $row['teaser'], //required
						'description' => ($row['title']) ? 'Quote By '.$row['title'] : 'Quote', // only text, no html, required
						'content' => ($row['title']) ? 'Quote By '.$row['title'] : 'Quote' // can contain html, optional
					);
					break;
				}
				case Sitengine_Blog_Posts_Table::TYPE_GALLERY:
				{
					$postDetail = array(
						'title' => $row['title'], //required
						'description' => $row['teaser'], // only text, no html, required
						'content' => $row['markup'] // can contain html, optional
					);
					break;
				}
				case Sitengine_Blog_Posts_Table::TYPE_PHOTO:
				{
					$postDetail = array(
						'title' => $row['title'], //required
						'description' => html_entity_decode(strip_tags($row['markup']), ENT_COMPAT, 'UTF-8'), // only text, no html, required
						'content' => $row['markup'] // can contain html, optional
					);
					break;
				}
				case Sitengine_Blog_Posts_Table::TYPE_TEXT:
				{
					$postDetail = array(
						'title' => $row['title'], //required
						'description' => $row['teaser'], // only text, no html, required
						'content' => $row['markup'] // can contain html, optional
					);
					break;
				}
			}
			
			$data['entries'][] = array_merge($post, $postDetail);
		}
		
		require_once 'Zend/Uri/Http.php';
		require_once 'Zend/Feed.php';
		$atom = Zend_Feed::importArray($data, 'atom');
		return $atom->send();
    }
    
    
    
    
    
    
    public function build()
    {
        try {
            $this->_controller->getViewHelper()->build();
			$this->_queries = $this->_controller->getViewHelper()->getQueries();
            $this->_settings = $this->_controller->getViewHelper()->getSettings();
            $this->_sections = $this->_controller->getViewHelper()->getSections();
            $this->setSection('POSTS', $this->_getMainSection());
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
            $valuePage = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAGE);
            $table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTable();
			$table->setTranslation($this->_controller->getPreferences()->getTranslation());
        	
            $filter = $table->getFilterInstance(
            	$this->_controller->getRequest(),
            	array(
					'find' => Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_FIND,
					'type' => Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_TYPE,
					'reset' => Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_RESET
				)
            );
            
            $sorting = $table->getSortingInstance(
            	$this->_controller->getRequest()->get(Sitengine_Env::PARAM_SORT),
    			$this->_controller->getRequest()->get(Sitengine_Env::PARAM_ORDER)
            );
            
            
            ########################################################################
            #### SETTINGS
            ########################################################################
            $settingsIsActive = false;
            $settingsElements = array();
            
            /*
            ### translation element ###
            $translations = $table->getTranslations();
            $translations->setLanguage($this->_controller->getPreferences()->getLanguage());
            if(!$translations->isDefault()) { $settingsIsActive = true; }
            # set html input element
            $languages = array();
            foreach($translations->get() as $symbol) {
            	$languages[$symbol] = $this->_controller->getDictionary()->getFromLanguages($symbol);
            }
            $e = new Sitengine_Form_Element(Sitengine_Env::PARAM_TRANSLATION, $translations->getSymbol());
            $e->setId('settings'.Sitengine_Env::PARAM_TRANSLATION);
            $e->setClass('settingsSelect');
            $settingsElements[Sitengine_Env::PARAM_TRANSLATION] = $e->getSelect($languages);
            */
            
            
            ### ipp element ###
            $valueIpp = $this->_controller->getPreferences()->getItemsPerPage();
            $valueIpp = (is_numeric($valueIpp)) ? $valueIpp : $this->_defaultIpp;
        	$valueIpp = ($valueIpp <= 100 && $valueIpp >= 1) ? $valueIpp : $this->_defaultIpp;
            # set html input element
            $ippValues = array(
                '' => $this->_controller->getDictionary()->getFromLabels('settingsSectionItemsPerPage'),
                5 => 5,
                10 => 10,
                20 => 20,
                50 => 50,
                100 => 100
            );
            $e = new Sitengine_Form_Element(Sitengine_Env::PARAM_IPP, $valueIpp);
            $e->setId('settings'.Sitengine_Env::PARAM_IPP);
            $e->setClass('settingsSelect');
            $settingsElements[Sitengine_Env::PARAM_IPP] = $e->getSelect($ippValues);
            
            
            
            $hiddens = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder()
            );
            foreach($hiddens as $k => $v) {
                $hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
            }
            
            $settingsData = array(
                'isActive' => $settingsIsActive,
                'hiddens' => implode('', $hiddens),
                'ELEMENTS' => $settingsElements
            );
            
            
            ########################################################################
            #### FILTER
            ########################################################################
            $e = new Sitengine_Form_Element(Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_TYPE, $filter->getVal(Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_TYPE));
            $e->setId('filter'.Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_TYPE);
            $e->setClass('filterSelect');
            $filter->setElement(Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_TYPE, $e->getSelect($this->_controller->getDictionary()->getFromFieldvals(Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_TYPE)));
            /*
            $users = $this->_controller->getPermiso()->getDirectory()->getAllUsers();
            $values = array_merge($this->_controller->getDictionary()->getFromFieldvals(Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_UID), $users);
            $e = new Sitengine_Form_Element(Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_UID, $filter->getVal(Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_UID));
            $e->setId('filter'.Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_UID);
            $e->setClass('filterSelect');
            $filter->setElement(Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_UID, $e->getSelect($values));
            
            $groups = $this->_controller->getPermiso()->getDirectory()->getAllGroups();
            $values = array_merge($this->_controller->getDictionary()->getFromFieldvals(Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_GID), $groups);
            $e = new Sitengine_Form_Element(Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_GID, $filter->getVal(Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_GID));
            $e->setId('filter'.Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_GID);
            $e->setClass('filterSelect');
            $filter->setElement(Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_GID, $e->getSelect($values));
            */
            $e = new Sitengine_Form_Element(Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_FIND, $filter->getVal(Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_FIND));
            $e->setId('filter'.Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_FIND);
            $e->setClass('filterText');
            $filter->setElement(Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_FIND, $e->getText(20));
            
            
            $hiddens = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder()
            );
            foreach($hiddens as $k => $v) {
                $hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
            }
            
            $args = array(
                Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorSlug()
            );
            $query = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder(),
                Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_RESET => 1
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS);
            $uriReset  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            $uriReset .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
            
            $filterData = array(
                'isActive' => $filter->isActive(),
                'uriReset' => $uriReset,
                'hiddens' => implode('', $hiddens),
                'ELEMENTS' => $filter->getElements(),
                'DATA' => $filter->getData()
            );
            
            
            ########################################################################
            #### SORTING
            ########################################################################
            $queries = array();
            
            foreach($sorting->getOrdering() as $field => $order)
            {
            	$args = array(
                    Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorSlug()
                );
                $query = array(
                    Sitengine_Env::PARAM_SORT => $field,
                    Sitengine_Env::PARAM_ORDER => $order,
                    Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_FIND => $filter->getVal(Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_FIND)
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS);
                $queries[$field]  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                $queries[$field] .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
            }
            
            $sortingData = array(
                'QUERIES' => $queries,
                'COLUMNS' => $sorting->getColumns()
            );
            
            
            ########################################################################
            #### PAGER
            ########################################################################
            $pager = new Sitengine_Grid_Pager($valuePage, $valueIpp);
            
            
            /*
            ########################################################################
            #### QUERY
            ########################################################################
            $table = $this->_controller->getFrontController()->getBlogPackage()->getPostsTableName();
            
        	$whereClauses = array(
        		"$table.blogId = '".$this->_controller->getEntity()->getAncestorId()."'",
        		"$table.publish = '1'",
        		#$this->_controller->getPermiso()->getDac()->getReadAccessSql($this->_controller->getFrontController()->getBlogPackage()->getAuthorizedGroups(), $table, false),
        		$filter->getSql('')
        	);
        	
        	$orderClause = $sorting->getClause(true);
        	$items = $table->get($whereClauses, $orderClause, $valueIpp, $pager->getOffset());
        	
        	if($pager->getCurrPage() > 1 && !sizeof($items))
			{
				# current page is out of bounds - go to beginning of list
				$pager = new Sitengine_Grid_Pager(1, $valueIpp);
				$items = $table->get($whereClauses, $orderClause, $valueIpp, 0);
			}
			
			$pager->calculate($table->count($whereClauses));
			*/
			
			########################################################################
            #### LISTQUERY
            ########################################################################
			$name = $this->_controller->getFrontController()->getBlogPackage()->getPostsTableName();
			
			$whereClauses = array(
				"$name.publish = '1'",
				$this->_controller->getDatabase()->quoteInto('blogId = ?', $this->_controller->getEntity()->getAncestorId()),
        		#$this->_controller->getPermiso()->getDac()->getReadAccessSql($this->_controller->getFrontController()->getBlogPackage()->getAuthorizedGroups(), $name, false),
        		$filter->getSql('')
        	);
			
        	$select = $table
        		->select()
        		->order($sorting->getClause())
        		->limit($valueIpp, $pager->getOffset())
        	;
        	foreach($whereClauses as $clause)
        	{
        		if($clause) { $select->where($clause); }
        	}
        	$items = $table->fetchAll($select);
        	
        	
        	
        	# if current page is out of bounds - go to beginning of list
        	if(!$items->count() && $pager->getCurrPage() > 1)
			{
				$pager = new Sitengine_Grid_Pager(1, $valueIpp);
				$select = $table
					->select()
					->order($sorting->getClause())
					->limit($valueIpp, 0)
				;
				foreach($whereClauses as $clause)
				{
					if($clause) { $select->where($clause); }
				}
				$items = $table->fetchAll($select);
			}
			
			
			
			# count total number of records
			$select = $table->select()->from($table, array('COUNT(*) AS count'));
			foreach($whereClauses as $clause) { if($clause) { $select->where($clause); } }
			$count = $table->fetchRow($select);
			$pager->calculate($count->count);
			
			
            ########################################################################
            #### LISTDATA
            ########################################################################
            #$markedRows = $this->_controller->getMarkedRows();
            $list = array();
            
            foreach($items as $item)
            {
            	$row = $table->complementRow($item);
            	
            	/*
            	#Sitengine_Debug::print_r($row);
                # row selector checkbox
				$p = 'SELECTROWITEM'.$row['id'];
				$s = (sizeof($markedRows) && isset($markedRows[$row['id']])) ? $this->_controller->getRequest()->getPost($p) : 0;
				$e = new Sitengine_Form_Element($p, 1);
				$e->setClass('listformCheckbox');
				$row['rowSelectCheckbox'] = $e->getCheckbox($s);
                
                $n = 'publish';
                $p = 'UPDATEROWITEM'.$n.'ITEMID'.$row['id'];
                $h = 'UPDATEROWITEMCURRENT'.$n.'ITEMID'.$row['id'];
                $s = (sizeof($markedRows) && isset($markedRows[$row['id']])) ? $this->_controller->getRequest()->getPost($p) : $row[$n];
                $e = new Sitengine_Form_Element($p, 1);
                $e->setClass('listformCheckbox');
                $publish  = $e->getCheckbox($s);
                $publish .= Sitengine_Form_Element::getHidden($h, $row[$n]);
                */
                # uris
                $args = array(
                    Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorSlug(),
                    Sitengine_Env::PARAM_ID => $row['id']
                    #Sitengine_Env::PARAM_PAGE => $valuePage,
                    #Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                    #Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder()
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_SHARP);
                $uriView = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $args = array(
                    Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getAncestorSlug(),
                    Sitengine_Env::PARAM_ANCESTORID => $row['id']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_COMMENTS);
                $uriCommentIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $args = array(
                    Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getAncestorSlug(),
                    Sitengine_Env::PARAM_ANCESTORID => $row['id']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_COMMENTS);
                $uriCommentInsert = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                
                $args = array(
                    Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getAncestorSlug(),
                    Sitengine_Env::PARAM_ANCESTORID => $row['id']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_FILES);
                $uriFileIndex = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                /*
                $args = array(
                    Sitengine_Env::PARAM_GREATANCESTORID => $this->_controller->getEntity()->getAncestorSlug(),
                    Sitengine_Env::PARAM_ANCESTORID => $row['id']
                );
                $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_FILES_NEW);
                $uriFileInsert = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                
                $row['isMarked'] = (isset($markedRows[$row['id']])) ? $markedRows[$row['id']] : 0;
                $row['publishCheckbox'] = $publish;
                */
                $row['fileCount'] = $this->_controller->getViewHelper()->countFiles($row['id']);
                $row['commentCount'] = $this->_controller->getViewHelper()->countComments($row['id']);
                $row['uriFileIndex'] = $uriFileIndex;
                #$row['uriFileInsert'] = $uriFileInsert;
                $row['uriCommentIndex'] = $uriCommentIndex;
                $row['uriCommentInsert'] = $uriCommentInsert;
                $row['uriView'] = $uriView;
                ##$row['cdate'] = $this->_controller->getViewHelper()->formatDate($row['cdate']);
                ##$row['mdate'] = $this->_controller->getViewHelper()->formatDate($row['mdate']);
                
                $row = array_merge($row, $this->_controller->getViewHelper()->fetchAuthor($row['uid']));
                #Sitengine_Debug::print_r($row);
                $list[] = $row;
            }
            #Sitengine_Debug::print_r($list);
            
            ########################################################################
            #### PAGER DATA
            ########################################################################
            $hiddens = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder(),
                Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_FIND => $filter->getVal(Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_FIND)
            );
            foreach($hiddens as $k => $v) {
                $hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
            }
            
            $args = array(
                Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorSlug()
            );
            $query = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder(),
                Sitengine_Env::PARAM_PAGE => $pager->getPrevPage(),
                Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_FIND => $filter->getVal(Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_FIND)
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS);
            $uriPrevPage  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            $uriPrevPage .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
            
            $args = array(
                Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorSlug()
            );
            $query = array(
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder(),
                Sitengine_Env::PARAM_PAGE => $pager->getNextPage(),
                Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_FIND => $filter->getVal(Sitengine_Blog_Frontend_Blogs_Posts_Controller::PARAM_FILTER_BY_FIND)
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS);
            $uriNextPage  = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            $uriNextPage .= Sitengine_Controller_Request_Http::makeNameValueQuery($query);
            
            $currPageInput = new Sitengine_Form_Element(Sitengine_Env::PARAM_PAGE, $pager->getCurrPage());
            $currPageInput->setClass('pagerInput');
            
            $pagerData = array(
                'hiddens' => implode('', $hiddens),
                'currPageInput' => $currPageInput->getText(2),
                'currPage' => $pager->getCurrPage(),
                'nextPage' => $pager->getNextPage(),
                'prevPage' => $pager->getPrevPage(),
                'numPages' => $pager->getNumPages(),
                'numItems' => $pager->getNumItems(),
                'firstItem' => $pager->getFirstItem(),
                'lastItem' => $pager->getLastItem(),
                'uriPrevPage' => $uriPrevPage,
                'uriNextPage' => $uriNextPage
            );
            
            
            /*
            ########################################################################
            #### URIS
            ########################################################################
            $args = array(
            	Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorSlug()
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_BATCH);
            $uriDoBatchDelete = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $args = array(
            	Sitengine_Env::PARAM_ANCESTORID => $this->_controller->getEntity()->getAncestorSlug()
            );
            $route = $this->_controller->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_BATCH);
            $uriDoBatchUpdate = $this->_controller->getRequest()->getBasePath().'/'.$route->assemble($args, true);
            
            $uris = array(
            	'submitDoBatchDelete' => $uriDoBatchDelete,
            	'submitDoBatchUpdate' => $uriDoBatchUpdate
            );
            
            
            ########################################################################
            #### METHODS
            ########################################################################
            $methods = array(
            	'doBatchDelete' => Sitengine_Env::METHOD_DELETE,
            	'doBatchUpdate' => Sitengine_Env::METHOD_PUT
            );
            
            
            ########################################################################
            #### COLLECT ALL DATA
            ########################################################################
            $hiddens = array(
                Sitengine_Env::PARAM_METHOD => '',
                Sitengine_Env::PARAM_PAGE => $valuePage,
                Sitengine_Env::PARAM_SORT => $sorting->getActiveRule(),
                Sitengine_Env::PARAM_ORDER => $sorting->getActiveOrder()
            );
            foreach($hiddens as $k => $v) {
                $hiddens[$k] = Sitengine_Form_Element::getHidden($k, $v);
            }
            */
            return array(
                #'hiddens' => implode('', $hiddens),
                #'title' => $this->_controller->getDictionary()->getFromLabels('listformTitle'),
                #'URIS' => $uris,
                #'METHODS' => $methods,
                'FILTER' => $filterData,
                'SETTINGS' => $settingsData,
                'SORTING' => $sortingData,
                'DATA' => $list,
                'PAGER' => $pagerData
            );
        }
        catch (Exception $exception) {
			require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
			throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('list page error', $exception);
		}
    }
    
}


?>