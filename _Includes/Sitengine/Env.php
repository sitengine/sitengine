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


class Sitengine_Env
{
	
	private function __construct() {}
    private function __clone() {}
    
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    
    protected static $_instance = null;
    
    
    const PRODUCTION = 'production';
    const STAGING = 'staging';
    const DEVELOPMENT = 'development';
    
    
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_HEAD = 'HEAD';
    
	
    const PARAM_LOGINUSER = 'loginu';
    const PARAM_LOGINPASS = 'loginp';
    const PARAM_LOGOUT = 'logout';
    const PARAM_DBG = 'dbg';
    const PARAM_MODULE = 'm';
    #const PARAM_FRONTCONTROLLER = 'fc';
    const PARAM_CONTROLLER = 'c';
    const PARAM_ACTION = 'a';
    #const PARAM_GO2ACTION = 'g2a';
    const PARAM_REPRESENTATION = 'representation';
    const PARAM_METHOD = '_method';
    const PARAM_HANDLER = 'handler';
    const PARAM_TARGET = 'target';
    const PARAM_ID = 'id';
    #const PARAM_SLUG = 'slug';
    const PARAM_FILE = 'f';
    #const PARAM_INSERTID = 'iid';
    const PARAM_ANCESTORID = 'aid';
    const PARAM_GREATANCESTORID = 'gaid';
    const PARAM_PARENTID = 'pid';
    const PARAM_PAGE = 'p';
    const PARAM_SORT = 's';
    const PARAM_ORDER = 'o';
    const PARAM_LANGUAGE = 'l';
    const PARAM_TIMEZONE = 'tz';
    #const PARAM_ORG = 'org';
    const PARAM_MDATE = 'mdate';
    const PARAM_SESSIONID = 'ses';
    const PARAM_REMEMBERME = 'rememberMe';
    const PARAM_TRANSCRIPT = 't';
    const PARAM_PAYLOAD_NAME = 'pn';
    const PARAM_IPP = 'ipp';
    
    const LANGUAGE_EN = 'en';
    const LANGUAGE_FR = 'fr';
    const LANGUAGE_DE = 'de';
    const LANGUAGE_IT = 'it';
    const LANGUAGE_ES = 'es';
    
    const INPUTMODE_INSERT = 'insert';
    const INPUTMODE_UPDATE = 'update';
    
    const STATUS_BAD_REQUEST = 'statusBadRequest';
    const STATUS_NOT_FOUND = 'statusNotFound';
    const STATUS_FORBIDDEN = 'statusForbidden';
    const STATUS_METHOD_NOT_SUPPORTED = 'statusMethodNotSupported';
    const STATUS_INTERNAL_SERVER_ERROR = 'statusInternalServerError';
    const STATUS_NOT_IMPLEMENTED = 'statusNotImplemented';
    
    const STATUS_ERRORINPUT = 'statusErrorInput';
    const STATUS_OKINPUT = 'statusOkInput';
    const STATUS_ERRORIMPORT = 'statusErrorImport';
    const STATUS_OKIMPORT = 'statusOkImport';
    const STATUS_ERRORINSERT = 'statusErrorInsert';
    const STATUS_OKINSERT = 'statusOkInsert';
    const STATUS_ERRORUPDATE = 'statusErrorUpdate';
    const STATUS_OKUPDATE = 'statusOkUpdate';
    const STATUS_ERRORDELETE = 'statusErrorDelete';
    const STATUS_OKDELETE = 'statusOkDelete';
    const STATUS_ERRORUNLINK = 'statusErrorUnlink';
    const STATUS_OKUNLINK = 'statusOkUnlink';
    const STATUS_ERRORBATCHTRASH = 'statusErrorBatchDelete';
    const STATUS_OKBATCHTRASH = 'statusOkBatchDelete';
    const STATUS_ERRORBATCHUNLINK = 'statusErrorBatchUnlink';
    const STATUS_OKBATCHUNLINK = 'statusOkBatchUnlink';
    const STATUS_ERRORBATCHINSERT = 'statusErrorBatchInsert';
    const STATUS_OKBATCHINSERT = 'statusOkBatchInsert';
    const STATUS_ERRORBATCHUPDATE = 'statusErrorBatchUpdate';
    const STATUS_OKBATCHUPDATE = 'statusOkBatchUpdate';
    const STATUS_ERRORBATCHASSIGN = 'statusErrorBatchAssign';
    const STATUS_OKBATCHASSIGN = 'statusOkBatchAssign';
    const STATUS_UPLOAD_EXISTS = 'statusErrorUploadExists';
    const STATUS_UPLOAD_SIZEEXCEEDED = 'statusErrorUploadSizeExceeded';
    const STATUS_UPLOAD_INCOMPLETE = 'statusErrorUploadIncomplete';
    const STATUS_UPLOAD_NOFILE = 'statusErrorUploadNoFile';
    const STATUS_UPLOAD_ERROR = 'statusErrorUpload';
    
    const HINT_INVALID_ACTION = 'hintsInvalidAction';
    const HINT_DATA_EXPIRED = 'hintsDataExpired';
    
    
    const ERROR_BAD_REQUEST = 400;
    const ERROR_NOT_FOUND = 404;
    const ERROR_FORBIDDEN = 401;
    const ERROR_METHOD_NOT_SUPPORTED = 405;
    const ERROR_INTERNAL_SERVER_ERROR = 500;
    const ERROR_NOT_IMPLEMENTED = 501;
    
    
    
    
    protected $_type = null;
    
    public function setType($type)
    {
    	$this->_type = $type;
    	return $this;
    }
    
    
    public function getType()
    {
    	return $this->_type;
    }
    
    
    
    
	protected $_customConfigs = array();
	
	
	public function addCustomConfig($name, $config)
	{
		$this->_customConfigs[$name] = $config;
		return $this;
	}
	
	public function getCustomConfig($name)
	{
		if(isset($this->_customConfigs[$name])) {
			return $this->_customConfigs[$name];
		}
		require_once 'Sitengine/Exception.php';
		throw new Sitengine_Exception('custom config "'.$name.'" has not been set');
	}
	
	public function hasCustomConfig($name)
	{
		return isset($this->_customConfigs[$name]);
	}
	
	public function getCustomConfigs()
	{
		return $this->_customConfigs;
	}
	
	
	
	
	
	protected $_databaseConfigs = array();
	
	
	public function addDatabaseConfig($name, array $config)
	{
		$this->_databaseConfigs[$name] = $config;
		return $this;
	}
	
	public function getDatabaseConfig($name)
	{
		if(isset($this->_databaseConfigs[$name])) {
			return $this->_databaseConfigs[$name];
		}
		else {
			require_once 'Sitengine/Exception.php';
			throw new Sitengine_Exception('database config "'.$name.'" has not been set');
		}
	}
	
	
	
	
	protected $_ftpConfigs = array();
	
	
	public function addFtpConfig($name, array $config)
	{
		$this->_ftpConfigs[$name] = $config;
		return $this;
	}
	
	public function getFtpConfig($name)
	{
		if(isset($this->_ftpConfigs[$name])) {
			return $this->_ftpConfigs[$name];
		}
		else {
			require_once 'Sitengine/Exception.php';
			throw new Sitengine_Exception('ftp config "'.$name.'" has not been set');
		}
	}
	
	
	
	protected $_urls = array();
	
	
	public function addUrl($name, $url)
	{
		$this->_urls[$name] = $url;
		return $this;
	}
	
	
	public function setUrls(array $urls)
	{
		$this->_urls = $urls;
		return $this;
	}
	
	
	public function addUrls(array $urls)
	{
		$this->_urls = array_merge($this->_urls, $urls);
		return $this;
	}
	
	
	public function getUrl($name)
	{
		return (isset($this->_urls[$name])) ? $this->_urls[$name] : null;
	}
	
	
	public function getUrls()
	{
		return $this->_urls;
	}
	
	
	
	
	
	
	
	
	
	
	/*
	protected $_bootstraps = array();
	
	public function setBootstraps($bootstraps)
	{
		$this->_bootstraps = $bootstraps;
		return $this;
	}
	
	
	public function getBootstraps()
	{
		return $this->_bootstraps;
	}
	
	
	public function getBootstrap($name)
	{
		if(isset($this->_bootstraps[$name])) {
			return $this->_bootstraps[$name];
		}
		else {
			require_once 'Sitengine/Exception.php';
			throw new Sitengine_Exception('bootstrap "'.$name.'" has not been set');
		}
	}
	*/
	
	
	/*
	protected $_basepaths = array();
	
	public function setBasepaths($basepaths)
	{
		$this->_basepaths = $basepaths;
		return $this;
	}
	
	
	public function getBasepaths()
	{
		return $this->_basepaths;
	}
	
	
	public function getBasepath($name)
	{
		if(isset($this->_basepaths[$name])) {
			return $this->_basepaths[$name];
		}
		else {
			require_once 'Sitengine/Exception.php';
			throw new Sitengine_Exception('basepath "'.$name.'" has not been set');
		}
	}
    */
    
    
    
    
    
    protected $_debugControl = false;
    
    public function setDebugControl($debugControl)
    {
    	$this->_debugControl = $debugControl;
    	return $this;
    }
    
    
    public function getDebugControl()
    {
    	return $this->_debugControl;
    }
    
    
    
    
    protected $_enableCache = true;
    
    public function enableCache($enableCache)
    {
    	$this->_enableCache = $enableCache;
    	return $this;
    }
    
    
    public function isCacheEnabled()
    {
    	return $this->_enableCache;
    }
    
    
    
    
    protected $_cache = null;
    
    public function startCache($cache)
    {
    	$this->_cache = $cache;
    	return $this;
    }
    
    
    public function getCache()
    {
    	if($this->_cache === null)
		{
			require_once 'Sitengine/Env/Exception.php';
			throw new Sitengine_Env_Exception('startCache() must be called before using '.__METHOD__);
		}
    	return $this->_cache;
    }
    
    
    public function hasCache()
    {
    	return ($this->_cache !== null && $this->_cache instanceof Zend_Cache_Core);
    }
	
    
    
    
    /*
    protected $_uriSelfSubmit = '';
    
    public function setUriSelfSubmit($uriSelfSubmit)
    {
    	$this->_uriSelfSubmit = $uriSelfSubmit;
    	return $this;
    }
    
    
    public function getUriSelfSubmit()
    {
    	return $this->_uriSelfSubmit;
    }
    */
    
    public function getUriSelfSubmit()
    {
		return preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
    }
	
	
	
	
	protected $_logFilterPriority = null;
	
	public function setLogFilterPriority($logFilterPriority)
    {
    	$this->_logFilterPriority = $logFilterPriority;
    	return $this;
    }
    
    
    public function getLogFilterPriority()
    {
    	return $this->_logFilterPriority;
    }
    
	
	public function getData()
    {
    	$vars = array();
    	foreach(get_object_vars($this) as $key => $val) {
    		$key = preg_replace('/^_(\w*)$/', "$1", $key);
    		$vars[$key] = $val;
    	}
    	return $vars;
    }
    
	
	
	
	
	
	# sitengine dirs
	protected $_contribRequestDir = null;

    public function setContribRequestDir($dir)
    {
    	$this->_contribRequestDir = $dir;
    	return $this;
    }
    
    public function getContribRequestDir()
    {
    	return $this->_contribRequestDir;
    }
    
    
	protected $_mediaRequestDir = null;

    public function setMediaRequestDir($dir)
    {
    	$this->_mediaRequestDir = $dir;
    	return $this;
    }
    
    public function getMediaRequestDir()
    {
    	return $this->_mediaRequestDir;
    }
    
    
    protected $_scriptsRequestDir = null;

    public function setScriptsRequestDir($dir)
    {
    	$this->_scriptsRequestDir = $dir;
    	return $this;
    }
    
    public function getScriptsRequestDir()
    {
    	return $this->_scriptsRequestDir;
    }
    
    
	protected $_contribDir = null;

    public function setContribDir($dir)
    {
    	$this->_contribDir = $dir;
    	return $this;
    }
    
    public function getContribDir()
    {
    	return $this->_contribDir;
    }
    
    
	protected $_includesDir = null;

    public function setIncludesDir($dir)
    {
    	$this->_includesDir = $dir;
    	return $this;
    }
    
    public function getIncludesDir()
    {
    	return $this->_includesDir;
    }
    
    
	protected $_mediaDir = null;

    public function setMediaDir($dir)
    {
    	$this->_mediaDir = $dir;
    	return $this;
    }
    
    public function getMediaDir()
    {
    	return $this->_mediaDir;
    }
    
    
    
    
    
    
    
    
    
	
	# project dirs
	protected $_myProjectRequestDir = null;

    public function setMyProjectRequestDir($dir)
    {
    	$this->_myProjectRequestDir = $dir;
    	return $this;
    }
    
    public function getMyProjectRequestDir()
    {
    	return $this->_myProjectRequestDir;
    }
    
    
    protected $_myDigestsRequestDir = null;

    public function setMyDigestsRequestDir($dir)
    {
    	$this->_myDigestsRequestDir = $dir;
    	return $this;
    }
    
    public function getMyDigestsRequestDir()
    {
    	return $this->_myDigestsRequestDir;
    }
    
    
	protected $_myDataRequestDir = null;

    public function setMyDataRequestDir($dir)
    {
    	$this->_myDataRequestDir = $dir;
    	return $this;
    }
    
    public function getMyDataRequestDir()
    {
    	return $this->_myDataRequestDir;
    }
    
    
	protected $_myMediaRequestDir = null;

    public function setMyMediaRequestDir($dir)
    {
    	$this->_myMediaRequestDir = $dir;
    	return $this;
    }
    
    public function getMyMediaRequestDir()
    {
    	return $this->_myMediaRequestDir;
    }
    
    
	protected $_myContribRequestDir = null;

    public function setMyContribRequestDir($dir)
    {
    	$this->_myContribRequestDir = $dir;
    	return $this;
    }
    
    public function getMyContribRequestDir()
    {
    	return $this->_myContribRequestDir;
    }
    
    
	protected $_myProjectDir = null;

    public function setMyProjectDir($dir)
    {
    	$this->_myProjectDir = $dir;
    	return $this;
    }
    
    public function getMyProjectDir()
    {
    	return $this->_myProjectDir;
    }
    
    
    protected $_myDigestsDir = null;

    public function setMyDigestsDir($dir)
    {
    	$this->_myDigestsDir = $dir;
    	return $this;
    }
    
    public function getMyDigestsDir()
    {
    	return $this->_myDigestsDir;
    }
    
    
	protected $_myDataDir = null;

    public function setMyDataDir($dir)
    {
    	$this->_myDataDir = $dir;
    	return $this;
    }
    
    public function getMyDataDir()
    {
    	return $this->_myDataDir;
    }
    
    
	protected $_myIncludesDir = null;

    public function setMyIncludesDir($dir)
    {
    	$this->_myIncludesDir = $dir;
    	return $this;
    }
    
    public function getMyIncludesDir()
    {
    	return $this->_myIncludesDir;
    }
    
    
	protected $_myLogsDir = null;

    public function setMyLogsDir($dir)
    {
    	$this->_myLogsDir = $dir;
    	return $this;
    }
    
    public function getMyLogsDir()
    {
    	return $this->_myLogsDir;
    }
    
    
	protected $_myMediaDir = null;

    public function setMyMediaDir($dir)
    {
    	$this->_myMediaDir = $dir;
    	return $this;
    }
    
    public function getMyMediaDir()
    {
    	return $this->_myMediaDir;
    }
    
    
	protected $_myPrivateDir = null;

    public function setMyPrivateDir($dir)
    {
    	$this->_myPrivateDir = $dir;
    	return $this;
    }
    
    public function getMyPrivateDir()
    {
    	return $this->_myPrivateDir;
    }
    
    
	protected $_myContribDir = null;

    public function setMyContribDir($dir)
    {
    	$this->_myContribDir = $dir;
    	return $this;
    }
    
    public function getMyContribDir()
    {
    	return $this->_myContribDir;
    }
    
    
	protected $_myTempDir = null;

    public function setMyTempDir($dir)
    {
    	$this->_myTempDir = $dir;
    	return $this;
    }
    
    public function getMyTempDir()
    {
    	return $this->_myTempDir;
    }
    
    
	protected $_myIngestDir = null;

    public function setMyIngestDir($dir)
    {
    	$this->_myIngestDir = $dir;
    	return $this;
    }
    
    public function getMyIngestDir()
    {
    	return $this->_myIngestDir;
    }
    
    
	protected $_myIngestTestingDir = null;

    public function setMyIngestTestingDir($dir)
    {
    	$this->_myIngestTestingDir = $dir;
    	return $this;
    }
    
    public function getMyIngestTestingDir()
    {
    	return $this->_myIngestTestingDir;
    }
    
    
    
    protected $_myDropDir = null;

    public function setMyDropDir($dir)
    {
    	$this->_myDropDir = $dir;
    	return $this;
    }
    
    public function getMyDropDir()
    {
    	return $this->_myDropDir;
    }
    
    
    
    protected $_myExportDir = null;

    public function setMyExportDir($dir)
    {
    	$this->_myExportDir = $dir;
    	return $this;
    }
    
    public function getMyExportDir()
    {
    	return $this->_myExportDir;
    }
    
    
    
    public function getTimezones()
    {
    	return array(
			'Africa/Abidjan' => 'Africa/Abidjan',
			'Africa/Accra' => 'Africa/Accra',
			'Africa/Addis_Ababa' => 'Africa/Addis_Ababa',
			'Africa/Algiers' => 'Africa/Algiers',
			'Africa/Asmara' => 'Africa/Asmara',
			'Africa/Asmera' => 'Africa/Asmera',
			'Africa/Bamako' => 'Africa/Bamako',
			'Africa/Bangui' => 'Africa/Bangui',
			'Africa/Banjul' => 'Africa/Banjul',
			'Africa/Bissau' => 'Africa/Bissau',
			'Africa/Blantyre' => 'Africa/Blantyre',
			'Africa/Brazzaville' => 'Africa/Brazzaville',
			'Africa/Bujumbura' => 'Africa/Bujumbura',
			'Africa/Cairo' => 'Africa/Cairo',
			'Africa/Casablanca' => 'Africa/Casablanca',
			'Africa/Ceuta' => 'Africa/Ceuta',
			'Africa/Conakry' => 'Africa/Conakry',
			'Africa/Dakar' => 'Africa/Dakar',
			'Africa/Dar_es_Salaam' => 'Africa/Dar_es_Salaam',
			'Africa/Djibouti' => 'Africa/Djibouti',
			'Africa/Douala' => 'Africa/Douala',
			'Africa/El_Aaiun' => 'Africa/El_Aaiun',
			'Africa/Freetown' => 'Africa/Freetown',
			'Africa/Gaborone' => 'Africa/Gaborone',
			'Africa/Harare' => 'Africa/Harare',
			'Africa/Johannesburg' => 'Africa/Johannesburg',
			'Africa/Kampala' => 'Africa/Kampala',
			'Africa/Khartoum' => 'Africa/Khartoum',
			'Africa/Kigali' => 'Africa/Kigali',
			'Africa/Kinshasa' => 'Africa/Kinshasa',
			'Africa/Lagos' => 'Africa/Lagos',
			'Africa/Libreville' => 'Africa/Libreville',
			'Africa/Lome' => 'Africa/Lome',
			'Africa/Luanda' => 'Africa/Luanda',
			'Africa/Lubumbashi' => 'Africa/Lubumbashi',
			'Africa/Lusaka' => 'Africa/Lusaka',
			'Africa/Malabo' => 'Africa/Malabo',
			'Africa/Maputo' => 'Africa/Maputo',
			'Africa/Maseru' => 'Africa/Maseru',
			'Africa/Mbabane' => 'Africa/Mbabane',
			'Africa/Mogadishu' => 'Africa/Mogadishu',
			'Africa/Monrovia' => 'Africa/Monrovia',
			'Africa/Nairobi' => 'Africa/Nairobi',
			'Africa/Ndjamena' => 'Africa/Ndjamena',
			'Africa/Niamey' => 'Africa/Niamey',
			'Africa/Nouakchott' => 'Africa/Nouakchott',
			'Africa/Ouagadougou' => 'Africa/Ouagadougou',
			'Africa/Porto-Novo' => 'Africa/Porto-Novo',
			'Africa/Sao_Tome' => 'Africa/Sao_Tome',
			'Africa/Timbuktu' => 'Africa/Timbuktu',
			'Africa/Tripoli' => 'Africa/Tripoli',
			'Africa/Tunis' => 'Africa/Tunis',
			'Africa/Windhoek' => 'Africa/Windhoek',
			'America/Adak' => 'America/Adak',
			'America/Anchorage' => 'America/Anchorage',
			'America/Anguilla' => 'America/Anguilla',
			'America/Antigua' => 'America/Antigua',
			'America/Araguaina' => 'America/Araguaina',
			'America/Argentina/Buenos_Aires' => 'America/Argentina/Buenos_Aires',
			'America/Argentina/Catamarca' => 'America/Argentina/Catamarca',
			'America/Argentina/ComodRivadavia' => 'America/Argentina/ComodRivadavia',
			'America/Argentina/Cordoba' => 'America/Argentina/Cordoba',
			'America/Argentina/Jujuy' => 'America/Argentina/Jujuy',
			'America/Argentina/La_Rioja' => 'America/Argentina/La_Rioja',
			'America/Argentina/Mendoza' => 'America/Argentina/Mendoza',
			'America/Argentina/Rio_Gallegos' => 'America/Argentina/Rio_Gallegos',
			'America/Argentina/San_Juan' => 'America/Argentina/San_Juan',
			'America/Argentina/San_Luis' => 'America/Argentina/San_Luis',
			'America/Argentina/Tucuman' => 'America/Argentina/Tucuman',
			'America/Argentina/Ushuaia' => 'America/Argentina/Ushuaia',
			'America/Aruba' => 'America/Aruba',
			'America/Asuncion' => 'America/Asuncion',
			'America/Atikokan' => 'America/Atikokan',
			'America/Atka' => 'America/Atka',
			'America/Bahia' => 'America/Bahia',
			'America/Barbados' => 'America/Barbados',
			'America/Belem' => 'America/Belem',
			'America/Belize' => 'America/Belize',
			'America/Blanc-Sablon' => 'America/Blanc-Sablon',
			'America/Boa_Vista' => 'America/Boa_Vista',
			'America/Bogota' => 'America/Bogota',
			'America/Boise' => 'America/Boise',
			'America/Buenos_Aires' => 'America/Buenos_Aires',
			'America/Cambridge_Bay' => 'America/Cambridge_Bay',
			'America/Campo_Grande' => 'America/Campo_Grande',
			'America/Cancun' => 'America/Cancun',
			'America/Caracas' => 'America/Caracas',
			'America/Catamarca' => 'America/Catamarca',
			'America/Cayenne' => 'America/Cayenne',
			'America/Cayman' => 'America/Cayman',
			'America/Chicago' => 'America/Chicago',
			'America/Chihuahua' => 'America/Chihuahua',
			'America/Coral_Harbour' => 'America/Coral_Harbour',
			'America/Cordoba' => 'America/Cordoba',
			'America/Costa_Rica' => 'America/Costa_Rica',
			'America/Cuiaba' => 'America/Cuiaba',
			'America/Curacao' => 'America/Curacao',
			'America/Danmarkshavn' => 'America/Danmarkshavn',
			'America/Dawson' => 'America/Dawson',
			'America/Dawson_Creek' => 'America/Dawson_Creek',
			'America/Denver' => 'America/Denver',
			'America/Detroit' => 'America/Detroit',
			'America/Dominica' => 'America/Dominica',
			'America/Edmonton' => 'America/Edmonton',
			'America/Eirunepe' => 'America/Eirunepe',
			'America/El_Salvador' => 'America/El_Salvador',
			'America/Ensenada' => 'America/Ensenada',
			'America/Fort_Wayne' => 'America/Fort_Wayne',
			'America/Fortaleza' => 'America/Fortaleza',
			'America/Glace_Bay' => 'America/Glace_Bay',
			'America/Godthab' => 'America/Godthab',
			'America/Goose_Bay' => 'America/Goose_Bay',
			'America/Grand_Turk' => 'America/Grand_Turk',
			'America/Grenada' => 'America/Grenada',
			'America/Guadeloupe' => 'America/Guadeloupe',
			'America/Guatemala' => 'America/Guatemala',
			'America/Guayaquil' => 'America/Guayaquil',
			'America/Guyana' => 'America/Guyana',
			'America/Halifax' => 'America/Halifax',
			'America/Havana' => 'America/Havana',
			'America/Hermosillo' => 'America/Hermosillo',
			'America/Indiana/Indianapolis' => 'America/Indiana/Indianapolis',
			'America/Indiana/Knox' => 'America/Indiana/Knox',
			'America/Indiana/Marengo' => 'America/Indiana/Marengo',
			'America/Indiana/Petersburg' => 'America/Indiana/Petersburg',
			'America/Indiana/Tell_City' => 'America/Indiana/Tell_City',
			'America/Indiana/Vevay' => 'America/Indiana/Vevay',
			'America/Indiana/Vincennes' => 'America/Indiana/Vincennes',
			'America/Indiana/Winamac' => 'America/Indiana/Winamac',
			'America/Indianapolis' => 'America/Indianapolis',
			'America/Inuvik' => 'America/Inuvik',
			'America/Iqaluit' => 'America/Iqaluit',
			'America/Jamaica' => 'America/Jamaica',
			'America/Jujuy' => 'America/Jujuy',
			'America/Juneau' => 'America/Juneau',
			'America/Kentucky/Louisville' => 'America/Kentucky/Louisville',
			'America/Kentucky/Monticello' => 'America/Kentucky/Monticello',
			'America/Knox_IN' => 'America/Knox_IN',
			'America/La_Paz' => 'America/La_Paz',
			'America/Lima' => 'America/Lima',
			'America/Los_Angeles' => 'America/Los_Angeles',
			'America/Louisville' => 'America/Louisville',
			'America/Maceio' => 'America/Maceio',
			'America/Managua' => 'America/Managua',
			'America/Manaus' => 'America/Manaus',
			'America/Marigot' => 'America/Marigot',
			'America/Martinique' => 'America/Martinique',
			'America/Mazatlan' => 'America/Mazatlan',
			'America/Mendoza' => 'America/Mendoza',
			'America/Menominee' => 'America/Menominee',
			'America/Merida' => 'America/Merida',
			'America/Mexico_City' => 'America/Mexico_City',
			'America/Miquelon' => 'America/Miquelon',
			'America/Moncton' => 'America/Moncton',
			'America/Monterrey' => 'America/Monterrey',
			'America/Montevideo' => 'America/Montevideo',
			'America/Montreal' => 'America/Montreal',
			'America/Montserrat' => 'America/Montserrat',
			'America/Nassau' => 'America/Nassau',
			'America/New_York' => 'America/New_York',
			'America/Nipigon' => 'America/Nipigon',
			'America/Nome' => 'America/Nome',
			'America/Noronha' => 'America/Noronha',
			'America/North_Dakota/Center' => 'America/North_Dakota/Center',
			'America/North_Dakota/New_Salem' => 'America/North_Dakota/New_Salem',
			'America/Panama' => 'America/Panama',
			'America/Pangnirtung' => 'America/Pangnirtung',
			'America/Paramaribo' => 'America/Paramaribo',
			'America/Phoenix' => 'America/Phoenix',
			'America/Port-au-Prince' => 'America/Port-au-Prince',
			'America/Port_of_Spain' => 'America/Port_of_Spain',
			'America/Porto_Acre' => 'America/Porto_Acre',
			'America/Porto_Velho' => 'America/Porto_Velho',
			'America/Puerto_Rico' => 'America/Puerto_Rico',
			'America/Rainy_River' => 'America/Rainy_River',
			'America/Rankin_Inlet' => 'America/Rankin_Inlet',
			'America/Recife' => 'America/Recife',
			'America/Regina' => 'America/Regina',
			'America/Resolute' => 'America/Resolute',
			'America/Rio_Branco' => 'America/Rio_Branco',
			'America/Rosario' => 'America/Rosario',
			'America/Santiago' => 'America/Santiago',
			'America/Santo_Domingo' => 'America/Santo_Domingo',
			'America/Sao_Paulo' => 'America/Sao_Paulo',
			'America/Scoresbysund' => 'America/Scoresbysund',
			'America/Shiprock' => 'America/Shiprock',
			'America/St_Barthelemy' => 'America/St_Barthelemy',
			'America/St_Johns' => 'America/St_Johns',
			'America/St_Kitts' => 'America/St_Kitts',
			'America/St_Lucia' => 'America/St_Lucia',
			'America/St_Thomas' => 'America/St_Thomas',
			'America/St_Vincent' => 'America/St_Vincent',
			'America/Swift_Current' => 'America/Swift_Current',
			'America/Tegucigalpa' => 'America/Tegucigalpa',
			'America/Thule' => 'America/Thule',
			'America/Thunder_Bay' => 'America/Thunder_Bay',
			'America/Tijuana' => 'America/Tijuana',
			'America/Toronto' => 'America/Toronto',
			'America/Tortola' => 'America/Tortola',
			'America/Vancouver' => 'America/Vancouver',
			'America/Virgin' => 'America/Virgin',
			'America/Whitehorse' => 'America/Whitehorse',
			'America/Winnipeg' => 'America/Winnipeg',
			'America/Yakutat' => 'America/Yakutat',
			'America/Yellowknife' => 'America/Yellowknife',
			'Antarctica/Casey' => 'Antarctica/Casey',
			'Antarctica/Davis' => 'Antarctica/Davis',
			'Antarctica/DumontDUrville' => 'Antarctica/DumontDUrville',
			'Antarctica/Mawson' => 'Antarctica/Mawson',
			'Antarctica/McMurdo' => 'Antarctica/McMurdo',
			'Antarctica/Palmer' => 'Antarctica/Palmer',
			'Antarctica/Rothera' => 'Antarctica/Rothera',
			'Antarctica/South_Pole' => 'Antarctica/South_Pole',
			'Antarctica/Syowa' => 'Antarctica/Syowa',
			'Antarctica/Vostok' => 'Antarctica/Vostok',
			'Arctic/Longyearbyen' => 'Arctic/Longyearbyen',
			'Asia/Aden' => 'Asia/Aden',
			'Asia/Almaty' => 'Asia/Almaty',
			'Asia/Amman' => 'Asia/Amman',
			'Asia/Anadyr' => 'Asia/Anadyr',
			'Asia/Aqtau' => 'Asia/Aqtau',
			'Asia/Aqtobe' => 'Asia/Aqtobe',
			'Asia/Ashgabat' => 'Asia/Ashgabat',
			'Asia/Ashkhabad' => 'Asia/Ashkhabad',
			'Asia/Baghdad' => 'Asia/Baghdad',
			'Asia/Bahrain' => 'Asia/Bahrain',
			'Asia/Baku' => 'Asia/Baku',
			'Asia/Bangkok' => 'Asia/Bangkok',
			'Asia/Beirut' => 'Asia/Beirut',
			'Asia/Bishkek' => 'Asia/Bishkek',
			'Asia/Brunei' => 'Asia/Brunei',
			'Asia/Calcutta' => 'Asia/Calcutta',
			'Asia/Choibalsan' => 'Asia/Choibalsan',
			'Asia/Chongqing' => 'Asia/Chongqing',
			'Asia/Chungking' => 'Asia/Chungking',
			'Asia/Colombo' => 'Asia/Colombo',
			'Asia/Dacca' => 'Asia/Dacca',
			'Asia/Damascus' => 'Asia/Damascus',
			'Asia/Dhaka' => 'Asia/Dhaka',
			'Asia/Dili' => 'Asia/Dili',
			'Asia/Dubai' => 'Asia/Dubai',
			'Asia/Dushanbe' => 'Asia/Dushanbe',
			'Asia/Gaza' => 'Asia/Gaza',
			'Asia/Harbin' => 'Asia/Harbin',
			'Asia/Ho_Chi_Minh' => 'Asia/Ho_Chi_Minh',
			'Asia/Hong_Kong' => 'Asia/Hong_Kong',
			'Asia/Hovd' => 'Asia/Hovd',
			'Asia/Irkutsk' => 'Asia/Irkutsk',
			'Asia/Istanbul' => 'Asia/Istanbul',
			'Asia/Jakarta' => 'Asia/Jakarta',
			'Asia/Jayapura' => 'Asia/Jayapura',
			'Asia/Jerusalem' => 'Asia/Jerusalem',
			'Asia/Kabul' => 'Asia/Kabul',
			'Asia/Kamchatka' => 'Asia/Kamchatka',
			'Asia/Karachi' => 'Asia/Karachi',
			'Asia/Kashgar' => 'Asia/Kashgar',
			'Asia/Katmandu' => 'Asia/Katmandu',
			'Asia/Kolkata' => 'Asia/Kolkata',
			'Asia/Krasnoyarsk' => 'Asia/Krasnoyarsk',
			'Asia/Kuala_Lumpur' => 'Asia/Kuala_Lumpur',
			'Asia/Kuching' => 'Asia/Kuching',
			'Asia/Kuwait' => 'Asia/Kuwait',
			'Asia/Macao' => 'Asia/Macao',
			'Asia/Macau' => 'Asia/Macau',
			'Asia/Magadan' => 'Asia/Magadan',
			'Asia/Makassar' => 'Asia/Makassar',
			'Asia/Manila' => 'Asia/Manila',
			'Asia/Muscat' => 'Asia/Muscat',
			'Asia/Nicosia' => 'Asia/Nicosia',
			'Asia/Novosibirsk' => 'Asia/Novosibirsk',
			'Asia/Omsk' => 'Asia/Omsk',
			'Asia/Oral' => 'Asia/Oral',
			'Asia/Phnom_Penh' => 'Asia/Phnom_Penh',
			'Asia/Pontianak' => 'Asia/Pontianak',
			'Asia/Pyongyang' => 'Asia/Pyongyang',
			'Asia/Qatar' => 'Asia/Qatar',
			'Asia/Qyzylorda' => 'Asia/Qyzylorda',
			'Asia/Rangoon' => 'Asia/Rangoon',
			'Asia/Riyadh' => 'Asia/Riyadh',
			'Asia/Saigon' => 'Asia/Saigon',
			'Asia/Sakhalin' => 'Asia/Sakhalin',
			'Asia/Samarkand' => 'Asia/Samarkand',
			'Asia/Seoul' => 'Asia/Seoul',
			'Asia/Shanghai' => 'Asia/Shanghai',
			'Asia/Singapore' => 'Asia/Singapore',
			'Asia/Taipei' => 'Asia/Taipei',
			'Asia/Tashkent' => 'Asia/Tashkent',
			'Asia/Tbilisi' => 'Asia/Tbilisi',
			'Asia/Tehran' => 'Asia/Tehran',
			'Asia/Tel_Aviv' => 'Asia/Tel_Aviv',
			'Asia/Thimbu' => 'Asia/Thimbu',
			'Asia/Thimphu' => 'Asia/Thimphu',
			'Asia/Tokyo' => 'Asia/Tokyo',
			'Asia/Ujung_Pandang' => 'Asia/Ujung_Pandang',
			'Asia/Ulaanbaatar' => 'Asia/Ulaanbaatar',
			'Asia/Ulan_Bator' => 'Asia/Ulan_Bator',
			'Asia/Urumqi' => 'Asia/Urumqi',
			'Asia/Vientiane' => 'Asia/Vientiane',
			'Asia/Vladivostok' => 'Asia/Vladivostok',
			'Asia/Yakutsk' => 'Asia/Yakutsk',
			'Asia/Yekaterinburg' => 'Asia/Yekaterinburg',
			'Asia/Yerevan' => 'Asia/Yerevan',
			'Atlantic/Azores' => 'Atlantic/Azores',
			'Atlantic/Bermuda' => 'Atlantic/Bermuda',
			'Atlantic/Canary' => 'Atlantic/Canary',
			'Atlantic/Cape_Verde' => 'Atlantic/Cape_Verde',
			'Atlantic/Faeroe' => 'Atlantic/Faeroe',
			'Atlantic/Faroe' => 'Atlantic/Faroe',
			'Atlantic/Jan_Mayen' => 'Atlantic/Jan_Mayen',
			'Atlantic/Madeira' => 'Atlantic/Madeira',
			'Atlantic/Reykjavik' => 'Atlantic/Reykjavik',
			'Atlantic/South_Georgia' => 'Atlantic/South_Georgia',
			'Atlantic/St_Helena' => 'Atlantic/St_Helena',
			'Atlantic/Stanley' => 'Atlantic/Stanley',
			'Australia/ACT' => 'Australia/ACT',
			'Australia/Adelaide' => 'Australia/Adelaide',
			'Australia/Brisbane' => 'Australia/Brisbane',
			'Australia/Broken_Hill' => 'Australia/Broken_Hill',
			'Australia/Canberra' => 'Australia/Canberra',
			'Australia/Currie' => 'Australia/Currie',
			'Australia/Darwin' => 'Australia/Darwin',
			'Australia/Eucla' => 'Australia/Eucla',
			'Australia/Hobart' => 'Australia/Hobart',
			'Australia/LHI' => 'Australia/LHI',
			'Australia/Lindeman' => 'Australia/Lindeman',
			'Australia/Lord_Howe' => 'Australia/Lord_Howe',
			'Australia/Melbourne' => 'Australia/Melbourne',
			'Australia/North' => 'Australia/North',
			'Australia/NSW' => 'Australia/NSW',
			'Australia/Perth' => 'Australia/Perth',
			'Australia/Queensland' => 'Australia/Queensland',
			'Australia/South' => 'Australia/South',
			'Australia/Sydney' => 'Australia/Sydney',
			'Australia/Tasmania' => 'Australia/Tasmania',
			'Australia/Victoria' => 'Australia/Victoria',
			'Australia/West' => 'Australia/West',
			'Australia/Yancowinna' => 'Australia/Yancowinna',
			'Brazil/Acre' => 'Brazil/Acre',
			'Brazil/DeNoronha' => 'Brazil/DeNoronha',
			'Brazil/East' => 'Brazil/East',
			'Brazil/West' => 'Brazil/West',
			'Canada/Atlantic' => 'Canada/Atlantic',
			'Canada/Central' => 'Canada/Central',
			'Canada/East-Saskatchewan' => 'Canada/East-Saskatchewan',
			'Canada/Eastern' => 'Canada/Eastern',
			'Canada/Mountain' => 'Canada/Mountain',
			'Canada/Newfoundland' => 'Canada/Newfoundland',
			'Canada/Pacific' => 'Canada/Pacific',
			'Canada/Saskatchewan' => 'Canada/Saskatchewan',
			'Canada/Yukon' => 'Canada/Yukon',
			#'CET' => 'CET',
			'Chile/Continental' => 'Chile/Continental',
			'Chile/EasterIsland' => 'Chile/EasterIsland',
			#'CST6CDT' => 'CST6CDT',
			'Cuba' => 'Cuba',
			#'EET' => 'EET',
			'Egypt' => 'Egypt',
			'Eire' => 'Eire',
			/*
			'EST' => 'EST',
			'EST5EDT' => 'EST5EDT',
			'Etc/GMT' => 'Etc/GMT',
			'Etc/GMT+0' => 'Etc/GMT+0',
			'Etc/GMT+1' => 'Etc/GMT+1',
			'Etc/GMT+10' => 'Etc/GMT+10',
			'Etc/GMT+11' => 'Etc/GMT+11',
			'Etc/GMT+12' => 'Etc/GMT+12',
			'Etc/GMT+2' => 'Etc/GMT+2',
			'Etc/GMT+3' => 'Etc/GMT+3',
			'Etc/GMT+4' => 'Etc/GMT+4',
			'Etc/GMT+5' => 'Etc/GMT+5',
			'Etc/GMT+6' => 'Etc/GMT+6',
			'Etc/GMT+7' => 'Etc/GMT+7',
			'Etc/GMT+8' => 'Etc/GMT+8',
			'Etc/GMT+9' => 'Etc/GMT+9',
			'Etc/GMT-0' => 'Etc/GMT-0',
			'Etc/GMT-1' => 'Etc/GMT-1',
			'Etc/GMT-10' => 'Etc/GMT-10',
			'Etc/GMT-11' => 'Etc/GMT-11',
			'Etc/GMT-12' => 'Etc/GMT-12',
			'Etc/GMT-13' => 'Etc/GMT-13',
			'Etc/GMT-14' => 'Etc/GMT-14',
			'Etc/GMT-2' => 'Etc/GMT-2',
			'Etc/GMT-3' => 'Etc/GMT-3',
			'Etc/GMT-4' => 'Etc/GMT-4',
			'Etc/GMT-5' => 'Etc/GMT-5',
			'Etc/GMT-6' => 'Etc/GMT-6',
			'Etc/GMT-7' => 'Etc/GMT-7',
			'Etc/GMT-8' => 'Etc/GMT-8',
			'Etc/GMT-9' => 'Etc/GMT-9',
			'Etc/GMT0' => 'Etc/GMT0',
			'Etc/Greenwich' => 'Etc/Greenwich',
			'Etc/UCT' => 'Etc/UCT',
			'Etc/Universal' => 'Etc/Universal',
			'Etc/UTC' => 'Etc/UTC',
			'Etc/Zulu' => 'Etc/Zulu',
			*/
			'Europe/Amsterdam' => 'Europe/Amsterdam',
			'Europe/Andorra' => 'Europe/Andorra',
			'Europe/Athens' => 'Europe/Athens',
			'Europe/Belfast' => 'Europe/Belfast',
			'Europe/Belgrade' => 'Europe/Belgrade',
			'Europe/Berlin' => 'Europe/Berlin',
			'Europe/Bratislava' => 'Europe/Bratislava',
			'Europe/Brussels' => 'Europe/Brussels',
			'Europe/Bucharest' => 'Europe/Bucharest',
			'Europe/Budapest' => 'Europe/Budapest',
			'Europe/Chisinau' => 'Europe/Chisinau',
			'Europe/Copenhagen' => 'Europe/Copenhagen',
			'Europe/Dublin' => 'Europe/Dublin',
			'Europe/Gibraltar' => 'Europe/Gibraltar',
			'Europe/Guernsey' => 'Europe/Guernsey',
			'Europe/Helsinki' => 'Europe/Helsinki',
			'Europe/Isle_of_Man' => 'Europe/Isle_of_Man',
			'Europe/Istanbul' => 'Europe/Istanbul',
			'Europe/Jersey' => 'Europe/Jersey',
			'Europe/Kaliningrad' => 'Europe/Kaliningrad',
			'Europe/Kiev' => 'Europe/Kiev',
			'Europe/Lisbon' => 'Europe/Lisbon',
			'Europe/Ljubljana' => 'Europe/Ljubljana',
			'Europe/London' => 'Europe/London',
			'Europe/Luxembourg' => 'Europe/Luxembourg',
			'Europe/Madrid' => 'Europe/Madrid',
			'Europe/Malta' => 'Europe/Malta',
			'Europe/Mariehamn' => 'Europe/Mariehamn',
			'Europe/Minsk' => 'Europe/Minsk',
			'Europe/Monaco' => 'Europe/Monaco',
			'Europe/Moscow' => 'Europe/Moscow',
			'Europe/Nicosia' => 'Europe/Nicosia',
			'Europe/Oslo' => 'Europe/Oslo',
			'Europe/Paris' => 'Europe/Paris',
			'Europe/Podgorica' => 'Europe/Podgorica',
			'Europe/Prague' => 'Europe/Prague',
			'Europe/Riga' => 'Europe/Riga',
			'Europe/Rome' => 'Europe/Rome',
			'Europe/Samara' => 'Europe/Samara',
			'Europe/San_Marino' => 'Europe/San_Marino',
			'Europe/Sarajevo' => 'Europe/Sarajevo',
			'Europe/Simferopol' => 'Europe/Simferopol',
			'Europe/Skopje' => 'Europe/Skopje',
			'Europe/Sofia' => 'Europe/Sofia',
			'Europe/Stockholm' => 'Europe/Stockholm',
			'Europe/Tallinn' => 'Europe/Tallinn',
			'Europe/Tirane' => 'Europe/Tirane',
			'Europe/Tiraspol' => 'Europe/Tiraspol',
			'Europe/Uzhgorod' => 'Europe/Uzhgorod',
			'Europe/Vaduz' => 'Europe/Vaduz',
			'Europe/Vatican' => 'Europe/Vatican',
			'Europe/Vienna' => 'Europe/Vienna',
			'Europe/Vilnius' => 'Europe/Vilnius',
			'Europe/Volgograd' => 'Europe/Volgograd',
			'Europe/Warsaw' => 'Europe/Warsaw',
			'Europe/Zagreb' => 'Europe/Zagreb',
			'Europe/Zaporozhye' => 'Europe/Zaporozhye',
			'Europe/Zurich' => 'Europe/Zurich',
			/*
			#'Factory' => 'Factory',
			'GB' => 'GB',
			'GB-Eire' => 'GB-Eire',
			'GMT' => 'GMT',
			'GMT+0' => 'GMT+0',
			'GMT-0' => 'GMT-0',
			'GMT0' => 'GMT0',
			*/
			'Greenwich' => 'Greenwich',
			'Hongkong' => 'Hongkong',
			#'HST' => 'HST',
			'Iceland' => 'Iceland',
			'Indian/Antananarivo' => 'Indian/Antananarivo',
			'Indian/Chagos' => 'Indian/Chagos',
			'Indian/Christmas' => 'Indian/Christmas',
			'Indian/Cocos' => 'Indian/Cocos',
			'Indian/Comoro' => 'Indian/Comoro',
			'Indian/Kerguelen' => 'Indian/Kerguelen',
			'Indian/Mahe' => 'Indian/Mahe',
			'Indian/Maldives' => 'Indian/Maldives',
			'Indian/Mauritius' => 'Indian/Mauritius',
			'Indian/Mayotte' => 'Indian/Mayotte',
			'Indian/Reunion' => 'Indian/Reunion',
			'Iran' => 'Iran',
			'Israel' => 'Israel',
			'Jamaica' => 'Jamaica',
			'Japan' => 'Japan',
			'Kwajalein' => 'Kwajalein',
			'Libya' => 'Libya',
			#'MET' => 'MET',
			'Mexico/BajaNorte' => 'Mexico/BajaNorte',
			'Mexico/BajaSur' => 'Mexico/BajaSur',
			'Mexico/General' => 'Mexico/General',
			#'MST' => 'MST',
			#'MST7MDT' => 'MST7MDT',
			'Navajo' => 'Navajo',
			#'NZ' => 'NZ',
			#'NZ-CHAT' => 'NZ-CHAT',
			'Pacific/Apia' => 'Pacific/Apia',
			'Pacific/Auckland' => 'Pacific/Auckland',
			'Pacific/Chatham' => 'Pacific/Chatham',
			'Pacific/Easter' => 'Pacific/Easter',
			'Pacific/Efate' => 'Pacific/Efate',
			'Pacific/Enderbury' => 'Pacific/Enderbury',
			'Pacific/Fakaofo' => 'Pacific/Fakaofo',
			'Pacific/Fiji' => 'Pacific/Fiji',
			'Pacific/Funafuti' => 'Pacific/Funafuti',
			'Pacific/Galapagos' => 'Pacific/Galapagos',
			'Pacific/Gambier' => 'Pacific/Gambier',
			'Pacific/Guadalcanal' => 'Pacific/Guadalcanal',
			'Pacific/Guam' => 'Pacific/Guam',
			'Pacific/Honolulu' => 'Pacific/Honolulu',
			'Pacific/Johnston' => 'Pacific/Johnston',
			'Pacific/Kiritimati' => 'Pacific/Kiritimati',
			'Pacific/Kosrae' => 'Pacific/Kosrae',
			'Pacific/Kwajalein' => 'Pacific/Kwajalein',
			'Pacific/Majuro' => 'Pacific/Majuro',
			'Pacific/Marquesas' => 'Pacific/Marquesas',
			'Pacific/Midway' => 'Pacific/Midway',
			'Pacific/Nauru' => 'Pacific/Nauru',
			'Pacific/Niue' => 'Pacific/Niue',
			'Pacific/Norfolk' => 'Pacific/Norfolk',
			'Pacific/Noumea' => 'Pacific/Noumea',
			'Pacific/Pago_Pago' => 'Pacific/Pago_Pago',
			'Pacific/Palau' => 'Pacific/Palau',
			'Pacific/Pitcairn' => 'Pacific/Pitcairn',
			'Pacific/Ponape' => 'Pacific/Ponape',
			'Pacific/Port_Moresby' => 'Pacific/Port_Moresby',
			'Pacific/Rarotonga' => 'Pacific/Rarotonga',
			'Pacific/Saipan' => 'Pacific/Saipan',
			'Pacific/Samoa' => 'Pacific/Samoa',
			'Pacific/Tahiti' => 'Pacific/Tahiti',
			'Pacific/Tarawa' => 'Pacific/Tarawa',
			'Pacific/Tongatapu' => 'Pacific/Tongatapu',
			'Pacific/Truk' => 'Pacific/Truk',
			'Pacific/Wake' => 'Pacific/Wake',
			'Pacific/Wallis' => 'Pacific/Wallis',
			'Pacific/Yap' => 'Pacific/Yap',
			'Poland' => 'Poland',
			'Portugal' => 'Portugal',
			#'PRC' => 'PRC',
			#'PST8PDT' => 'PST8PDT',
			#'ROC' => 'ROC',
			#'ROK' => 'ROK',
			'Singapore' => 'Singapore',
			'Turkey' => 'Turkey',
			#'UCT' => 'UCT',
			#'Universal' => 'Universal',
			'US/Alaska' => 'US/Alaska',
			'US/Aleutian' => 'US/Aleutian',
			'US/Arizona' => 'US/Arizona',
			'US/Central' => 'US/Central',
			'US/East-Indiana' => 'US/East-Indiana',
			'US/Eastern' => 'US/Eastern',
			'US/Hawaii' => 'US/Hawaii',
			'US/Indiana-Starke' => 'US/Indiana-Starke',
			'US/Michigan' => 'US/Michigan',
			'US/Mountain' => 'US/Mountain',
			'US/Pacific' => 'US/Pacific',
			'US/Pacific-New' => 'US/Pacific-New',
			'US/Samoa' => 'US/Samoa',
			'UTC' => 'UTC',
			#'W-SU' => 'W-SU',
			#'WET' => 'WET',
			#'Zulu' => 'Zulu'
		);
    }
    
    
    protected $_headerBrand = 'Sitengine';
    
    public function setHeaderBrand($headerBrand)
    {
    	$this->_headerBrand = $headerBrand;
    	return $this;
    }
    
    
    public function getHeaderBrand()
    {
    	return $this->_headerBrand;
    }
    
    
    protected $_headerBrandUrl = 'sitengine.org';
    
    public function setHeaderBrandUrl($headerBrandUrl)
    {
    	$this->_headerBrandUrl = $headerBrandUrl;
    	return $this;
    }
    
    
    public function getHeaderBrandUrl()
    {
    	return $this->_headerBrandUrl;
    }
    
    
    protected $_poweredBy = 'Powered By Sitengine';
    
    public function setPoweredBy($poweredBy)
    {
    	$this->_poweredBy = $poweredBy;
    	return $this;
    }
    
    
    public function getPoweredBy()
    {
    	return $this->_poweredBy;
    }
    
    
    protected $_poweredByUrl = 'http://sitengine.org';
    
    public function setPoweredByUrl($poweredByUrl)
    {
    	$this->_poweredByUrl = $poweredByUrl;
    	return $this;
    }
    
    
    public function getPoweredByUrl()
    {
    	return $this->_poweredByUrl;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    protected $_administratorMails = array();
	
	public function setAdministratorMails($administratorMails)
	{
		$this->_administratorMails = $administratorMails;
		return $this;
	}
	
	
	public function getAdministratorMails()
	{
		return $this->_administratorMails;
	}
	
	
	public function getAdministratorMail($name)
	{
		if(isset($this->_administratorMails[$name])) {
			return $this->_administratorMails[$name];
		}
		else {
			require_once 'Sitengine/Exception.php';
			throw new Sitengine_Exception('administrator mail "'.$name.'" has not been set');
		}
	}
	
	
	
	
	
	
	
    protected $_contactMails = array();
	
	public function setContactMails($contactMails)
	{
		$this->_contactMails = $contactMails;
		return $this;
	}
	
	
	public function getContactMails()
	{
		return $this->_contactMails;
	}
	
	
	public function getContactMail($name)
	{
		if(isset($this->_contactMails[$name])) {
			return $this->_contactMails[$name];
		}
		else {
			require_once 'Sitengine/Exception.php';
			throw new Sitengine_Exception('contact mail "'.$name.'" has not been set');
		}
	}
	
	
	
	
	
	
	
	
	protected $_moderatorMails = array();
	
	public function setModeratorMails($moderatorMails)
	{
		$this->_moderatorMails = $moderatorMails;
		return $this;
	}
	
	
	public function getModeratorMails()
	{
		return $this->_moderatorMails;
	}
	
	
	public function getModeratorMail($name)
	{
		if(isset($this->_moderatorMails[$name])) {
			return $this->_moderatorMails[$name];
		}
		else {
			require_once 'Sitengine/Exception.php';
			throw new Sitengine_Exception('moderator mail "'.$name.'" has not been set');
		}
	}
	
	
	
	
	
	
	
	
	
	protected $_administratorSenderMail = null;

    public function setAdministratorSenderMail($dir)
    {
    	$this->_administratorSenderMail = $dir;
    	return $this;
    }
    
    public function getAdministratorSenderMail()
    {
    	return $this->_administratorSenderMail;
    }
    
    
    
    
    
    
    protected $_contactSenderMail = null;

    public function setContactSenderMail($dir)
    {
    	$this->_contactSenderMail = $dir;
    	return $this;
    }
    
    public function getContactSenderMail()
    {
    	return $this->_contactSenderMail;
    }
    
    
    
    
    
    protected $_moderatorSenderMail = null;

    public function setModeratorSenderMail($dir)
    {
    	$this->_moderatorSenderMail = $dir;
    	return $this;
    }
    
    public function getModeratorSenderMail()
    {
    	return $this->_moderatorSenderMail;
    }
    
    
    
    public function __call($method, array $args)
    {
        require_once 'Kompakt/Shop/Exception.php';
    	throw new Kompakt_Shop_Exception("Unrecognized method: ".__METHOD__);
    }
    
    
    
    protected $_bootstrap = null;

    public function setBootstrap($bootstrap)
    {
    	$this->_bootstrap = $bootstrap;
    	return $this;
    }
    
    public function getBootstrap()
    {
    	return $this->_bootstrap;
    }
    
    
    protected $_config = null;

    public function setConfig($config)
    {
    	$this->_config = $config;
    	return $this;
    }
    
    public function getConfig()
    {
    	return $this->_config;
    }
    
    
    
    
}

?>