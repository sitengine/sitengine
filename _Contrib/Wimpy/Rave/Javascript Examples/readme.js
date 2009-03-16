/////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////
//
//  These functions are primarily used to 
//  display returned data in the readme examples.
//
/////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////


function displayInfo(returnedInfo){
	// Print the results to the page:
	writeit(returnedInfo,"trackInfo");
}
function displayObject(returnedObject){
	var retText = "";
	for(var prop in returnedObject){
		retText += "<b>" + prop + "</b> : " + returnedObject[prop] + "<br>";
	}
	writeit(retText,"trackInfo");
}

function renderHTML (theString) {
	if(theString != "" && typeof(theString) == "string"){
		//var retval = theString.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;");
		var retval = theString.split("&").join("&amp;").split("<").join("&lt;").split(">").join("&gt;");
		return retval;
	} else {
		return theString;
	}
}

function displayPlaylistObject(returnedObject){
	//displayObject(returnedObject)
	//*
	var retText = "";
	for(var prop in returnedObject){
		var value = returnedObject[prop];
		if(typeof(value) == "object"){
			for(var itemProp in value){
				retText += "<b>" + itemProp + "</b> : " + renderHTML(value[itemProp]) + "<br>";
			}
		} else {
			retText += "<b>" + prop + "</b> : " + renderHTML(value) + "<br>";
		}
	}
	writeit(retText,"trackInfo");
	
	
	//*/

	
}
function displayXML(returnedXML){

	// Wimpy returns teh data URL encoded, so we must un-URL encode it:
	var XMLdata = unescape(returnedXML);

	// Load the data into an XML object:
	// code for IE
	if (window.ActiveXObject){
		var doc=new ActiveXObject("Microsoft.XMLDOM");
		doc.async="false";
		doc.loadXML(XMLdata);
	// code for Mozilla, Firefox, Opera, etc.
	} else if (document.implementation && document.implementation.createDocument){
		var parser=new DOMParser();
		var doc=parser.parseFromString(XMLdata,"text/xml");
	} else {
		alert('Your browser cannot handle this script');
	}

	// Set X to the root (<item>) node:
	var x=doc.documentElement;

	// determine how many nodes are in the <item>. (Whatever went into Wimpy will come back out):
	var theNodeLength = x.childNodes.length
	retText = "<p>Item contains " + theNodeLength + " pieces of info:</p><b><pre>";

	// Itterate over each node and extract the name of the node and the value of the node:
	for(i=0; i<x.childNodes.length; i++){

		// Set the node name to a variable:
		var theNodeName = x.childNodes[i].nodeName;

		// Set the data contained in the node to a variable:
		// Check to see if the node is empty:
		if(x.childNodes[i].childNodes.length > 0){
			var theNodeValue = x.childNodes[i].childNodes[0].nodeValue;
		} else {
			// if the node is empty, set the variable to an empty string:
			var theNodeValue = "";
		}

		// Put the info into a human readable form:
		retText += theNodeName + " : \t" + theNodeValue + "<br>";
	}

	// Create a text box to dump the raw XML code into:
	retText += ('</pre></b><p>Here&apos;s the raw XML data that was returned:<br><textarea name="textarea" id="textarea" wrap="VIRTUAL">'+XMLdata+'</textarea></p>');
	
	// Print the results to the page:
	writeit(retText,"trackInfo");
}

/*
function writeit(text,id){
	
	if (typeof(document.getElementById) == "object") {
		var wimpyDoc = document.getElementById(id);
		//wimpyDoc.innerHTML = '';
		//wimpyDoc.innerHTML = text;
		
	} else if (document.all) {
		var wimpyDoc = document.all[id];
		wimpyDoc.innerHTML = text;
		
	} else if (document.layers) {
		var wimpyDoc = document.layers[id];
		text2 = '<P CLASS="testclass">' + text + '</P>';
		wimpyDoc.document.open();
		wimpyDoc.document.write(text2);
		wimpyDoc.document.close();
		
	}
	

}
//*/


function writeit(text,id){
	if (document.getElementById) {
		var wimpyDoc = document.getElementById(id);
		wimpyDoc.innerHTML = '';
		wimpyDoc.innerHTML = text;
	} else if (document.all) {
		var wimpyDoc = document.all[id];
		wimpyDoc.innerHTML = text;
	} else if (document.layers) {
		var wimpyDoc = document.layers[id];
		text2 = '<P CLASS="testclass">' + text + '</P>';
		wimpyDoc.document.open();
		wimpyDoc.document.write(text2);
		wimpyDoc.document.close();
	}
}


function writeitAppend(text,id){
	if (document.getElementById) {
		var wimpyDoc = document.getElementById(id);
		wimpyDoc.innerHTML += text;
	} else if (document.all) {
		var wimpyDoc = document.all[id];
		wimpyDoc.innerHTML += text + "<br>";
	} else if (document.layers) {
		var wimpyDoc = document.layers[id];
		text2 += text  + "<br>";
		wimpyDoc.document.open();
		wimpyDoc.document.write(text2);
		wimpyDoc.document.close();
	}
}
