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
$iniform="";
$endform="";
$notmsg="Load file...";
$optfrm="style='opacity:0.6' disabled";
$extrastyle="margin-left:10px;margin-right:10px;";
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
$hashpath=md5($fpath);
$id="file_$hashpath";

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
     element.innerHTML='Loading';
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
   //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
   //DIRECTORY
   //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
   $ftable=filesTable("$Dir/$File","","Parent");
$fcontent.=<<<CONTENT
$ftable
CONTENT;
   break;

 case "TEXT":
   //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
   //TEXT FILE
   //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$fcontent.=<<<CONTENT
<div class="actionbutton">
  <a href="JavaScript:void(null)" onclick="$ajax_file">
    $BUTTONS[Update]
  </a>
</div>
<div class="actionbutton">
  <a href="$Dir/$File">Download</a> 
</div>
CONTENT;

   if($Mode=="Edit"){
     $iniform="<form action='?' method='get' enctype='multipart/form-data'>";
     $endform="";

     $optfrm="";
$fcontent.=<<<CONTENT
<div class="actionbutton">
  <a href="?$PHP[QSTRING]&Mode=View">View</a>
</div>
<textarea id="filecontent" class="filearea" name="FileContent"></textarea>
CONTENT;
   }else{
$fcontent.=<<<CONTENT
<div class="actionbutton">
  <a href="?$PHP[QSTRING]&Mode=Edit">Edit</a>
</div>
<div id="filecontent" class="plainarea"></div>
CONTENT;
   }

 break;
 case "IMAGE":
   //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
   //IMAGE FILE
   //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$fcontent.=<<<CONTENT
<div class="actionbutton">
  <a href="JavaScript:void(null)" onclick="$ajax_file">
    $BUTTONS[Update]
  </a>
</div>
<div class="actionbutton">
  <a href="$Dir/$File">Download</a> 
</div>
CONTENT;

$fcontent.=<<<CONTENT
<div id="filecontent" class="imgarea"></div>
CONTENT;
 break;

 case "TGZ":
   //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
   //TARBALL
   //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
   $tmpdir="$PROJ[TMPDIR]/dir-$File-$PHP[SESSID]";
   $tmppath="$PROJ[TMPPATH]/dir-$File-$PHP[SESSID]";
   systemCmd("mkdir -p $tmppath");
   systemCmd("echo '*' > $tmppath/.s2wfiles");
   systemCmd("tar -zxvf $fpath -C $tmppath");
   $ftable=filesTable("$tmpdir","","Parent");
$fcontent.=<<<CONTENT
$ftable
CONTENT;
   break;

 default:
   //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
   //OTHER FILE
   //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
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
//CONTENT
//==================================================
divBlanketOver($id);
/*
$cmd="cd $PHP[ROOTPATH]/$Dir;stat '$File'";
$cmd="cd $PHP[ROOTPATH]/$Dir && ls -ld /tmp/a";
$metadata=systemCmd($cmd,true);
*/
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
<!--
<div class="tabbertab sectab">
  <h2>Metadata</h2>
  Stat
  <div class="plainarea">
  $metadata
  </div>
</div>
-->
CONTENT;
if(!isset($PHP["HeightWindow"])){
  $PHP["HeightWindow"]="77%";
}
$extrastyle.="height:$PHP[HeightWindow];";
$content.="<input type='hidden' name='HeightWindow' value='$PHP[HeightWindow]'";

//==================================================
//FOOTER
//==================================================
$footer.=<<<FOOTER
FOOTER;

//==================================================
//HEADER
//==================================================
$back="";
if(preg_match("/file\.php/",$PHP[REFERER])){
$back=<<<BACK
  <div class="actionbutton">
    <a href="$PHP[REFERER]" class="image" 
       onmouseover="explainThis(this)" explanation="Back">
      $BUTTONS[Back]
    </a>
  </div>
BACK;
}

$header.=<<<HEADER
<!-- -------------------- RUN NAME -------------------- -->
<div class="actionbutton">
  <span style="font-size:18px">
    <b>File</b>: 
    <input type="ReName" value="$File" $optfrm>
  </span>
</div>
<div class="actionbutton">
  <button name="Action" value="Save" $optfrm>
    $BUTTONS[Save]
  </button> 
</div>
<div class="actionbutton">
  <a href="$PHP[Dir]/$PHP[File]" class="image" 
     onmouseover="explainThis(this)" explanation="Download">
    $BUTTONS[Down]
  </a>
</div>
<div class="actionbutton"
     style="position:absolute;right:0px;top:10px;">
  $back
  <div class="actionbutton">
    <a href="JavaScript:void(null)" class="image" 
       onclick="window.location.reload()"
       onmouseover="explainThis(this)" explanation="Reload">
      $BUTTONS[Update]
    </a>
  </div>
  <div class="actionbutton">
    <a href="JavaScript:void(null)" class="image" onclick="window.close()">
      $BUTTONS[Cancel]
    </a>
  </div>
</div>
HEADER;

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
    $iniform
      <input type="hidden" name="Dir" value="$Dir">
      <input type="hidden" name="File" value="$File">
      <input type="hidden" name="Mode" value="$Mode">
      <div style="position:relative">
	<!-- -------------------------------------------------------- -->
	<!-- NOTIFICATION AREA -->
	<!-- -------------------------------------------------------- -->
	<div style="position:relative">
	  <div id="notfile" class="notification" style="display:none"></div>
	  $PROJ[ELBLANKET]
	  $PROJ[ELOVER]  
	  $notification
	</div>
	<!-- -------------------------------------------------------- -->
	<!-- HEADER AREA -->
	<!-- -------------------------------------------------------- -->
	<div style="position:relative;padding:10px">
	  $header
	</div>
	<!-- -------------------------------------------------------- -->
	<!-- TABS AREA -->
	<!-- -------------------------------------------------------- -->
	<div class="tabber" id="0"
	     style="$extrastyle">
	  $content
	</div>
	<!-- -------------------------------------------------------- -->
	<!-- FOOTER AREA -->
	<!-- -------------------------------------------------------- -->
	<div style="position:relative">
	  $DEBUG
	  $footer
	</div>
      </div>
    $endform
  </body>
</html>

CONTENT;
echo $content
//echo "<textarea>$content</textarea>";

?>
