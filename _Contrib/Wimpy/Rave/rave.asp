<%@ Language=VBScript EnableSessionState=False %>
<%
Option Explicit
Response.buffer = True
dim flashCode,filename,useMysql,smallName,AsmallName,AmyWimpyASPfilename,line_end,check4config,striphackretval,objLst,root,makeConfigFile_ret,printMySkin,sendback,useTptBkgd,useSkin,displayHeight,displayWidth,myConfigFile,getSkinSelector,writeSkinSelector_ret,GetDirArrayretval,mgrReq,defaultVisualBaseName,bkgdColor,scrollInfoDisplay,tptBkgd,forceXMLplaylist,randomOnLoad,shuffleOnLoad,defaultImage,voteScript,trackPlays,wimpyApp,wimpySwf,WIMPY_CONFIGS,useConfigFile,objXML,randomButtonState,startOnTrack,autoAdvance,bufferAudio,skin_width,skin_height,strFileToDownload,strFileContents,mySkin,wimpySkin,useSysCodePage,serveMP3
dim newline,ecomWindow,strAbsFile,strFileExtension,objFile,Ahide_files,hide_files,visualFound,j,totalAmyItems,items,encodedstring,objStream,sMIME,AdownloadFileName,downloadFileName,newLocation,saveFileContents,totalAitems,writeThisHeader,Aitems,saveResults,tf,fso,myFileName,myDirPath,HTMLcode,QueryString,playerH,playerW,playerSize_value,testing,destination,theDataIN,Aeachitem,Afile,strRight,intLoop,strIn,strOut,intPos,strLeft,sFile,AfileNameOnlyMyVis,fileNameOnlyVis,visualfilenameA,visualfilenameB,defaultVisualExt,defaultVisualName,forceDownload,hide_folders,defaultPlaylistFilename,myDataSetup,pwd,user,table,db,host
dim tptParamString,randomPlayback,theVolume,popUpHelp,startPlayingOnload,defaultPlayRandom,infoDisplayTime,displayDownloadButton,background_color,wimpyHTMLpageTitle,playlisterOutputDirName,getMyid3info,action,theFile,filenameuseMysql,queryValue,queryWhere,myQueryString,dir,arrGenre,objMP3Directory,objMP3,retval,strComment,intGenre,objAspPage,file_system,WIMPY_PATH,myWimpyASPfilename,Apath,Adatasetup,Ahide_folders,myCurrentWWWpath,countFiles,AtheFile,theFileName,tempPath,x,objFSO,objFSOwriteText,objFSOdownload,AmyDirs,myDirs,fileInfo,fileNameOnly,displayMe,myDirsSort,sortCount,myFilesSort,i,try,k
dim tptEmbedString,myFileInfo,AmyDirsSorted,mgrCode,myInfo,data,AmyExt,myExt,bob,Amgr_files_playlist,Amgr_files_playlist_spacer,Amgr_files_skin,Amgr_files_skin_spacer,newString,b,doit,letter,char,Amedia_types,skipfile,myDay,myDays,myMonthmyYear,myHours,myMonths,mySeconds,myMinutes,podder_title,podder_description,contactEmail,theDate,domainName,IP_title,IP_description,IP_category,IP_webMaster,IP_managingEditor,IP_generator,IP_docs,IP_lastBuildDate,IP_pubDate,IP_language,IP_link,searchLocations,AsearchLocations,fs,getMgrCoderetval,value,myCurrentWWWpath_mem,Amgr_files_skin_SPLIT,Amgr_files_playlist_SPLIT,bkgd_main_list,theWidth,theHeight,reval,getData
dim myFile,myWimpyJSfilename,coverartBasename,wimpyJS,defaultWidth,defaultHeight,wimpySkinFilepath,myPathInstall,httpOption,startDir,loopPlaylist,wimpyVersion,wimpyConfigFile,myWimpySWFfilename,wimpy_auth,media_types,mediaExtMp3,myCodePage,useCustomCharset,myCharSet,RS
Response.Clear
'//<//////////////////////////////////////////////////////////////
'//                                                             //
'//                                                             //
'//                                                             //
'//                                                             //
'//						   Wimpy Rave                           //
'//                                                             //
'//           by Mike Gieson <info@wimpyplayer.com>             //
'//          available at http://www.wimpyplayer.com            //
'//                     2002-2007 plaino                       //
'//                                                             //
'//                                                             //
'//                                                             //
'/////////////////////////////////////////////////////////////////
'//                                                             //
'//                      INSTALLATION:                          //
'//                                                             //
'/////////////////////////////////////////////////////////////////
'// 
'// Upload wimpy.php and rave.swf to the folder that contains your mp3, flv, aac, swf or h264 compressed media's.
'// 
'// USE AT YOUR OWN RISK.
'//
wimpyVersion = "v1.0.0"
wimpyConfigFile = "raveConfigs.xml"
myWimpySWFfilename = "rave.swf"
myWimpyJSfilename = "rave.js"
media_types = "flv,mp4,3gp,m4a,m4a,m4p,aac,mp3,swf,xml,m3u,pls"
mediaExtMp3 = "mp3"
defaultWidth = "250"
defaultHeight = "290"
httpOption = "http"
coverartBasename = "coverart.jpg"
'//
'// Character Mapping
'// If you are experiencing problems displaying glyphs and other 
'// double-byte (multibyte) characters, set myCharSet
'// for your language's characters set here.
'// 
'// For more information on "codepage" see:
'// http://msdn.microsoft.com/library/default.asp?url=/library/en-us/iissdk/html/268f1db1-9a36-4591-956b-d7269aeadcb0.asp
'// 
'// For more information on "CharSet" see:
'// http://msdn.microsoft.com/library/default.asp?url=/workshop/author/dhtml/reference/charsets/charset4.asp
'// 
'// In order to use a custom character mapping, you must "useCustomCharset" to "yes"
'// 
'// Example:
'// useCustomCharset = "yes"
'// 
useCustomCharset = "no"
myCodePage = 65001
myCharSet = "uft-8"
'//
'/////////////////////////////////////////////////////////////////
'//                                                             //
'//         Do not edit anything below here unless              //
'//          you really know what you are doing!                //
'//                                                             //
'/////////////////////////////////////////////////////////////////
'/////////////////////////////////////////////////////////////////
'/////////////////////////////////////////////////////////////////
'/////////////////////////////////////////////////////////////////
'/////////////////////////////////////////////////////////////////
myDataSetup = "filename|artist|album|title|track|comments|genre|seconds|filesize|bitrate|visual"
newline = VBNewLine
set file_system = CreateObject("Scripting.FileSystemObject")
set WIMPY_PATH = CreateObject("Scripting.Dictionary")
WIMPY_PATH.add "physical", file_system.getparentfoldername(request.servervariables("PATH_TRANSLATED"))
myPathInstall = WIMPY_PATH("physical")
WIMPY_PATH.add "www", httpOption & "://" & request.servervariables("SERVER_NAME") & file_system.getparentfoldername(request.servervariables("PATH_INFO"))
AmyWimpyASPfilename = split(request.servervariables("PATH_INFO"), "/")
myWimpyASPfilename = AmyWimpyASPfilename(Ubound(AmyWimpyASPfilename))

set WIMPY_CONFIGS = CreateObject("Scripting.Dictionary")
set check4config = CreateObject("Scripting.FileSystemObject")
useConfigFile = false

If check4config.FileExists((WIMPY_PATH("physical") & "\" & wimpyConfigFile)) Then
	Set objXML = Server.CreateObject("Microsoft.XMLDOM")

	objXML.async = False
	objXML.Load (WIMPY_PATH("physical") & "\" & wimpyConfigFile)

	If objXML.parseError.errorCode <> 0 Then
		useConfigFile = false
	else
		useConfigFile = true
	end if

	if useConfigFile = true then
		Set root = objXML.documentElement
		Set objLst = root.getElementsByTagName("*")
		if objLst.length > 4 then
			For i = 0 to (objLst.length-1)
				WIMPY_CONFIGS.add objLst.item(i).nodeName, objLst.item(i).text
			Next
		end if
	end if
end If


set check4config = nothing


If WIMPY_CONFIGS.Exists("wimpySwf") = false Then
	WIMPY_CONFIGS.add "wimpySwf", WIMPY_PATH("www") & "/" &  myWimpySWFfilename
end if
If WIMPY_CONFIGS.Exists("wimpyApp") = false Then
	WIMPY_CONFIGS.add "wimpyApp", WIMPY_PATH("www") & "/" &  myWimpyASPfilename
end if
If WIMPY_CONFIGS.Exists("wimpySkin") = false Then
	WIMPY_CONFIGS.add "wimpySkin", ""
end If
If WIMPY_CONFIGS.Exists("wimpyJS") = false Then
	WIMPY_CONFIGS.add "wimpyJS", WIMPY_PATH("www") & "/" &  myWimpyJSfilename
end if
If WIMPY_CONFIGS.Exists("tptBkgd") = false Then
	WIMPY_CONFIGS.add "tptBkgd", ""
end if
If WIMPY_CONFIGS.Exists("bkgdColor") = false Then
	WIMPY_CONFIGS.add "bkgdColor", "000000"
end if
If WIMPY_CONFIGS.Exists("startDir") = false Then
	WIMPY_CONFIGS.add "startDir", ""
end if
If WIMPY_CONFIGS.Exists("hide_folders") = false Then
	WIMPY_CONFIGS.add "hide_folders", "goodies,playlister_output,skins,getid3,_private,_private,_vti_bin,_vti_cnf,_vti_pvt,_vti_txt,cgi-bin"
Else
	WIMPY_CONFIGS("hide_folders") = "goodies,playlister_output,skins,getid3,_private,_private,_vti_bin,_vti_cnf,_vti_pvt,_vti_txt,cgi-bin," & WIMPY_CONFIGS("hide_folders")
end if
If WIMPY_CONFIGS.Exists("hide_files") = false Then
	WIMPY_CONFIGS.add "hide_files", "rave.swf,raveConfigs.xml,skin.xml,wimpyConfigs.xml,wimpyAVConfigs.xml,wimpy.swf,wimpyAV.swf,wasp.swf,wimpy_button.swf"
Else
	WIMPY_CONFIGS("hide_files") = "rave.swf,raveConfigs.xml,skin.xml,wimpyConfigs.xml,wimpyAVConfigs.xml,wimpy.swf,wimpyAV.swf,wasp.swf,wimpy_button.swf," & WIMPY_CONFIGS("hide_files")
end if
If WIMPY_CONFIGS.Exists("wimpyHTMLpageTitle") = false Then
	WIMPY_CONFIGS.add "wimpyHTMLpageTitle", "Wimpy Player"
end if
If WIMPY_CONFIGS.Exists("getMyid3info") = false Then
	WIMPY_CONFIGS.add "getMyid3info", ""
end if
If WIMPY_CONFIGS.Exists("displayWidth") = false Then
	WIMPY_CONFIGS.add "displayWidth", 0
end If
If WIMPY_CONFIGS.Exists("displayHeight") = false Then
	WIMPY_CONFIGS.add "displayHeight", 0
end If
If WIMPY_CONFIGS.Exists("coverartBasename") = false Then
	WIMPY_CONFIGS.add "coverartBasename", coverartBasename
end if

wimpySwf = WIMPY_CONFIGS("wimpySwf")
wimpyJS = WIMPY_CONFIGS("wimpyJS")
wimpyApp = WIMPY_CONFIGS("wimpyApp")
tptBkgd = WIMPY_CONFIGS("tptBkgd")
bkgdColor = WIMPY_CONFIGS("bkgdColor")
startDir = WIMPY_CONFIGS("startDir")
hide_folders = WIMPY_CONFIGS("hide_folders")
hide_files = WIMPY_CONFIGS("hide_files")
wimpyHTMLpageTitle = WIMPY_CONFIGS("wimpyHTMLpageTitle")
getMyid3info = WIMPY_CONFIGS("getMyid3info")
wimpySkin = WIMPY_CONFIGS("wimpySkin")
coverartBasename = WIMPY_CONFIGS("coverartBasename")

defaultVisualBaseName = left(coverartBasename,InStrRev(coverartBasename, ".")-1)
defaultVisualExt = mid(coverartBasename, InStr(coverartBasename, ".")+1, Len(coverartBasename))


useSkin = true

' // Only get Skin Info if needed
if IsEmpty(Request.QueryString("action")) then
	if len(wimpySkin)>4 then
		'Set objXML = Server.CreateObject("Microsoft.XMLDOM")
		Set objXML = Server.CreateObject("MSXML2.DOMDocument")
		objXML.setProperty "ServerHTTPRequest", true
		objXML.async = False
		objXML.Load (wimpySkin)
		' The following was used when startdir was processed before this block
		'tempPath = WIMPY_PATH("www") & "/"
		'wimpySkinFilepath = myPathInstall & "\" & Replace(Replace(wimpySkin, tempPath, ""), "/", "\")
		'objXML.Load (wimpySkinFilepath)
		If NOT objXML.parseError.errorCode <> 0 Then
			Set root = objXML.getElementsByTagName("bkgd_main")
			displayWidth = root.item(0).getAttribute("width")
			displayHeight = root.item(0).getAttribute("height")
			useSkin = true
		end if
		set root = nothing
		set objXML = nothing
	else
		useSkin = false
	end if
	if displayWidth < 1 or displayHeight < 1 then
		useSkin = false
		displayWidth = defaultWidth
		displayHeight = defaultHeight
	end if
end If

' // Startdir must be processed after looking for skin file
if NOT startDir = "" Then
	WIMPY_PATH("physical") = startDir
	'Response.write WIMPY_PATH("physical")
end if

Ahide_files = Split(hide_files, ",")
Ahide_folders = Split(hide_folders, ",")
arrGenre = Split("Blues,Classic Rock,Country,Dance,Disco,Funk,Grunge,Hip-Hop,Jazz,Metal,New Age,Oldies,Other,Pop,R&B,Rap,Reggae,Rock,Techno,Industrial,Alternative,Ska,Death Metal,Pranks,Soundtrack,Euro-Techno,Ambient,Trip-Hop,Vocal,Jazz+Funk,Fusion,Trance,Classical,Instrumental,Acid,House,Game,Sound Clip,Gospel,Noise,Altern Rock,Bass,Soul,Punk,Space,Meditative,Instrumental Pop,Instrumental Rock,Ethnic,Gothic,Darkwave,Techno-Industrial,Electronic,Pop-Folk,Eurodance,Dream,Southern Rock,Comedy,Cult,Gangsta,Top 40,Christian Rap,Pop/Funk,Jungle,Native American,Cabaret,New Wave,Psychadelic,Rave,Showtunes,Trailer,Lo-Fi,Tribal,Acid Punk,Acid Jazz,Polka,Retro,Musical,Rock & Roll,Hard Rock,Folk,Folk/Rock,National Folk,Swing,Bebob,Latin,Revival,Celtic,Bluegrass,Avantgarde,Gothic Rock,Progressive Rock,Psychedelic Rock,Symphonic Rock,Slow Rock,Big Band,Chorus,Easy Listening,Acoustic,Humour,Speech,Chanson,Opera,Chamber Music,Sonata,Symphony,Booty Bass,Primus,Porn Groove,Satire,Slow Jam,Club,Tango,Samba,Folklore", ",")
Adatasetup = split(myDataSetup, "|")

function convertURL2filepath(theURL)
	tempPath = WIMPY_PATH("www") & "/"
	convertURL2filepath = WIMPY_PATH("physical") & "\" & Replace(Replace(theURL, tempPath, ""), "/", "\")
end function

'function convertFilepath2URL(theFilePath)
	'tempPath = WIMPY_PATH("physical") & "\"
	'tempPath = Replace(theFilePath, tempPath, "")
	'convertFilepath2URL = WIMPY_PATH("www") & "/" & Replace(tempPath, "\", "/")
'end Function

function convertFilepath2URL(theFilePath)
	Dim normFP, normSYS
	normFP = Replace(theFilePath, "\", "/")
	normSYS = Replace(WIMPY_PATH("physical") & "\", "\", "/")
	tempPath = Replace(normFP, normSYS, "")
	convertFilepath2URL = WIMPY_PATH("www") & "/" & Replace(tempPath, "\", "/")
end function

function convertURL2siteRoot(theURL)
	tempPath = WIMPY_PATH("www") & "/"
	convertURL2siteRoot = Replace(theURL, tempPath, "")
end function

function ascii2hex(theString_in)
	newString = ""
	b = 0
	for i=0 to len(theString_in)-1
		b = b + 1
		doit = false
		letter = mid(theString_in, b, 1)
		char = Asc(letter)
		if char < 46 then
			bob = "%" & Hex(char)
		elseif char > 58 AND char < 65 then
			bob = "%" & Hex(char)
		elseif char > 90 AND char < 97 then
			bob = "%" & Hex(char)
		else
			bob = letter
		end if
		newString = newString & bob
	next

	ascii2hex = newString
end function

function ConvertTheBinary(thebytes)
	For x = 1 to LenB(thebytes)
		If AscB(MidB(thebytes, x, 1)) <> 0 Then
			ConvertTheBinary = ConvertTheBinary & Chr(AscB(MidB(thebytes, x, 1)))
		End If
	Next
End Function


Dim basename
function GetDirArray(sPath)
	redim AmyFiles(0)
	redim myFileInfo(Ubound(Adatasetup))
	' //
	' //
	Set objFSO = CreateObject("Scripting.FileSystemObject")
	'response.write sPath
	Set objMP3Directory = objFSO.GetFolder(sPath)
	Set objMP3 = Server.CreateObject("ADODB.Stream")  
	objMP3.Type = 1
	'File Size
	'File Name
	'Title
	'Artist
	'Album
	'Year
	'Comment
	'Track
	'Genre
	'Tag


	visualfilenameA = myCurrentWWWpath & "/" & defaultVisualBaseName & "." & defaultVisualExt
	visualfilenameB = convertURL2filepath(visualfilenameA)
	If objFSO.FileExists(visualfilenameB) Then
		if not startDir = "" then
			visualfilenameA = WIMPY_PATH("www") & "/" & myWimpyASPfilename & "?action=getCoverart&theFile=" & visualfilenameA
		end if
		GetDirArrayretval = "<playlist image=""" & visualfilenameA & """>" & newline
	else 
		GetDirArrayretval = "<playlist>" & newline
	End If


	Set AmyDirs = objMP3Directory.SubFolders
	
	' =========================================
	' Folders
	' =========================================
	For Each myDirsSort in AmyDirs
		basename = myDirsSort.Name
		displayMe = true
		if Len(join(Filter(Ahide_folders, basename), "")) > 0 then
			displayMe = false
		end if
		if InStr(LCase(myDirs), "skin") > 0 then
			displayMe = false
		end if
		if displayMe = true then
			GetDirArrayretval = GetDirArrayretval & "	<item>" & newline
			GetDirArrayretval = GetDirArrayretval & "		<filename>" & myCurrentWWWpath & "/" & basename & "</filename>" & newline
			GetDirArrayretval = GetDirArrayretval & "		<artist>" & basename & "</artist>" & newline
			GetDirArrayretval = GetDirArrayretval & "		<title>" & basename & "</title>" & newline

			visualfilenameA = myCurrentWWWpath & "/" & basename & "/" & defaultVisualBaseName & "." & defaultVisualExt
			visualfilenameB = convertURL2filepath(visualfilenameA)
			If objFSO.FileExists(visualfilenameB) Then
				if not startDir = "" then
					visualfilenameA = WIMPY_PATH("www") & "/" & myWimpyASPfilename & "?action=getCoverart&theFile=" & visualfilenameA
				end if
				GetDirArrayretval = GetDirArrayretval & "		<image>" & visualfilenameA & "</image>" & newline
			else 
				GetDirArrayretval = GetDirArrayretval & "		<image></image>" & newline
			End If



			GetDirArrayretval = GetDirArrayretval & "		<date>" & formatDate(myDirsSort.DateLastModified) & "</date>" & newline
			GetDirArrayretval = GetDirArrayretval & "		<filekind>dir</filekind>" & newline
			GetDirArrayretval = GetDirArrayretval & "	</item>" & newline

		end if
	Next

	Amedia_types = split(media_types, ",")

	For Each myFile in objMP3Directory.Files
		if Not LCase(myFile.name) = LCase(join(Filter(Ahide_files, myFile.name, true), "")) Then
			if LCase(objFSO.GetExtensionName(myFile)) = LCase(join(Filter(Amedia_types, LCase(objFSO.GetExtensionName(myFile)), true), "")) then
				set fileInfo = CreateObject("Scripting.Dictionary")
				fileNameOnly = myFile.name
				
				basename = left(fileNameOnly,InStrRev(fileNameOnly, ".")-1)
				skipfile = false
				fileInfo.add "filename",  myCurrentWWWpath & "/" & fileNameOnly
				if LCase(objFSO.GetExtensionName(myFile)) = LCase(mediaExtMp3) then
					If getMyid3info	= "yes"	Then
						fileInfo.add "filesize", Round((myFile.Size * .000001), 2)
						objMP3.Open
						objMP3.LoadFromFile myFile.Path
						objMP3.Position = objMP3.Size - 128
						If UCase(ConvertTheBinary(objMP3.Read(3))) = "TAG" Then
							fileInfo.add "title", ConvertTheBinary(objMP3.Read(30))
							fileInfo.add "artist", ConvertTheBinary(objMP3.Read(30))
							fileInfo.add "album", ConvertTheBinary(objMP3.Read(30))
							fileInfo.add "track", ConvertTheBinary(objMP3.Read(4))
							strComment = objMP3.Read(30)
							If AscB(MidB(strComment, 29, 1)) = 0 AND AscB(MidB(strComment, 30, 1)) > 0 AND AscB(MidB(strComment, 30, 1)) < 256 Then
								'strTag = "ID3v1.1"
								id3Comments = ConvertTheBinary(LeftB(strComment, 28))
							Else
								'strTag = "ID3v1"
								id3Comments = ConvertTheBinary(strComment)
							End If
							
							If Left(id3Comments, 4) = "http" then
								fileInfo.add "link", id3Comments
								fileInfo.add "description", ""
							Else
								fileInfo.add "link", ""
								fileInfo.add "description", id3Comments
							End if
						Else 
							fileInfo.add "artist", basename
							fileInfo.add "title", basename
						End If
						objMP3.Close
					else
						' Don't use ID3 info:
						fileInfo.add "artist", basename
						fileInfo.add "title", basename
					End If
				Else

					if InStr(LCase(myFile.name), "skin") > 0 then
						skipfile = true
					end if
					if InStr(LCase(myFile.name), "config") > 0 then
						skipfile = true
					end if
					if skipfile = false then
						fileInfo.add "artist", basename
						fileInfo.add "title", basename
					end if
					set root = nothing
					set objXML = nothing

				End If
				if skipfile = false then
					
					fileInfo.add "date", formatDate(myFile.DateLastModified)
					


					visualfilenameA = myCurrentWWWpath & "/" & basename & "." & defaultVisualExt
					visualfilenameB = convertURL2filepath(visualfilenameA)
					If objFSO.FileExists(visualfilenameB) Then
						if not startDir = "" then
							visualfilenameA = WIMPY_PATH("www") & "/" & myWimpyASPfilename & "?action=getCoverart&theFile=" & visualfilenameA
						end if
						fileInfo.add "image", visualfilenameA
					else 
						fileInfo.add "image", ""
					End If




					Dim myKeys
					myKeys = fileInfo.Keys
					GetDirArrayretval = GetDirArrayretval & "	<item>" & newline
					for i=0 to fileInfo.Count-1
						GetDirArrayretval = GetDirArrayretval & "		<" & myKeys(i) & ">" & fileInfo.Item(myKeys(i)) & "</" & myKeys(i) & ">" & newline
					next
					GetDirArrayretval = GetDirArrayretval & "	</item>" & newline

				end if
				set fileInfo = nothing
			end if
		end if
	Next

	Set objMP3 = Nothing
	Set objMP3Directory = Nothing
	Set objFSO = Nothing

	GetDirArray = GetDirArrayretval & "</playlist>"

end Function

Function lookForCoverart(theURL)
	Dim tempFSO
	Set tempFSO = CreateObject("Scripting.FileSystemObject")
	visualfilenameA = theURL
	visualfilenameB = convertURL2filepath(visualfilenameA)
	If tempFSO.FileExists(visualfilenameB) Then
		if not startDir = "" then
			visualfilenameA = WIMPY_PATH("www") & "/" & myWimpyASPfilename & "?action=getCoverart&theFile=" & visualfilenameA
		end if
		retval = visualfilenameA
	else 
		retval =  ""
	End If
	Set tempFSO = nothing
	lookForCoverart = retval
end Function

if IsEmpty(Request.QueryString("getMyid3info")) then
	getMyid3info = getMyid3info
else
	getMyid3info = Request.QueryString("getMyid3info")
end if
' **************************************************
if IsEmpty(Request.QueryString("action")) then
	action = ""
else
	action = Request.QueryString("action")
end if
' **************************************************
if IsEmpty(Request.QueryString("theFile")) then
	theFile = ""
else
	theFile = Request.QueryString("theFile")
end if
' **************************************************
if IsEmpty(Request.QueryString("filename")) then
	filename = ""
else
	filename = Request.QueryString("filename")
end if
' **************************************************
if IsEmpty(Request.QueryString("dir")) then
	dir = ""
else
	dir = Request.QueryString("dir")
end if
' **************************************************
if IsEmpty(Request.QueryString("useMysql")) then
	useMysql = ""
else
	useMysql = Request.QueryString("useMysql")
end if
' **************************************************
if IsEmpty(Request.QueryString("destination")) then
	destination = ""
else
	destination = Request.QueryString("destination")
end if
' **************************************************
if IsEmpty(Request.QueryString("items")) then
	items = ""
else
	items = Request.QueryString("items")
end if
' **************************************************
' mod: 3.0.4
'if IsEmpty(Request.QueryString("visualOverRide")) then
'	visualOverRide = ""
'else
'	visualOverRide = Request.QueryString("visualOverRide")
'end if
' **************************************************
if IsEmpty(Request.QueryString("queryValue")) then
	queryValue = ""
else
	queryValue = Request.QueryString("queryValue")
end if
' **************************************************
if IsEmpty(Request.QueryString("queryWhere")) then
	queryWhere = ""
else
	queryWhere = Request.QueryString("queryWhere")
end if
' **************************************************
'//
if not isEmpty(Request.QueryString("defaultVisualExt")) then
	defaultVisualExt = Request.QueryString("defaultVisualExt")
end If
'//
if not isEmpty(Request.QueryString("defaultVisualBaseName")) then
	defaultVisualBaseName = Request.QueryString("defaultVisualBaseName")
end if
' //
' //
' //
' //
' //
' //
' //
' //
Function URLDecode(byVal encodedstring)
	' //
	strIn  = encodedstring : strOut = _
		 "" : intPos = Instr(strIn, "+")
	Do While intPos
		strLeft = "" : strRight = ""
		If intPos > 1 then _
			strLeft = Left(strIn, intPos - 1)
		If intPos < len(strIn) then _
			strRight = Mid(strIn, intPos + 1)
		strIn = strLeft & " " & strRight
		intPos = InStr(strIn, "+")
		intLoop = intLoop + 1
	Loop
	intPos = InStr(strIn, "%")
	Do while intPos
		If intPos > 1 then _
			strOut = strOut & _
				Left(strIn, intPos - 1)
		strOut = strOut & _
			Chr(CInt("&H" & _
				mid(strIn, intPos + 1, 2)))
		If intPos > (len(strIn) - 3) then
			strIn = ""
		Else
			strIn = Mid(strIn, intPos + 3)
		End If
		intPos = InStr(strIn, "%")
	Loop
	URLDecode = strOut & strIn
End Function
' **************************************************
' **************************************************
' **************************************************
' **************************************************
' **************************************************
' **************************************************
' **************************************************
' **************************************************
Function formatDate(myDateIN)
	Dim myDate
	myDate = CDate(myDateIN)

	' Y-m-d H:i:s
	formatDate = Year(myDate) & _
				"-" & zeroPad(Month(myDate), 2) & _
				"-" & zeroPad(Day(myDate), 2) & _
				" " & zeroPad(Hour(myDate), 2) & _
				":" & zeroPad(Minute(myDate), 2) & _
				":" & zeroPad(Second(myDate), 2)

End Function 
Function zeroPad(m, t)
  zeroPad = String(t-Len(m),"0")&m
End Function
' //
' //
' //
' //
' Print XML node map
'dim s,a
'a = WIMPY_CONFIGS.Keys
'for i = 0 To WIMPY_CONFIGS.Count -1
'    s = s & a(i) & "<br>"
'next
'Response.Write(s)
mgrReq = false
' //
' //
' //
function striphack(theString)
	striphackretval = theString
	striphackretval = Replace(striphackretval, Chr(10), "x")
	striphackretval = Replace(striphackretval, Chr(13), "x")
	striphackretval = Replace(striphackretval, Chr(9), "x")
	striphackretval = Replace(striphackretval, "\", "x")
	striphackretval = Replace(striphackretval, "./", "x")
	striphackretval = Replace(striphackretval, "..", "x")
	striphack = striphackretval
end Function


function ListFolderContents(path, AextFilter, XMLkind)

	dim fs, folder, file, item, url, retval, useFile

	set fs = CreateObject("Scripting.FileSystemObject")
	set folder = fs.GetFolder(path)

	for each item in folder.SubFolders
		retval = retval & ListFolderContents(item.Path, AextFilter, XMLkind)
	next

	for each item in folder.Files
		if LCase(fs.GetExtensionName(item)) = LCase(join(Filter(AextFilter, LCase(fs.GetExtensionName(item)), true), "")) Then

			Set objXML = Server.CreateObject("Microsoft.XMLDOM")

			objXML.async = False
			objXML.Load (item.path)

			If objXML.parseError.errorCode <> 0 Then
				useFile = false
			else
				useFile = true
			end If
			
			if useFile = true then
				Set root = objXML.documentElement
				if root.nodeName = XMLkind Then
					retval = retval & "<item>" & convertFilepath2URL(item.path) & "</item>" & newline
				end if
			end If
			
		End If

	next

	ListFolderContents = retval

end Function

function printXML(theXML)
	Dim printThis
	printThis = "<?xml version=""1.0""  encoding=""UTF-8"" ?>" & newline

	Response.AddHeader "Pragma", "public"
	Response.AddHeader "Expires", "Thu, 19 Nov 1981 08:52:00 GMT"
	Response.AddHeader "Cache-Control", "must-revalidate, post-check=0, pre-check=0"
	Response.AddHeader "Cache-Control", "no-store, no-cache, must-revalidate"
	Response.AddHeader "Cache-Control", "private"
	Response.ContentType = "text/xml"

	Response.Write printThis & retval
End function
' //
' //
' //
' //
' //
' //
' //
' //
' //
If action = "getList" Then
	'If useCustomCharset="yes" then
		'Response.codepage = myCodePage
		'Response.CharSet = myCharSet
	'End if
	'myCurrentWWWpath = WIMPY_PATH("www")
	'retval = GetDirArray()
	Dim myExtCheck, Aext
	myExtCheck = "xml"
	Aext = split(myExtCheck, ",")
	retval = "<list>" & newline & ListFolderContents(WIMPY_PATH("physical"), Aext, Request.QueryString("theKind")) & newline & "</list>"
	printXML(retval)
	
elseif action="getVersion" Then
	Response.Write wimpyVersion
elseif action="dir" Then
	If useCustomCharset="yes" then
		Response.codepage = myCodePage
		Response.CharSet = myCharSet
	End if
	myCurrentWWWpath = dir
	retval = GetDirArray(convertURL2filepath(striphack(myCurrentWWWpath)))
	'retval = convertURL2filepath(striphack(myCurrentWWWpath))
	Response.Write retval
ElseIf action="getCoverart" Then
	'sFile = convertURL2filepath(striphack(theFile))
	AdownloadFileName = split(theFile, "/")
	downloadFileName = AdownloadFileName(Ubound(AdownloadFileName))
	sFile = WIMPY_PATH("physical") & "\" & Replace(theFile, WIMPY_PATH("www") & "/", "")
	'sFile = WIMPY_PATH("www") & "\" & Replace(theFile, "/", "\")
	strAbsFile = sFile
	'-- create FSO object to check if file exists and get properties
	Set objFSO = Server.CreateObject("Scripting.FileSystemObject")
	'-- check to see if the file exists
	If objFSO.FileExists(strAbsFile) Then
		Set objFile = objFSO.GetFile(strAbsFile)
			'-- first clear the response, and then set the appropriate headers
			'Response.Clear
			'-- the filename you give it will be the one that is shown
			'   to the users by default when they save
			Response.AddHeader "Pragma", "public"
			Response.AddHeader "Expires", "Thu, 19 Nov 1981 08:52:00 GMT"
			Response.AddHeader "Cache-Control", "must-revalidate, post-check=0, pre-check=0"
			Response.AddHeader "Cache-Control", "no-store, no-cache, must-revalidate"
			Response.AddHeader "Cache-Control", "private"
			'Response.AddHeader "Content-Disposition", "attachment; filename=" & Replace(objFile.Name, " ","%20")
			Response.AddHeader "Content-Length", objFile.Size
			Response.ContentType = "image/jpeg"
			Set objStream = Server.CreateObject("ADODB.Stream")
			objStream.Open
			'-- set as binary
			objStream.Type = 1
			If useCustomCharset="yes" then
				Response.codepage = myCodePage
				Response.CharSet = myCharSet
			End if
			'-- load into the stream the file
			objStream.LoadFromFile(strAbsFile)
			'-- send the stream in the response
			Response.BinaryWrite(objStream.Read)
			objStream.Close
			Set objStream = Nothing
		Set objFile = Nothing
	'Else  
	'objFSO.FileExists(strAbsFile)
		'Response.Clear
		'Response.Write("No such file exists.")
	End If
	Set objFSO = Nothing
ElseIf action="serveMP3" OR action="downloadfile" Then
	if not startDir = "" then
		'sFile = convertURL2filepath(striphack(theFile))
		' //
		AdownloadFileName = split(theFile, "/")
		downloadFileName = AdownloadFileName(Ubound(AdownloadFileName))
		' //
		sFile = WIMPY_PATH("physical") & "\" & Replace(theFile, WIMPY_PATH("www") & "/", "")
		' //
		'sFile = WIMPY_PATH("www") & "\" & Replace(theFile, "/", "\")
		'response.write sFile
		strAbsFile = sFile
	else
		sFile = convertURL2filepath(striphack(theFile))
		AdownloadFileName = split(theFile, "/")
		downloadFileName = AdownloadFileName(Ubound(AdownloadFileName))
		' //
		sFile = Replace(theFile, WIMPY_PATH("www") & "/", "")
		' //
		strAbsFile = Server.MapPath(sFile)
	end if
	'-- create FSO object to check if file exists and get properties
	Set objFSO = Server.CreateObject("Scripting.FileSystemObject")
	'-- check to see if the file exists
	If objFSO.FileExists(strAbsFile) Then
		Set objFile = objFSO.GetFile(strAbsFile)
			'-- first clear the response, and then set the appropriate headers
			'Response.Clear
			'-- the filename you give it will be the one that is shown
			'   to the users by default when they save
			Response.AddHeader "Pragma", "public"
			Response.AddHeader "Expires", "Thu, 19 Nov 1981 08:52:00 GMT"
			Response.AddHeader "Cache-Control", "must-revalidate, post-check=0, pre-check=0"
			Response.AddHeader "Cache-Control", "no-store, no-cache, must-revalidate"
			Response.AddHeader "Cache-Control", "private"
			if action="downloadfile" Then
				Response.AddHeader "Content-Disposition", ("attachment; filename=""" & objFile.Name & """")
			end if
			Response.AddHeader "Content-Length", objFile.Size
			Response.ContentType = "audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3"
			Set objStream = Server.CreateObject("ADODB.Stream")
			objStream.Open
			'-- set as binary
			objStream.Type = 1
			If useCustomCharset="yes" then
				Response.codepage = myCodePage
				Response.CharSet = myCharSet
			End if
			'-- load into the stream the file
			objStream.LoadFromFile(strAbsFile)
			'-- send the stream in the response
			Response.BinaryWrite(objStream.Read)
			objStream.Close
			Set objStream = Nothing
		Set objFile = Nothing
	End If
	Set objFSO = Nothing
ElseIf action="getstartupdirlist" Then
	If useCustomCharset="yes" then
		Response.codepage = myCodePage
		Response.CharSet = myCharSet
	End if
	myCurrentWWWpath = WIMPY_PATH("www")
	retval = GetDirArray(WIMPY_PATH("physical"))
	Response.ContentType = "text/xml"
	Response.Write retval
Else


	flashCode = "<html>" & newline
	flashCode = flashCode & "<head>" & newline
	flashCode = flashCode & "<title>" & wimpyHTMLpageTitle & "</title>" & newline
	flashCode = flashCode & "<script src=""" & WIMPY_PATH("www") & "/" & myWimpyJSfilename & """ type=""text/javascript""></script>" & newline
	flashCode = flashCode & "</head>" & newline
	flashCode = flashCode & "<body bgcolor=""#""" & bkgdColor & """ leftmargin=""0"" topmargin=""0"" marginwidth=""0"" marginheight=""0"">" & newline
	flashCode = flashCode & "<table width=""100%"" height=""100%"" border=""0"" cellpadding=""0"" cellspacing=""0"">" & newline
	flashCode = flashCode & "<tr>" & newline
	flashCode = flashCode & "<td align=""center"" valign=""middle"">" & newline
	flashCode = flashCode & "<!-- START WIMPY CODE -->" & newline
	flashCode = flashCode & "<div id=""flashcontent"">" & newline
	flashCode = flashCode & "<strong>You need to upgrade your Flash Player</strong>" & newline
	flashCode = flashCode & "</div>" & newline
	flashCode = flashCode & "<script language=""JavaScript"" >" & newline


	flashCode = flashCode & "// <![CDATA[" & newline
	flashCode = flashCode & "var wimpyConfigs070907 = new Object();" & newline
	flashCode = flashCode & "wimpyConfigs070907.wimpySwf=""" & wimpySwf & """;" & newline
	flashCode = flashCode & "wimpyConfigs070907.wimpyApp=""" & wimpyApp & """;" & newline
	flashCode = flashCode & "wimpyConfigs070907.wimpyWidth=""" & displayWidth & """;" & newline
	flashCode = flashCode & "wimpyConfigs070907.wimpyHeight=""" & displayHeight & """;" & newline
	If useConfigFile = True then
		flashCode = flashCode & "wimpyConfigs070907.wimpyConfigs=""" & WIMPY_PATH("www") & "/" & wimpyConfigFile & """;" & newline
	End if
	If useSkin = True then
		flashCode = flashCode & "wimpyConfigs070907.wimpySkin=""" & wimpySkin & """;" & newline
	End if
	flashCode = flashCode & "wimpyConfigs070907.autoAdvance=""no"";" & newline
	flashCode = flashCode & "makeWimpyPlayer(wimpyConfigs070907, ""flashcontent"");" & newline
	flashCode = flashCode & "// ]]>" & newline



	flashCode = flashCode & "</script>" & newline
	flashCode = flashCode & "<!-- END WIMPY CODE -->" & newline
	flashCode = flashCode & "</td>" & newline
	flashCode = flashCode & "</tr>" & newline
	flashCode = flashCode & "</table>" & newline
	flashCode = flashCode & "</body>" & newline
	flashCode = flashCode & "</html>" & newline




	Response.Write flashCode
End If
set file_system = nothing
set WIMPY_PATH = nothing
set WIMPY_CONFIGS = Nothing
Response.Flush
%>