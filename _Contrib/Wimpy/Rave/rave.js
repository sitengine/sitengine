///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////
//                                                      ///////
//                      Wimpy JS                        ///////
//                     Version 2.0                      ///////
//                                                      ///////
//         by Mike Gieson <info@wimpyplayer.com>        ///////
//                                                      ///////
//        Available at http://www.wimpyplayer.com       ///////
//                 2002-2006 plaino                    ///////
//                                                      ///////
///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////
//                                                      ///////
//                USE AT YOUR OWN RISK                  ///////
//                                                      ///////
///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////
//                                                      ///////
//                       OPTIONS                        ///////
//                                                      ///////
///////////////////////////////////////////////////////////////


// Enter your registration code here:
var wimpyReg			= "";

// The following should refer to a filename only, not a full URL. 
// We've provided this option so that you can change the file name if needed.
var wimpySwfBasename	= "rave.swf";
var wimpyAppBasename	= "rave.php";
var wimpyPopout			= "rave_popout.html";
var wimpyFullscreen		= "rave_fullscreen.html";

// Enter your default configuration options here: 
// When entering options that are references to files 
// (e.g. wimpyApp, wimpySwf, plugPlaylist, onTrackCompleteURL), 
// be sure to use a full URL to the file.
var defaultWimpyConfigs = new Object();
defaultWimpyConfigs.wimpyReg			= wimpyReg;
defaultWimpyConfigs.wimpySwf			= wimpySwfBasename;
defaultWimpyConfigs.wimpyApp			= wimpyAppBasename;
defaultWimpyConfigs.wimpySkin			= "";
defaultWimpyConfigs.startPlayingOnload	= "";
defaultWimpyConfigs.startOnTrack		= "";
defaultWimpyConfigs.autoAdvance			= "";
defaultWimpyConfigs.loopTrack			= "";
defaultWimpyConfigs.repeatPlaylist		= "";
defaultWimpyConfigs.randomPlayback		= "";
defaultWimpyConfigs.randomOnLoad		= "";
defaultWimpyConfigs.sortField			= "";
defaultWimpyConfigs.sortOrder			= "";
defaultWimpyConfigs.bufferSeconds		= "";
defaultWimpyConfigs.theVolume			= "";
defaultWimpyConfigs.limitPlaytime		= "";
defaultWimpyConfigs.resume				= "";
defaultWimpyConfigs.scrollFormat		= "";
defaultWimpyConfigs.timeFormat			= "";
defaultWimpyConfigs.infoDisplaySpeed	= "";
defaultWimpyConfigs.fsMode				= "";
defaultWimpyConfigs.setAspectRatio		= "";
defaultWimpyConfigs.clickWindowAction	= "";
defaultWimpyConfigs.infoButtonAction	= "";
defaultWimpyConfigs.linkToWindow		= "";
defaultWimpyConfigs.coverartBasename	= "";
defaultWimpyConfigs.popUpHelp			= "";
defaultWimpyConfigs.enableDownloads		= "";
defaultWimpyConfigs.useSysCodePage		= "";
defaultWimpyConfigs.wimpyHTMLpageTitle	= "";
defaultWimpyConfigs.tptBkgd				= "";
defaultWimpyConfigs.bkgdColor			= "#000000";
defaultWimpyConfigs.wimpyWidth			= "250";
defaultWimpyConfigs.wimpyHeight			= "290";
defaultWimpyConfigs.debugMode			= "";
defaultWimpyConfigs.startupLogo			= "";
defaultWimpyConfigs.defaultImage		= "";
defaultWimpyConfigs.onTrackComplete		= "";
defaultWimpyConfigs.onTrackCompleteURL	= "";
defaultWimpyConfigs.plugPlaylist		= "";
defaultWimpyConfigs.plugEvery			= "";
defaultWimpyConfigs.getMyid3info		= "";
defaultWimpyConfigs.findAllMedia		= "";
defaultWimpyConfigs.hide_folders		= "";
defaultWimpyConfigs.hide_files			= "";
defaultWimpyConfigs.playlist			= "";


/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
////////////                                     ////////////
////////////    Advanced Usage (experts only!)   ////////////
////////////                                     ////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////

// When enableWimpyEvents is set to TRUE, then the following functions will be enabled:
//    wimpy_amReady
//    handlTrackStarted
//    handleTrackDon
// These "handler" functions are currently set up for use with Example 6 and 7 (readme_rave_js_example6.html).

var enableWimpyEvents = false;



// This function is pinged when Wimpy is ready and able to accept JavaScript calls / interaction.
// NOTE: See also wimpy_amReady_ask
function handleWimpyInit(retval){

	// Your code here:

	// NOTE: The following code is used for example purposes:
	var retText = "Wimpy is ready:" + retval + "<br>";
	writeitAppend(retText,"wimpySaid");


}


// This function gets pinged every time a track starts to play.
function handlTrackStarted(returnedObject){

	// Your code here:

	// NOTE: The following code is used for example purposes:
	var retText = 'Track Started. <br>&nbsp;&nbsp;Track data should be visible in the "Track info" section below.<br>';
	writeitAppend(retText, "wimpySaid");
	displayPlaylistObject(returnedObject);


}



// This function gets pinged each time a track finnishes playing.
function handleTrackDone(returnedObject){
	
	// Your code here:

	// NOTE: The following code is used for example purposes:

	var retText = 'Track Done. <br>&nbsp;&nbsp;Track data should be visible in the "Track info" section below.<br>';
	writeitAppend(retText, "wimpySaid");
	displayPlaylistObject(returnedObject);


}

/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
////////////                                     ////////////
////////////       Do not edit below here        ////////////
////////////                                     ////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////



///////////////////////////////
//
//        UTILITIES
//
///////////////////////////////

function randomNumber(minNum, maxNum) {
	return (minNum + Math.floor(Math.random() * (maxNum - minNum + 1)));
}
function path_parts(thePath) {
	if(thePath.lastIndexOf("/") == thePath.length-1){
		thePath = thePath.substr(0, thePath.length-1);
	}
	var filepathA = thePath.split("/");
	var filename = filepathA.pop();
	var filepathB = filename.split(".");
	var extension = "";
	if (filepathB.length > 1) {
		extension = filepathB.pop();
	}
	var basename = filepathB.join(".");
	if(extension == ""){
		filepathA.push(filename);
	}
	var mybasepath = filepathA.join("/");
	
	if(mybasepath.length > 0){
		mybasepath = mybasepath + "/";
	}
	var Oret = new Object();
	Oret.filename = filename;
	Oret.extension = extension;
	Oret.basename = basename;
	Oret.basepath = mybasepath;
	Oret.filepath = thePath;
	return Oret;
}
function getExtension(theFilename){
	return unescape(theFilename).split("/").pop().split(".").pop().toLowerCase();
}
function trim(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}
function stripWhiteSpace(string_in) {
	var retval =  string_in.split("\n").join("").split("\r\n").join("").split("\t").join("").split("%0A").join("").split("%09").join("");
	return retval;
}
function getQueryString(){
	var qsParm = new Array();
	var q = window.location.search || document.location.hash;
	var query = q.substring(1);
	var parms = query.split('&');
	for (var i=0; i<parms.length; i++) {
		var pos = parms[i].indexOf('=');
		if (pos > 0) {
			var key = parms[i].substring(0,pos);
			var val = parms[i].substring(pos+1);
			qsParm[key] = val;
		}
	}
	return qsParm;
}

var XMLio = [];
function XMLimport(theURL, handler) {
	if(window.ActiveXObject ) {
		var id = XMLio.length;
		var AX = ["Msxml2.XMLHTTP", "Microsoft.XMLHTTP","Microsoft.XMLDOM"];
		for( var i = 0; !XMLio[id] && i < AX.length; i++ ) {
			try {XMLio[id] = new ActiveXObject( AX[i] );} catch(e) {}
		}
		if(XMLio[id]) {
			XMLio[id].onreadystatechange = new Function( 'if( XMLio['+(id)+'].readyState == 4 ) { '+handler+'(XMLio['+(id)+'].responseXML);}' );
			if( XMLio[id].load ) {
				XMLio[id].load(theURL);
			} else {
				XMLio[id].open('GET', theURL, true);
				XMLio[id].send(null);
			}
			return true;
		}
	} else {
		var id = XMLio.length;
		XMLio[id] = new XMLHttpRequest();
		XMLio[id].onreadystatechange = new Function('if(XMLio['+(id)+'].readyState == 4){if(XMLio['+(id)+'].status == 200 ) {'+handler+'(XMLio['+(id)+'].responseXML);} else {'+handler+'(false);}}');
		XMLio[id].open("GET", theURL, true);
		XMLio[id].send(null);
		return true;
	}
	return false;
}

///////////////////////////////
///////////////////////////////
///////////////////////////////
///////////////////////////////
///////////////////////////////
//
//        RENDER PLAYER
//
///////////////////////////////

function makeWimpyPlayer(configsIN, theTarget){
	
	var theConfigObject = configsIN || "";
	var theTarget = theTarget || "flashcontent";

	if(typeof(theConfigObject) == "string"){
		var theConfigObject = defaultWimpyConfigs;

		if(theConfigObject != ""){
			theConfigObject.playlist = configsIN;
		}
	}
	
	for(var prop in defaultWimpyConfigs){
		theConfigObject[prop] = theConfigObject[prop] || defaultWimpyConfigs[prop];
	}

	if(theConfigObject.bkgdColor.substring(0,1) != "#"){
		theConfigObject.bkgdColor = "#" + theConfigObject.bkgdColor;
	}
	// <![CDATA[
	var so = new SWFObject(theConfigObject.wimpySwf + "?cachebust=" + new Date().getTime(), "wimpy", theConfigObject.wimpyWidth, theConfigObject.wimpyHeight, "8", theConfigObject.bkgdColor);
	theConfigObject["wimpyHTMLpageTitle"] = "";
	theConfigObject["wimpyJS"] = "";
	theConfigObject["wimpySwf"] = "";
	theConfigObject["wimpyWidth"] = "";
	theConfigObject["wimpyHeight"] = "";
	theConfigObject["bkgdColor"] = "";
	theConfigObject["hide_files"] = "";
	theConfigObject["hide_folders"] = "";
	theConfigObject["findAllMedia"] = "";

	for(var prop in theConfigObject){
		if(prop == "playlist"){
			var val = theConfigObject[prop];
		} else {
			var val = encodeURI(theConfigObject[prop]);
		}
		if(val != ""){
			so.addVariable(prop, val);
		}
	}
	so.addParam("scale", "noscale");
	so.addParam("salign", "lt");
	so.addParam("allowScriptAccess", "always");
	so.addParam("allowFullScreen", "true");
	so.addParam("menu", "false");
	so.write(theTarget);
	// ]]>
}


///////////////////////////////
///////////////////////////////
///////////////////////////////
///////////////////////////////
///////////////////////////////
//
//        POP OUT
//
///////////////////////////////

var wimpy_popout_window;

function wimpyIsOpen(){
	if (!wimpy_popout_window || wimpy_popout_window.closed){
		return false;
	} else {
		return true;
	}
}

function notifyOpener() {
	if(self.opener || !self.opener.wimpy_popout_window) {
		self.opener.wimpy_popout_window = self;
	}
}

function wimpy_popAndPlay(popPage, theWidth, theHeight, thePlaylist, startPlayingOnload){
	var popPage = popPage || wimpyPopout;
	var theFile = theFile || "";
	var theWidth = theWidth || defaultWimpyConfigs.wimpyWidth;
	var theHeight = theHeight || defaultWimpyConfigs.wimpyHeight;
	var startPlayingOnload = startPlayingOnload || "";
	
	var winName = "a" + randomNumber(1, 1000);
	var winURL = popPage + "?w=" + theWidth + "&h=" + theHeight + "&startPlayingOnload=" + startPlayingOnload + "&playlist=" + thePlaylist;
	wimpy_popout_window = window.open(winURL, winName,'width=' + theWidth + ',height=' + theHeight);
}

function wimpy_popout_make(){
	
	var queryString = getQueryString();
	var Ourl = path_parts(location.href);
	// <![CDATA[
	var so = new SWFObject(Ourl.basepath + wimpySwfBasename + "?cachebust=" + new Date().getTime(), "wimpy", queryString['w'], queryString['h'], "8", "#000000");
	so.addVariable("wimpyPop", "yes");
	so.addParam("scale", "noscale");
	so.addParam("salign", "lt");
	so.addParam("allowScriptAccess", "always");
	so.addParam("allowFullScreen", "true");
	so.addParam("menu", "false");
	so.write("flashcontent");
	// ]]>
	//notifyOpener();
	setInterval( notifyOpener, 1000 );
}


///////////////////////////////
///////////////////////////////
///////////////////////////////
///////////////////////////////
///////////////////////////////
//
//        FULL SCREEN
//
///////////////////////////////

function wimpy_fullscreen(baseURL){
	var winName = "a" + randomNumber(1, 1000);
	window.open(baseURL + wimpyFullscreen, winName,'resizable=yes');
}

function wimpy_fullscreen_make(){
	window.moveTo(0,0);
	window.resizeTo(screen.availWidth/2, screen.availHeight/2);
	//var queryString = getQueryString();
	var Ourl = path_parts(location.href);
	// <![CDATA[
	var so = new SWFObject(Ourl.basepath + wimpySwfBasename + "?cachebust=" + new Date().getTime(), "wimpyFS", "100%", "100%", "8", "#000000");
	so.addVariable("wimpyFS", "yes");
	so.addParam("scale", "noscale");
	so.addParam("salign", "lt");
	so.addParam("allowScriptAccess", "always");
	so.addParam("menu", "false");
	so.write("flashcontent");
	// ]]>
}

///////////////////////////////
///////////////////////////////
///////////////////////////////
///////////////////////////////
///////////////////////////////
//
//        CONTROLS
//
///////////////////////////////

var wimpyUserAgent = navigator.appName.indexOf("Microsoft");

function wimpy_play(){
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_play();
	} else {
		return document["wimpy"].js_wimpy_play();
	}
}
function wimpy_stop(){
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_stop();
	} else {
		return document["wimpy"].js_wimpy_stop();
	}
}
function wimpy_pause(){
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_pause();
	} else {
		return document["wimpy"].js_wimpy_pause();
	}
}
function wimpy_next(){
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_next();
	} else {
		return document["wimpy"].js_wimpy_next();
	}
}
function wimpy_prev(){
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_prev();
	} else {
		return document["wimpy"].js_wimpy_prev();
	}
}
function wimpy_gotoTrack(trackNumber){
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_gotoTrack(trackNumber);
	} else {
		return document["wimpy"].js_wimpy_gotoTrack(trackNumber);
	}
}
function wimpy_clearPlaylist(){
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_clearPlaylist();
	} else {
		return document["wimpy"].js_wimpy_clearPlaylist();
	}
}
function wimpy_appendPlaylist(XMLplaylist, playOnLoad){
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_appendPlaylist(XMLplaylist, playOnLoad);
	} else {
		return document["wimpy"].js_wimpy_appendPlaylist(XMLplaylist, playOnLoad);
	}
}
function wimpy_getTrackInfo(trackNumber){
	var sendTrackNumber = trackNumber || false;
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_getTrackInfo(sendTrackNumber);
	} else {
		return document["wimpy"].js_wimpy_getTrackInfo(sendTrackNumber);
	}
}
function wimpy_loadExternalPlaylist(playlistURL, startOnLoad){
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_loadExternalPlaylist(playlistURL, startOnLoad);
	} else {
		return document["wimpy"].js_wimpy_loadExternalPlaylist(playlistURL, startOnLoad);
	}
}

function wimpy_amReady_ask(){
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_amReady_ask();
	} else {
		return document["wimpy"].js_wimpy_amReady_ask();
	}
}

function wimpy_setVolume(thePercent){
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_setVolume(thePercent);
	} else {
		return document["wimpy"].js_wimpy_setVolume(thePercent);
	}
}

function wimpy_setLoopTrackState(theState){
	// off, on
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_setLoopTrackState(theState);
	} else {
		return document["wimpy"].js_wimpy_setLoopTrackState(theState);
	}
}

function wimpy_setRandomState(theState){
	// off, on
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_setRandomState(theState);
	} else {
		return document["wimpy"].js_wimpy_setRandomState(theState);
	}
}

function wimpy_setRepeatState(theState){
	// off, on
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_setRepeatState(theState);
	} else {
		return document["wimpy"].js_wimpy_setRepeatState(theState);
	}
}

function wimpy_setMuteState(theState){
	// off, on
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_setMuteState(theState);
	} else {
		return document["wimpy"].js_wimpy_setMuteState(theState);
	}
}

function wimpy_updateInfoDisplay(theArtist, theTitle){
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_updateInfoDisplay(theArtist, theTitle);
	} else {
		return document["wimpy"].js_wimpy_updateInfoDisplay(theArtist, theTitle);
	}
}




function wimpy_getPlayheadPercent(){
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_getPlayheadPercent();
	} else {
		return document["wimpy"].js_wimpy_getPlayheadPercent();
	}
}
function wimpy_getPlayheadSeconds(){
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_getPlayheadSeconds();
	} else {
		return document["wimpy"].js_wimpy_getPlayheadSeconds();
	}
}


function wimpy_setPlayheadPercent(thePercent){
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_setPlayheadPercent(thePercent);
	} else {
		return document["wimpy"].js_wimpy_setPlayheadPercent(thePercent);
	}
}
function wimpy_setPlayheadSeconds(theSeconds){
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_setPlayheadSeconds(theSeconds);
	} else {
		return document["wimpy"].js_wimpy_setPlayheadSeconds(theSeconds);
	}
}
function wimpy_getLoadPercent(){
	var retval = "";
	if (wimpyUserAgent != -1) {
		retval = window["wimpy"].js_wimpy_getLoadPercent();
	} else {
		retval = document["wimpy"].js_wimpy_getLoadPercent();
	}
	return retval;
}
function wimpy_getLoadState(){
	var retval = "";
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_getLoadState();
	} else {
		return document["wimpy"].js_wimpy_getLoadState();
	}
	return retval;
}
function wimpy_getPlayerState(){
	var retval = "";
	if (wimpyUserAgent != -1) {
		return window["wimpy"].js_wimpy_getPlayerState();
	} else {
		return document["wimpy"].js_wimpy_getPlayerState();
	}
	return retval;
}

function wimpy_getTotalPlaylistItems(){
	var retval = "";
	if (wimpyUserAgent != -1) {
		retval = window["wimpy"].js_wimpy_getTotalPlaylistItems();
	} else {
		retval = document["wimpy"].js_wimpy_getTotalPlaylistItems();
	}
	return retval;
}

function wimpy_getPlaylist(){
	var retval;
	if (wimpyUserAgent != -1) {
		retval = window["wimpy"].js_wimpy_getPlaylist();
	} else {
		retval = document["wimpy"].js_wimpy_getPlaylist();
	}
	return retval;
}

function wimpy_getPlaylistXML(){
	var retval = "";
	if (wimpyUserAgent != -1) {
		retval = window["wimpy"].js_wimpy_getPlaylistXML();
	} else {
		retval = document["wimpy"].js_wimpy_getPlaylistXML();
	}
	return retval;
}

function wimpy_callPlugin(arg1, arg2){
	var retval = "";
	if (wimpyUserAgent != -1) {
		retval = window["wimpy"].js_wimpy_callPlugin(arg1, arg2);
	} else {
		retval = document["wimpy"].js_wimpy_callPlugin(arg1, arg2);
	}
	return retval;
}

function wimpy_resume_kill(){
	var retval = "";
	if (wimpyUserAgent != -1) {
		retval = window["wimpy"].js_wimpy_resume_kill();
	} else {
		retval = document["wimpy"].js_wimpy_resume_kill();
	}
	return retval;
}
// The following are called by Wimpy. DO NOTE invoke these methods, 
// Wimpy will call them as needed to inform you of an event.
function wimpy_amReady(retval){
	if(enableWimpyEvents){
		handleWimpyInit(retval);
	}
}
function wimpy_trackStarted(returnedObject){
	if(enableWimpyEvents){
		handlTrackStarted(returnedObject);
	}
}
function wimpy_trackDone(returnedObject){
	if(enableWimpyEvents){
		handleTrackDone(returnedObject);
	}
}
/**
 * SWFObject v1.5: Flash Player detection and embed - http://blog.deconcept.com/swfobject/
 *
 * SWFObject is (c) 2007 Geoff Stearns and is released under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 */
if(typeof deconcept=="undefined"){var deconcept=new Object();}if(typeof deconcept.util=="undefined"){deconcept.util=new Object();}if(typeof deconcept.SWFObjectUtil=="undefined"){deconcept.SWFObjectUtil=new Object();}deconcept.SWFObject=function(_1,id,w,h,_5,c,_7,_8,_9,_a){if(!document.getElementById){return;}this.DETECT_KEY=_a?_a:"detectflash";this.skipDetect=deconcept.util.getRequestParameter(this.DETECT_KEY);this.params=new Object();this.variables=new Object();this.attributes=new Array();if(_1){this.setAttribute("swf",_1);}if(id){this.setAttribute("id",id);}if(w){this.setAttribute("width",w);}if(h){this.setAttribute("height",h);}if(_5){this.setAttribute("version",new deconcept.PlayerVersion(_5.toString().split(".")));}this.installedVer=deconcept.SWFObjectUtil.getPlayerVersion();if(!window.opera&&document.all&&this.installedVer.major>7){deconcept.SWFObject.doPrepUnload=true;}if(c){this.addParam("bgcolor",c);}var q=_7?_7:"high";this.addParam("quality",q);this.setAttribute("useExpressInstall",false);this.setAttribute("doExpressInstall",false);var _c=(_8)?_8:window.location;this.setAttribute("xiRedirectUrl",_c);this.setAttribute("redirectUrl","");if(_9){this.setAttribute("redirectUrl",_9);}};deconcept.SWFObject.prototype={useExpressInstall:function(_d){this.xiSWFPath=!_d?"expressinstall.swf":_d;this.setAttribute("useExpressInstall",true);},setAttribute:function(_e,_f){this.attributes[_e]=_f;},getAttribute:function(_10){return this.attributes[_10];},addParam:function(_11,_12){this.params[_11]=_12;},getParams:function(){return this.params;},addVariable:function(_13,_14){this.variables[_13]=_14;},getVariable:function(_15){return this.variables[_15];},getVariables:function(){return this.variables;},getVariablePairs:function(){var _16=new Array();var key;var _18=this.getVariables();for(key in _18){_16[_16.length]=key+"="+_18[key];}return _16;},getSWFHTML:function(){var _19="";if(navigator.plugins&&navigator.mimeTypes&&navigator.mimeTypes.length){if(this.getAttribute("doExpressInstall")){this.addVariable("MMplayerType","PlugIn");this.setAttribute("swf",this.xiSWFPath);}_19="<embed type=\"application/x-shockwave-flash\" src=\""+this.getAttribute("swf")+"\" width=\""+this.getAttribute("width")+"\" height=\""+this.getAttribute("height")+"\" style=\""+this.getAttribute("style")+"\"";_19+=" id=\""+this.getAttribute("id")+"\" name=\""+this.getAttribute("id")+"\" ";var _1a=this.getParams();for(var key in _1a){_19+=[key]+"=\""+_1a[key]+"\" ";}var _1c=this.getVariablePairs().join("&");if(_1c.length>0){_19+="flashvars=\""+_1c+"\"";}_19+="/>";}else{if(this.getAttribute("doExpressInstall")){this.addVariable("MMplayerType","ActiveX");this.setAttribute("swf",this.xiSWFPath);}_19="<object id=\""+this.getAttribute("id")+"\" classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\""+this.getAttribute("width")+"\" height=\""+this.getAttribute("height")+"\" style=\""+this.getAttribute("style")+"\">";_19+="<param name=\"movie\" value=\""+this.getAttribute("swf")+"\" />";var _1d=this.getParams();for(var key in _1d){_19+="<param name=\""+key+"\" value=\""+_1d[key]+"\" />";}var _1f=this.getVariablePairs().join("&");if(_1f.length>0){_19+="<param name=\"flashvars\" value=\""+_1f+"\" />";}_19+="</object>";}return _19;},write:function(_20){if(this.getAttribute("useExpressInstall")){var _21=new deconcept.PlayerVersion([6,0,65]);if(this.installedVer.versionIsValid(_21)&&!this.installedVer.versionIsValid(this.getAttribute("version"))){this.setAttribute("doExpressInstall",true);this.addVariable("MMredirectURL",escape(this.getAttribute("xiRedirectUrl")));document.title=document.title.slice(0,47)+" - Flash Player Installation";this.addVariable("MMdoctitle",document.title);}}if(this.skipDetect||this.getAttribute("doExpressInstall")||this.installedVer.versionIsValid(this.getAttribute("version"))){var n=(typeof _20=="string")?document.getElementById(_20):_20;n.innerHTML=this.getSWFHTML();return true;}else{if(this.getAttribute("redirectUrl")!=""){document.location.replace(this.getAttribute("redirectUrl"));}}return false;}};deconcept.SWFObjectUtil.getPlayerVersion=function(){var _23=new deconcept.PlayerVersion([0,0,0]);if(navigator.plugins&&navigator.mimeTypes.length){var x=navigator.plugins["Shockwave Flash"];if(x&&x.description){_23=new deconcept.PlayerVersion(x.description.replace(/([a-zA-Z]|\s)+/,"").replace(/(\s+r|\s+b[0-9]+)/,".").split("."));}}else{if(navigator.userAgent&&navigator.userAgent.indexOf("Windows CE")>=0){var axo=1;var _26=3;while(axo){try{_26++;axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash."+_26);_23=new deconcept.PlayerVersion([_26,0,0]);}catch(e){axo=null;}}}else{try{var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");}catch(e){try{var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");_23=new deconcept.PlayerVersion([6,0,21]);axo.AllowScriptAccess="always";}catch(e){if(_23.major==6){return _23;}}try{axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash");}catch(e){}}if(axo!=null){_23=new deconcept.PlayerVersion(axo.GetVariable("$version").split(" ")[1].split(","));}}}return _23;};deconcept.PlayerVersion=function(_29){this.major=_29[0]!=null?parseInt(_29[0]):0;this.minor=_29[1]!=null?parseInt(_29[1]):0;this.rev=_29[2]!=null?parseInt(_29[2]):0;};deconcept.PlayerVersion.prototype.versionIsValid=function(fv){if(this.major<fv.major){return false;}if(this.major>fv.major){return true;}if(this.minor<fv.minor){return false;}if(this.minor>fv.minor){return true;}if(this.rev<fv.rev){return false;}return true;};deconcept.util={getRequestParameter:function(_2b){var q=document.location.search||document.location.hash;if(_2b==null){return q;}if(q){var _2d=q.substring(1).split("&");for(var i=0;i<_2d.length;i++){if(_2d[i].substring(0,_2d[i].indexOf("="))==_2b){return _2d[i].substring((_2d[i].indexOf("=")+1));}}}return "";}};deconcept.SWFObjectUtil.cleanupSWFs=function(){var _2f=document.getElementsByTagName("OBJECT");for(var i=_2f.length-1;i>=0;i--){_2f[i].style.display="none";for(var x in _2f[i]){if(typeof _2f[i][x]=="function"){_2f[i][x]=function(){};}}}};if(deconcept.SWFObject.doPrepUnload){if(!deconcept.unloadSet){deconcept.SWFObjectUtil.prepUnload=function(){__flash_unloadHandler=function(){};__flash_savedUnloadHandler=function(){};window.attachEvent("onunload",deconcept.SWFObjectUtil.cleanupSWFs);};window.attachEvent("onbeforeunload",deconcept.SWFObjectUtil.prepUnload);deconcept.unloadSet=true;}}if(!document.getElementById&&document.all){document.getElementById=function(id){return document.all[id];};}var getQueryParamValue=deconcept.util.getRequestParameter;var FlashObject=deconcept.SWFObject;var SWFObject=deconcept.SWFObject;