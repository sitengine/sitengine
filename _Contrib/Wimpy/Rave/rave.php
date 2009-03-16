<?php
if(@ini_get('safe_mode')){
	print ('<h2><b>ERROR</b></h2>');
	print ('<br><br>');
	print ('PHP is running in <b>"Safe Mode"</b> which prevents');
	print ('Wimpy from performing necessary functions such as');
	print ('automatically reading the contents of a directory."');
	print ('<br><br>');
	print ('Check with your hosting provider to see if they can');
	print ('turn off <b>"Safe Mode"</b> for your account."');
}
if(!@session_id()){
	@session_start();
}
//print ("<pre>");
//print_r ($_SERVER);
//print ("</pre>");
//<//////////////////////////////////////////////////////////////
//                                                             //
//                                                             //
//                                                             //
//                                                             //
//                        Wimpy Rave                           //
//                          v 1.0                              //
//                                                             //
//           by Mike Gieson <info@wimpyplayer.com>             //
//          available at http://www.wimpyplayer.com            //
//                     2002-2006 plaino                       //
//                                                             //
//                                                             //
//                                                             //
/////////////////////////////////////////////////////////////////
//                                                             //
//                       INSTALLATION:                         //
//                                                             //
/////////////////////////////////////////////////////////////////
// 
// Upload rave.php and rave.swf to the folder that 
// contains your mp3's.
// 
// USE AT YOUR OWN RISK.
//
$wimpyVersion = "3.0.101";
$wimpyConfigFile = "raveConfigs.xml";
$wimpyJavascriptFile = "rave.js";
$wimpySwf = "rave.swf";
$wimpy_auth = "wimpy_auth.php";
$media_types = "flv,mp4,3gp,m4a,m4a,m4p,aac,mp3,swf,xml,m3u,pls";
$hide_keywords = "skin,wimpy,config,customizer,source,plugin";
//
// findAllMedia / findAllMediaBlock
// To enable "findAllMedia" this variable needs to be set to "false"
// Example:
// $findAllMediaBlock = false;
$findAllMediaBlock = true;
//
// startDir / startDirBlock
// To enable "startDir" this variable needs to be set to "false"
// Example:
// $startDirBlock = false;
$startDirBlock = true;
//
// httpOption
// Allows you to run wimpy in "https" mode;
//$httpOption = "https";
$httpOption = "http";
//
// blockPHPinfo
// Setting this value to 'Yes' will prevent anyone 
// to view your phpinfo() page by adding the correct ?request to the URL.
// viewing the PHP info is only used for troubleshooting first installs.
$blockPHPinfo = "no";
//
/////////////////////////////////////////////////////////////////
//                                                             //
//         Do not edit anything below here unless              //
//          you really know what you are doing!                //
//                                                             //
/////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////
//
//>
define("newline", "\r\n");
define("slash", "/");
$flashVersion = "8";
$default_width = "250";
$default_height = "290";
//
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
//
//              security and request clean
//
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////


// prevent certain extensions from being downloaded
function secureFiles($fileName, $extCheck){
	$ext = explode('.',$fileName);
	$myExt = strtolower($ext[sizeof($ext)-1]);
	if(strtolower($myExt) != strtolower($extCheck)){
		return false;
	} else {
		return true;
	}
	if ((!ereg('\.\.', $fileName)) && (file_exists($fileName))) {
		return true;
	} else {
		return false;
	}
}


// strip hacker requests
function striphack($string){
	$retval = $string;
	//*
	$retval = strip_tags(stripslashes(rawurldecode(utf8_decode($retval))));
	$retval = strip_tags($retval);
	$retval = str_replace("sscanf", "x", $retval);
	$retval = str_replace("base64_decode", "x", $retval);
	$retval = str_replace("rawurldecode", "x", $retval);
	$retval = str_replace("urldecode", "x", $retval);
	$retval = str_replace("0;", "x", $retval);
	$retval = str_replace("%5C", "x", $retval);
	$retval = str_replace("\n", "x", $retval);
	$retval = str_replace("\r", "x", $retval);
	$retval = str_replace("\t", "x", $retval);
	$retval = str_replace("\\", "x", $retval);
	$retval = ereg_replace("\.+/", "x", $retval); 
	$retval = ereg_replace("\.\.","x",$retval);
	$retval = ereg_replace("^[\/]+", "x", $retval);
	//*/
	return $retval;
}


function secureArray($array_in){
	if(@is_array($array_in)){
		foreach ($array_in as $key => $value){
			$Atemp[striphack($key)] = striphack($value);
		}
	} else {
		$Atemp = $array_in;
	}
	return $array_in;
}


if ( @get_magic_quotes_gpc () ){
   function traverse ( &$arr ){
       if ( !is_array ( $arr ) ){
           return;
	   }
       foreach ( $arr as $key => $val ){
           is_array ( $arr[$key] ) ? traverse ( $arr[$key] ) : ( $arr[$key] = stripslashes ( $arr[$key] ) );
	   }
   }
   $gpc = array ( &$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
   traverse ( $gpc );
}


$_REQUEST = secureArray($_REQUEST);
$_GET = secureArray($_GET);
$_POST = secureArray($_POST);
$_COOKIE = secureArray($_COOKIE);

// these get set after default settings below because getid3 and coverartbasename should be over-written by request
//
// see: (below)
/*
foreach($_REQUEST as $key => $value){
	if(in_array($key, $AcheckRequests)){
		$$key = $value;
	} else {
		unset($$_REQUEST[$key]);
	}
}
*/
$AcheckRequests = array();
$AcheckRequests[] = "action";
$AcheckRequests[] = "theFile";
$AcheckRequests[] = "theKind";
$AcheckRequests[] = "dir";
$AcheckRequests[] = "getMyid3info";
$AcheckRequests[] = "findAllMedia";
$AcheckRequests[] = "coverartBasename";
$AcheckRequests[] = "s";

if(is_file($wimpy_auth)){
	$useAuth = TRUE;
	require ($wimpy_auth);
}


/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
//
//                     FILE PATHS
//
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////


//strstr( PHP_OS, "WIN") ? $osType = "win" : $osType = "unix";

if(!@getcwd ()){
	$WIMPY_PATH['physical'] = str_replace("\\", "/", dirname(__FILE__));
} else {
	$WIMPY_PATH['physical'] = str_replace("\\", "/", getcwd ());
}


if(!isset($_SERVER['SCRIPT_NAME'])){
	$_REQUEST = get_defined_vars();
	$_SERVER = $HTTP_SERVER_VARS;
}




$Apathtome = explode("/", $_SERVER['PHP_SELF']);
$wimpyPhp = array_pop($Apathtome);
//$pathtome = implode("/", $Apathtome);
$WIMPY_PATH['www'] = $httpOption."://".$_SERVER['HTTP_HOST'].(implode("/", $Apathtome));

/*
print ("<pre>");
print_r ($Apathtome);
print ("</pre>");
print ("<br>");
print $wimpyPhp;
print ("<br>");
exit;
*/

$wimpySwf = $WIMPY_PATH['www']."/".$wimpySwf;
$wimpyApp = $WIMPY_PATH['www']."/".$wimpyPhp;


/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
//
//                DEFAULT SETTINGS / SET CONFIGS
//
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////

$tptBkgd = "no";
$bkgdColor = "000000";
$hide_folders = "_notes,skins,getid3,_private,_private,_vti_bin,_vti_cnf,_vti_pvt,_vti_txt,cgi-bin";
$hide_files = "skin.xml,wimpyConfigs.xml,raveConfigs.xml,wimpy.swf,rave.swf,wimpyAV.swf,wasp.swf,wimpy_button.swf";
$wimpyHTMLpageTitle = "Wimpy Player";
$displayWidth = 0;
$displayHeight = 0;
$wimpySkin = "";
$coverartBasename = "coverart.jpg";
$getMyid3info = "no";
$findAllMedia = "no";


foreach($_REQUEST as $key => $value){
	if(in_array($key, $AcheckRequests)){
		$$key = $value;
	} else {
		unset($$_REQUEST[$key]);
	}
}



// Vars that should not be over-written by an external wimpyConfigs.xml file:
$hiderVars = Array("hide_folders", "hide_files");

$AdefaultVisual = explode(".", $coverartBasename);
$defaultVisualBaseName = $AdefaultVisual[0];
$defaultVisualExt = $AdefaultVisual[1];

function setCoverartBasenameData(){
	global $AdefaultVisual, $defaultVisualBaseName, $defaultVisualExt, $coverartBasename;
	$AdefaultVisual = explode(".", $coverartBasename);
	$defaultVisualBaseName = $AdefaultVisual[0];
	$defaultVisualExt = $AdefaultVisual[1];
}


function file_get_as_string($file){
	$file = @file("$file");
	return !$file ? false : implode('', $file);
}

if($data = file_get_as_string($wimpyConfigFile)){
	$xml_config_parser = @xml_parser_create('');
	@xml_parser_set_option ($xml_config_parser, XML_OPTION_CASE_FOLDING, false );
	@xml_parse_into_struct($xml_config_parser, $data, $vals, $index);
	@xml_parser_free($xml_config_parser);
	foreach ($vals as $k=>$v){
		if($v['type'] == "complete"){
			$myVar = $v['tag'];
			$myVal = trim(@$v['value']);
			if(@$myVal && @$myVal != ""){
				if(in_array($myVar, $hiderVars)){
					$myVal = $myVal.",".$$myVar;
				}
				$$myVar = trim(@$myVal);
			}
		}
	}
	$useConfigFile = true;
} else {
	$useConfigFile = false;
}

// Thse vars need to be set after reading the config file.
if(isset($coverartBasename) && $coverartBasename != ""){
	setCoverartBasenameData();
}
if($findAllMediaBlock){
	$findAllMedia = "no";
}
if($startDirBlock){
	$starDir = "";
}


// Only get Skin Info if needed
if(!isset($_REQUEST['action'])){
	$useSkin = true;
	if(strlen(@$wimpySkin)>4){
		if($data = file_get_as_string($wimpySkin)){
			$xml_parser = @xml_parser_create('');
			@xml_parse_into_struct($xml_parser, $data, $vals, $index);
			@xml_parser_free($xml_parser);
			$displayWidth = @$vals[@$index['BKGD_MAIN'][0]]['attributes']['WIDTH'];
			$displayHeight = @$vals[@$index['BKGD_MAIN'][0]]['attributes']['HEIGHT'];
		} else {
			$useSkin = false;
		}
	} else {
		$useSkin = false;
	}
	if($displayWidth<1 || $displayHeight<1){
		$useSkin = false;
	}
}


if($displayWidth<1 || $displayHeight<1){
	$useSkin = false;
	$displayWidth = $default_width;
	$displayHeight = $default_height;
}

if($bkgdColor == ""){
	$bkgdColor = "000000";
}


if($tptBkgd == "yes"){
	$useTptBkgd = true;
} else {
	$useTptBkgd = false;
}


$WIMPY_PATH['physical'] = str_replace("\\", "/", $WIMPY_PATH['physical']);
$WIMPY_PATH['physical'] = str_replace("//", "/", $WIMPY_PATH['physical']);


/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
//
//                DIRECTORY READING
//
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////

$Ahide_files = explode(",",$hide_files);
$Ahide_folders = explode(",",$hide_folders);
$Amedia_types = explode(",",$media_types);
$Akeywords = explode(",",$hide_keywords);


function checkKeyWords($Ahaystack, $needle){
	foreach($Ahaystack as $value){
		if(@stristr(strtolower($needle), strtolower($value))){
			return true;
		}
	}
	return false;
}

function GetDirArrayRecursive($dir, $filter=false) {
	global $Amedia_types, $Ahide_files, $Ahide_folders, $Akeywords;
	if($filter){
		$Afilter = $filter;
	} else {
		$Afilter = $Amedia_types;
	}
	$Afiles = array ();
	if(!$dir || $dir == ""){
		$dir = ".";
	}
	$handle=@opendir($dir);
	//$d = dir($dir);
	if($dir){
		while (false !== ($entry = @readdir($handle))){
		//while (false !== ($entry = $d->read())) {
			if($entry!='.' && $entry!='..') {
				$entry = $dir.'/'.str_replace("\\", "/", $entry);
				$pathinfo = path_parts($entry);
				if(is_dir($entry)) {
					if(!checkKeyWords($Ahide_folders, $pathinfo['filename']) && !checkKeyWords($Akeywords, $pathinfo['filename'])){
						$Afiles = array_merge($Afiles, GetDirArrayRecursive($entry, $Afilter));
					}
				} else {
					if(in_array(strtolower($pathinfo['ext']), $Afilter)){
						if(!checkKeyWords($Ahide_files, $pathinfo['filename']) && !checkKeyWords($Akeywords, $pathinfo['filename'])){
							$Afiles[] = $entry;
						}
					}
				}
			}
		}
		@closedir($handle);
		//$d->close();
	}
	return $Afiles;
}


function GetDirArray($dir){
	global $Amedia_types, $Ahide_files, $Ahide_folders, $Akeywords;
	$Asee = array();
	$Aitems = array ();
	$Adirs = array ();
	$Afiles = array ();
	$handle=@opendir($dir);
	//$d = dir(utf8_encode($dir));
	if($dir){
		while (false !== ($entry = @readdir($handle))){
		//while (false !== ($entry = $d->read())) {
			if($entry != '.' && $entry != '..' && substr ($entry, 0, 1 ) != "..") {
				$entry = $dir.'/'.str_replace("\\", "/", $entry);
				$pathinfo = path_parts($entry);
				if(is_dir($entry)) {
					if(!checkKeyWords($Ahide_folders, $pathinfo['filename']) && !checkKeyWords($Akeywords, $pathinfo['filename'])){
						$Adirs[] = $entry;
					}
				} else {
					if(in_array(strtolower($pathinfo['ext']), $Amedia_types)){
						if(!checkKeyWords($Ahide_files, $pathinfo['filename']) && !checkKeyWords($Akeywords, $pathinfo['filename'])){
							$Afiles[] = $entry;
						}
					}
				}
			}
		}
		@closedir($handle);
		//$d->close();
	}
	$Aitems['dirs'] = $Adirs;
	$Aitems['files'] = $Afiles;
	return $Aitems;
}




function printArray($theArray, $exit=false){
	print "<pre>";
	print_r($theArray);
	print "</pre>";
	if($exit){
		exit;
	}
}


/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
//
//                CONVERSION
//
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////

function asc2hex ($theString) {
	$temp = $theString;
	$data = "";
	for ($i=0; $i<strlen($temp); $i++){
		$char = substr($temp,$i,1);
		if(!ereg('[ A-Za-z0-9|/:.^]', $char)){
			$data .= rawurlencode(code2utf(ord($char)));
		}else{
			$data.=$char;
		}
	}
	return $data;
}

function XMLkind($theFile){
	if($data = file_get_as_string($theFile)){
		$xml_parser = xml_parser_create('');
		xml_parse_into_struct($xml_parser, $data, $vals, $index);
		xml_parser_free($xml_parser);
		$firstNode = trim(@$vals[0]['tag']);
	} else {
		$firstNode = false;
	}
	return $firstNode;
}

function code2utf($num){
   if($num<128)return chr($num);
   if($num<2048)return chr(($num>>6)+192).chr(($num&63)+128);
   if($num<65536)return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
   if($num<2097152)return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128) .chr(($num&63)+128);
   return '';
}

function cleanForFlash($theString){
	//global $osType;
	$retval = $theString;
	//if($osType == "win"){
		$retval = asc2hex ($retval);
		//$retval = str_replace("&", "%26", $retval);
		//$retval = str_replace("%", "%25", $retval);
	//} else {
		//$retval = rawurlencode($theString);
	//}
	//$retval = cleanForXML($theString);
	return $retval;
}
function cleanForXML($theString){
	$retval = $theString;
	//$retval = cleanForFlash($theString);
	//$retval = htmlentities($retval);
	//*
	//$retval = str_replace("&", "%26", $retval);
	/*
	$retval = str_replace("`", "%60", $retval);
	$retval = str_replace("^", "%5E", $retval);
	$retval = str_replace("{", "%7B", $retval);
	$retval = str_replace("}", "%7D", $retval);
	$retval = str_replace("]", "%5D", $retval);
	
	//*/
	//*
	$retval = str_replace("&", "&amp;", $retval);
	$retval = str_replace("<", "&lt;", $retval);
	$retval = str_replace(">", "&gt;", $retval);
	$retval = str_replace("'", "&apos;", $retval);
	$retval = str_replace('"', "&quot;", $retval);
	$retval = str_replace('', "&#xA9;", $retval);
	$retval = str_replace('', "&#x2122;", $retval);
	//*/
	return $retval;
}


/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
//
//                XML Playlist
//
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////

function returnXMLkeys($AitemInfo){
	$retval = "";
	foreach($AitemInfo as $key => $val){
		$retval .= "<".$key.">".cleanForFlash(trim($val))."</".$key.">".newline;
	}
	return $retval;
}



function makeXMLplaylist($theDir, $recursive=false){
	global $WIMPY_PATH, $wimpyApp, $defaultVisualBaseName, $defaultVisualExt;

	$sPath = utf8_decode(rawurldecode($theDir));
	if($recursive){
		$AdirList['files'] = GetDirArrayRecursive($sPath);
		$AdirList['dirs'] = array();
	} else {
		$AdirList = GetDirArray($sPath);
	}
	$playlistCoverart = cleanForFlash(lookForVisual($sPath.slash.$defaultVisualBaseName.".".$defaultVisualExt));

	$retval = '<playlist image="'.$playlistCoverart.'">'.newline;

	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////


	// Directory
	$Adirs = array();

	if(sizeof($AdirList['dirs']) > 0){
		for($i=0;$i<sizeof($AdirList['dirs']);$i++){
			$tempName = $AdirList['dirs'][$i];
			$path_info = path_parts($tempName);

			$Aitem = array();

			$Aitem['filename'] = filepath2url($tempName);

			$Aitem['date'] = date("Y-m-d H:i:s", filemtime ($tempName));

			// Image:
			$visualfilenameA = $tempName.slash.$defaultVisualBaseName.".".$defaultVisualExt;
			if (@is_file($visualfilenameA)){
				$visualFound = filepath2url($visualfilenameA);
			} else {
				$visualFound = "";
			}
			$Aitem['image'] = $visualFound;

			$infoFound = lookForInfo($tempName.slash."folder.info");
			if($infoFound != false){
				$retval .= '<item>'.newline;
				$retval .= '<filename>'.$Aitem['filename'].'</filename>'.newline;
				$retval .= $infoFound;
				$retval .= '<image>'.$Aitem['image'].'</image>'.newline;
				$retval .= '<date>'.$Aitem['date'].'</date>'.newline;
				
				$retval .= '<filekind>dir</filekind>'.newline;
				$retval .= "</item>".newline;

			} else {

				$Aitem['artist'] = "";
				$Aitem['title'] = $path_info['filename'];
				$Aitem['album'] = "";
				$Aitem['seconds'] = "";
				$Aitem['link'] = "";
				$Aitem['description'] = "";
				
				
				

				// Write item XML
				$retval .= '<item>'.newline;
				$retval .= returnXMLkeys($Aitem);
				$retval .= "<filekind>dir</filekind>".newline;
				$retval .= "</item>".newline;

			}

		}
	}



	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////


	// Files
	$Afiles = array();

	if(sizeof($AdirList['files']) > 0){
		for($i = 0; $i < sizeof($AdirList['files']); $i++){

			$tempName = $AdirList['files'][$i];
			$path_info = path_parts($tempName);


			$Aitem = array();

			// Filename
			//
			$Aitem['filename'] = filepath2url($tempName);
			$Aitem['date'] = date("Y-m-d H:i:s", filemtime ($tempName));
			$Aitem['image'] = lookForVisual($path_info['basepath'].slash.$path_info['basename'].".".$defaultVisualExt);

			$infoFound = lookForInfo($path_info['basepath'].slash.$path_info['basename'].".info");
			if($infoFound != false){
				$retval .= '<item>'.newline;
				$retval .= '<filename>'.$Aitem['filename'].'</filename>'.newline;
				$retval .= $infoFound;
				$retval .= '<date>'.$Aitem['date'].'</date>'.newline;
				$retval .= '<image>'.$Aitem['image'].'</image>'.newline;
				$retval .= '<filekind>'.strtolower($path_info['ext']).'</filekind>'.newline;
				$retval .= "</item>".newline;

			} else {
			
				if(strtolower($path_info['ext']) == "mp3"){
					$Aid3 = getID3info4me($tempName);
				} else {
					$Aid3 = array();
				}


				// Basic ID3 info:
				$Aitem['artist'] = @$Aid3['artist'];
				$Aitem['title'] = (@$Aid3['title'])? @$Aid3['title'] : $path_info['basename'];
				$Aitem['album'] = @$Aid3['album'];
				$Aitem['seconds'] = @$Aid3['seconds'];
				
				


				// URL Link
				//
				// [comment] = id3v1
				// [commentS] = id3v2
				//
				// See if comments contain a URL:
				if(@substr($Aid3['comment'],0,4) == "http"){
					$Aitem['link'] = @$Aid3['comment'];
				}
				// Replace ID3v1 with ID3v2
				if(@substr($Aid3['comments'],0,4) == "http"){
					$Aitem['link'] = @$Aid3['comments'];
				}

				// Comments and URL link
				//
				// [comment] = id3v1
				// [commentS] = id3v2
				//
				if(@substr(@$Aid3['comment'],0,4) != "http" && strlen(@$Aid3['comment']) > 0){
					$Aitem['description'] = @$Aid3['comment'];
				}
				// Replace ID3v1 with ID3v2
				if(@substr(@$Aid3['comments'],0,4) == "http" && strlen(@$Aid3['comments']) > 0){
					$Aitem['description'] = @$Aid3['comments'];
				}

				// Image
				//
				


				$retval .= '<item>'.newline;
				$retval .= returnXMLkeys($Aitem);
				$retval .= '<filekind>'.strtolower($path_info['ext']).'</filekind>'.newline;
				$retval .= "</item>".newline;

			}
			
			
		}
	}


	$retval .= "</playlist>".newline;
	@clearstatcache();
	return $retval;

}

function path_parts($thePath) {
	$thePath = str_replace("\\", "/", $thePath);
	$filepathA = explode("/", $thePath);
	$filename = array_pop($filepathA);
	$filepathB = explode(".", $filename);
	$extension = array_pop($filepathB);
	$basename = implode(".", $filepathB);
	$basePath = join("/", $filepathA);
	$Aret = array();
	$Aret['filename'] = $filename;
	$Aret['ext'] = $extension;
	$Aret['basename'] = $basename;
	$Aret['basepath'] = $basePath;
	return $Aret;
}

function getID3info4me($theFile_in){
	global $getMyid3info, $getID3;
	if($getMyid3info == "yes"){
		$info = $getID3->analyze(urldecode($theFile_in));
		getid3_lib::CopyTagsToComments($info);
	} else {
		$info = array();
	}
	$retval = array();
	if(sizeof($info)>0){
		if(isset($info['comments_html'])){
			$Atemp = @$info['comments_html'];
			foreach($Atemp as $key => $val){
				$retval[$key] = $val[0];
			}
			$retval['seconds']=(@$info['playtime_seconds']);
		}
	}
	return $retval;
}

function lookForVisual($theFile){
	global $wimpyApp,$defaultVisualBaseName, $defaultVisualExt, $WIMPY_PATH;
	$visualFound = "";
	if (@is_file($theFile)){
		$visualFound = filepath2url($theFile);
	}
	return $visualFound;
}

function lookForInfo($theFile){
	global $wimpyApp, $WIMPY_PATH;
	$retval = "";
	if(@is_file($theFile)){
		$retval = file_get_as_string($theFile);
	}
	return $retval;
}


function url2filepath($theURL){
	global $WIMPY_PATH;
	$pubPath = str_replace($WIMPY_PATH['www'], "", $theURL);
	$theFilepath = $WIMPY_PATH['physical'].$pubPath;
	$theFilepath = str_replace("//", "/", $theFilepath);
	return $theFilepath;
}


function filepath2url($theFilepath){
	global $WIMPY_PATH;
	$pubPath = str_replace($WIMPY_PATH['physical'], "", $theFilepath);
	$theFilepath = $WIMPY_PATH['www'].$pubPath;
	$theFilepath = str_replace("://", "__:__", $theFilepath);
	$theFilepath = str_replace("//", "/", $theFilepath);
	$theFilepath = str_replace("__:__", "://", $theFilepath);
	return $theFilepath;
}

/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
//
//                    WRITE X-HTML
//
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////


function printXML($theXML){
	$XMLlead = '<'.urldecode("%3F").'xml version="1.0"  encoding="UTF-8" '.urldecode("%3F").'>';
	//*
	header("Pragma: public", false);
	header("Expires: Thu, 19 Nov 1981 08:52:00 GMT", false);
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0", false);
	header("Cache-Control: no-store, no-cache, must-revalidate", false);
	header("Content-Type: text/xml");
	//*/
	print (utf8_encode($XMLlead.$theXML));
	exit;
}



/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
//
//                    ACTIONS
//
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////

if(!isset($action)){
	$action = "";
}




if($action=="getVersion"){
	print "Wimpy Player Version: $wimpyVersion";
	exit;
} else if ($action=="phpinfo"){
	if($blockPHPinfo != "yes"){
		$retval = phpinfo();
		echo "$retval";
		exit;
	}
} else if ($action=="getList"){
	$Ahide_files = Array();
	$Ahide_folders = Array();
	$Amedia_types = Array("xml");
	$Akeywords = array();
	$Axmls = GetDirArrayRecursive("");
	//$Axmls = GetDirArrayRecursive("", Array("xml"));
	$retval = "<list>";
	foreach($Axmls as $key=>$val){
		$each = strtolower(@XMLkind($val));
		$lookup = strtolower($theKind);
		
		if($lookup == "playlist"){
			if($each == $lookup || $each == "rss"){
				$retval .= "<item>".$WIMPY_PATH['www']."/".str_replace("./", "", $val)."</item>";
			}
		} else {
			if($each == $lookup){
				$retval .= "<item>".$WIMPY_PATH['www']."/".str_replace("./", "", $val)."</item>";
			}
		}
		
	}
	$retval .= "</list>";
	printXML($retval);
	//print ($retval);

} else if($action=="getstartupdirlist"){
	header("Content-Type: text/html", false);
	//header("charset: UTF-8");
	//header("Content-Transfer-Encoding: UTF-8");
	if($getMyid3info == "yes"){
		if(is_file('getid3.php')){
			require ('getid3.php');
			$getID3 = new getID3;
		} else if (is_file('getid3'.slash.'getid3.php')){
			require ('getid3'.slash.'getid3.php');
			$getID3 = new getID3;
		} else if (is_file(@$getid3libPath)){
			require (@$getid3libPath);
			$getID3 = new getID3;
		} else {
			$getMyid3info = "no";
		}
	}
	$getall = false;
	if($findAllMedia == "yes"){
		$getall = true;
	}
	printXML(makeXMLplaylist($WIMPY_PATH['physical'], $getall));


} else if ($action=="dir"){

	if($getMyid3info == "yes"){
		if(is_file('getid3.php')){
			require ('getid3.php');
			$getID3 = new getID3;
		} else if (is_file('getid3'.slash.'getid3.php')){
			require ('getid3'.slash.'getid3.php');
			$getID3 = new getID3;
		} else if (is_file(@$getid3libPath)){
			require (@$getid3libPath);
			$getID3 = new getID3;
		} else {
			$getMyid3info = "no";
		}
	}
	$setDir = @$_REQUEST['dir'];
	//if(!$setDir || substr ($setDir, 0, 2 ) == ".." || $setDir.substr(0,1) == "/" || $setDir.substr(0,1) == "\\"){
	if(!$setDir || substr ($setDir, 0, 2 ) == ".." || substr($setDir, 0, 1) == "/" || substr($setDir, 0, 1) == "\\"){
		 $setDir = "";
	}
	printXML(makeXMLplaylist(url2filepath($setDir)));


} else if($action=="getmysql"){
	require ("wimpy.sql.php");


} else if ($action=="podcast"){

	$method = "mysql";
	$getMyid3info = "yes";
	if(is_file('getid3.php')){
		require ('getid3.php');
		$getID3 = new getID3;
	} else if (is_file('getid3'.slash.'getid3.php')){
		require ('getid3'.slash.'getid3.php');
		$getID3 = new getID3;
	} else {
		//$getMyid3info = "no";
		print 'You have elected to use ID3 information in the playlist.<br>'; 
		print 'In order to present ID3 information you must upload the getID3<br>';
		print 'library to your wimpy folder. The files can be found in the <br>';
		print '"goodies" folder or downloaded from the following location:<br>';
		print 'http://www.wimpyplayer.com/resources<br>';
		print 'Please upload all of the getID3 files to the same location as rave.php<br>';
		exit;
	}
	$podBack = GetDirArray($WIMPY_PATH['physical'], "yes");


} else {




	$flashCode = '<html>'.newline;
	$flashCode .= '<head>'.newline;
	$flashCode .= '<title>'.$wimpyHTMLpageTitle.'</title>'.newline;
	$flashCode .= '<script src="'.$WIMPY_PATH['www']."/".$wimpyJavascriptFile.'" type="text/javascript"></script>'.newline;
	$flashCode .= '</head>'.newline;
	$flashCode .= '<body bgcolor="#'.$bkgdColor.'" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">'.newline;
	$flashCode .= '<table width="100%" height="100%"  border="0" cellpadding="0" cellspacing="0">'.newline;
	$flashCode .= '<tr>'.newline;
	$flashCode .= '<td align="center" valign="middle">'.newline;
	$flashCode .= '<!-- START WIMPY CODE -->'.newline;
	$flashCode .= '<div id="flashcontent">'.newline;
	$flashCode .= '<strong>You need to upgrade your Flash Player</strong>'.newline;
	$flashCode .= '</div>'.newline;
	$flashCode .= '<script language="JavaScript" >'.newline;


	$flashCode .= '// <![CDATA['.newline;
	$flashCode .= 'var wimpyConfigs080108 = new Object();'.newline;
	$flashCode .= 'wimpyConfigs080108.wimpySwf="'.$wimpySwf.'";'.newline;
	$flashCode .= 'wimpyConfigs080108.wimpyApp="'.$wimpyApp.'";'.newline;
	$flashCode .= 'wimpyConfigs080108.wimpyWidth="'.$displayWidth.'";'.newline;
	$flashCode .= 'wimpyConfigs080108.wimpyHeight="'.$displayHeight.'";'.newline;
	if($useConfigFile){
		$flashCode .= 'wimpyConfigs080108.wimpyConfigs="'.$wimpyConfigFile.'";'.newline;
	}
	if($useSkin){
		$flashCode .= 'wimpyConfigs080108.wimpySkin="'.$wimpySkin.'";'.newline;
	}
	$flashCode .= 'wimpyConfigs080108.autoAdvance="no";'.newline;
	$flashCode .= 'makeWimpyPlayer(wimpyConfigs080108, "flashcontent");'.newline;
	$flashCode .= '// ]]>'.newline;


	$flashCode .= '</script>'.newline;
	$flashCode .= '<!-- END WIMPY CODE -->'.newline;
	$flashCode .= '</td>'.newline;
	$flashCode .= '</tr>'.newline;
	$flashCode .= '</table>'.newline;
	$flashCode .= '</body>'.newline;
	$flashCode .= '</html>'.newline;
	print ($flashCode);
	exit;
}
?>