///////////////////////////////////////
//                                   //
//        Wimpy Button Maker         //
//                                   //
//           ©2008 Plaino            //
//           Available at            //
//       www.wimpyplayer.com         //
//                                   //
///////////////////////////////////////

var wimpyUserAgent = navigator.appName.indexOf("Microsoft");
var wimpyButtonIDs = wimpyButtonIDs || Array();
function wimpyButtonStopOthers(myid_in){
	for(i=0; i<wimpyButtonIDs.length; i++){
		if(wimpyButtonIDs[i] != myid_in){
			if (wimpyUserAgent != -1) {
				window[wimpyButtonIDs[i]].js_wimpy_pause();
			} else {
				document[wimpyButtonIDs[i]].js_wimpy_pause();
			}
		}
	}
}
function writeWimpyButton(theFile, wimpyWidth, wimpyHeight, wimpyConfigs, backgroundColor){
	var wimpyReg = "REGISTRATION_CODE";
	var defaultWidth = 35;
	var defaultHeight = 35;
	var defaultConfigs = "";
	var baseURL = "";
	var wimpySwf = "wimpy_button.swf";
	var wimpyWidth = (wimpyWidth == null) ? defaultWidth : wimpyWidth;
	var wimpyHeight = (wimpyHeight == null) ? defaultHeight : wimpyHeight;
	var wimpyConfigs = (wimpyConfigs == null) ? defaultConfigs : wimpyConfigs;
	var backgroundColor = (backgroundColor == null) ? false : backgroundColor;
	var myid = "wimpybutton"+Math.round((Math.random()*1000)+1);
	wimpyButtonIDs[wimpyButtonIDs.length] = myid;
	var flashCode = "";
	var newlineChar = "\n";
	var backgroundColor = (backgroundColor == null) ? false : backgroundColor;
	if(typeof(backgroundColor) == "string"){
		var Astring = backgroundColor.split("");
		if(Astring[0] == "#"){
			Astring.shift();
			backgroundColor = Astring.join("");
		}
	}
	if(backgroundColor == false){
		tptParam = '<param name="wmode" value="transparent" />'+newlineChar;
		tptEmbed = ' wmode="transparent"';
	} else {
		tptParam = '<param name="bgcolor" value="#'+backgroundColor+'" />'+newlineChar;
		tptEmbed = ' bgcolor="#'+backgroundColor+'"';
	}
	flashCode += '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="'+wimpyWidth+'" height="'+wimpyHeight+'" id="'+myid+'">'+newlineChar;
	flashCode += '<param name="movie" value="'+wimpySwf+'" />'+newlineChar;
	flashCode += '<param name="loop" value="false" />'+newlineChar;
	flashCode += '<param name="menu" value="false" />'+newlineChar;
	flashCode += '<param name="quality" value="high" />'+newlineChar;
	flashCode += '<param name="wmode" value="transparent" />'+newlineChar;
	flashCode += '<param name="flashvars" value="theFile='+baseURL+theFile+wimpyConfigs+'&wimpyReg='+wimpyReg+'&myid='+myid+'" />'+newlineChar;
	flashCode += '<embed src="'+wimpySwf+'" width="'+wimpyWidth+'" height="'+wimpyHeight+'" flashvars="theFile='+baseURL+theFile+wimpyConfigs+'&wimpyReg='+wimpyReg+'&myid='+myid+'"'+tptEmbed+' loop="false" menu="false" quality="high" name="'+myid+'" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /></object>'+newlineChar;
	//document.write('<br>'+myid+'<br><textarea name="textarea" cols="40" rows="3">'+flashCode+'</textarea><br>')+newlineChar;
	document.write(flashCode);
}
