<?
/*
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
#          _ ___               _						 #
#         (_)__ \             | |    						 #
# ___  ___ _   ) |_      _____| |__  						 #
#/ __|/ __| | / /\ \ /\ / / _ \ '_ \ 						 #
#\__ \ (__| |/ /_ \ V  V /  __/ |_) |						 #
#|___/\___|_|____| \_/\_/ \___|_.__/ 						 #
#JORGE ZULUAGA (C) 2011  							 #
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
# INTERACTIVE SCRIPT
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
*/
//////////////////////////////////////////////////////////////////////////////////
//LIBRARY
//////////////////////////////////////////////////////////////////////////////////
$RELATIVE="..";
$PAGETITLE="File";
include_once("$RELATIVE/lib/sci2web.php");

//////////////////////////////////////////////////////////////////////////////////
//GLOBAL VARIABLES
//////////////////////////////////////////////////////////////////////////////////
$header="";
$fcontent="";
$content="";
$footer="";
$notmsg="Load file...";
$optfrm="style='opacity:0.6' disabled";
$metadata="";

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//INPUT VARIABLES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
if(isset($PHP["Dir"])) $Dir=$PHP["Dir"];
else{
  echo "<p>Error: directory not provided</p>";
  return 0;
}

if(isset($PHP["File"])) $File=$PHP["File"];
else{
  echo "<p>Error: filename not provided</p>";
  return 0;
}

if(isset($PHP["Action"])) $Action=$PHP["Action"];
else $Action="Get";

if(isset($PHP["Mode"])) $Mode=$PHP["Mode"];
else{
  echo "<p>Error: mode not provided</p>";
  return 0;
}

$maxsize=30;
if(strlen($File)>$maxsize){
  $Fileshort=substr($File,0,$maxsize)."...";
}else{
  $Fileshort=$File;
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//SAVE FILE
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
if($Action=="Save"){
  require_once("$PROJ[BINPATH]/ajax-trans-file.php");
  $notmsg="File saved...";
  $Action="Get";
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//GET OBJECT ACCORDING TO TYPE
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$fdir="$Dir/$File";
$fpath="$PHP[ROOTPATH]/$fdir";
$ftype=filedType($fpath);
$id="file";

$ajax_file=<<<AJAX
loadContent
  (
   '$PROJ[BINDIR]/ajax-trans-file.php?Action=Get&Dir=$Dir&File=$File&Mode=$Mode',
   'filecontent',
   function(element,rtext){
     element.innerHTML=rtext;
     $('#DIVBLANKET$id').css('display','none');
     $('#DIVOVER$id').css('display','none');
   },
   function(element,rtext){
     $('#DIVBLANKET$id').css('display','block');
     $('#DIVOVER$id').css('display','block');
   },
   function(element,rtext){
     element.innerHTML='Error';
     $('#DIVBLANKET$id').css('display','none');
     $('#DIVOVER$id').css('display','none');
   },
   -1,
   true
   )
AJAX;
$onload_file=genOnLoad($ajax_file,'load');

switch($ftype){
 case "DIRECTORY":
   $ftable=filesTable("$Dir/$File");
$fcontent.=<<<CONTENT
$ftable
CONTENT;
   break;
 case "TEXT":
   if($Mode=="Edit"){
     $optfrm="";
$fcontent.=<<<CONTENT
<a href="$Dir/$File">Download</a> |
<a href="?$PHP[QSTRING]&Mode=View">View</a>
<textarea id="filecontent" class="filearea" name="FileContent"></textarea>
CONTENT;
   }else{
$fcontent.=<<<CONTENT
<a href="$Dir/$File">Download</a> |
<a href="?$PHP[QSTRING]&Mode=Edit">Edit</a>
<div id="filecontent"
     class="plainarea">
</div>
CONTENT;
   }
$fcontent.=<<<CONTENT
<div class="update" style="top:25px">
  <a href="JavaScript:void(null)" onclick="$ajax_file">
    $BUTTONS[Update]
  </a>
</div>
CONTENT;
 break;
 case "IMAGE":
$fcontent.=<<<CONTENT
<a href="$Dir/$File">Download</a>
<div id="filecontent" class="imgarea">
</div>
<div class="update" style="top:25px">
  <a href="JavaScript:void(null)" onclick="$ajax_file">
    $BUTTONS[Update]
  </a>
</div>
CONTENT;
 break;
 case "TGZ":
   $tmpdir="$PROJ[TMPDIR]/dir-$File-$PHP[SESSID]";
   $tmppath="$PROJ[TMPPATH]/dir-$File-$PHP[SESSID]";
   systemCmd("mkdir -p $tmppath");
   systemCmd("mkdir -p $tmppath/.root");
   systemCmd("tar -zxvf $fpath -C $tmppath");
   $ftable=filesTable("$tmpdir");
$fcontent.=<<<CONTENT
<a href="$Dir/$File">Download tarball</a>
$ftable
CONTENT;
   break;
 default:
$fcontent.=<<<CONTENT
<a href="$Dir/$File">Download</a>
<div id="filecontent"></div>
CONTENT;
   break;
 }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//GENERATE CONTENT
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//==================================================
//HEADER
//==================================================
$header.=<<<HEADER
<div id="notfile" class="notification" style="display:none"></div>
HEADER;

//==================================================
//CONTENT
//==================================================
divBlanketOver($id);
$cmd="cd $PHP[ROOTPATH]/$Dir;stat '$File'";
$metadata=systemCmd($cmd,true);
$content.=<<<CONTENT
<div class="tabbertab sectab">
  <h2>$Fileshort</h2>
  $onload_file
  <!-- --------------------------------------------------------------------- -->
  <!-- FILE AREA  							     -->
  <!-- --------------------------------------------------------------------- -->
  <div style="position:relative">
    $PROJ[DIVBLANKET]
    $PROJ[DIVOVER]
    $fcontent
  </div>
</div>
<div class="tabbertab sectab">
  <h2>Metadata</h2>
  Stat
  <div class="plainarea">
  $metadata
  </div>
</div>
CONTENT;

//==================================================
//FOOTER
//==================================================
$footer.=<<<FOOTER
<div class="formbuttons" id="buttons">
<button name="Action" value="Save" $optfrm>
  $BUTTONS[Save]
</button> 
</div>
<div class="close">
  <button class="image" onclick="window.close()">
    $BUTTONS[Cancel]
  </button>
</div>
FOOTER;

//////////////////////////////////////////////////////////////////////////////////
//CONTENT DISPLAY
//////////////////////////////////////////////////////////////////////////////////
end:
$notification=genOnLoad("notDiv('notfile','$notmsg')");
$DEBUG=genDebug();
$head="";
$head.=genHead("","");
$content=<<<CONTENT
<html>
<head>
$head
</head>

<body>
<form action="?" method="get" enctype="multipart/form-data">
<input type="hidden" name="Dir" value="$Dir">
<input type="hidden" name="File" value="$File">
<input type="hidden" name="Mode" value="$Mode">
$notification
$header

<div class="tabber sectabber" id="0">
$DEBUG
$content
</div>

$footer
</form>
</body>
</html>

CONTENT;
echo $content
//echo "<textarea>$content</textarea>";

?>
