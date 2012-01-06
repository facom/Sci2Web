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
# WEB PAGE
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
*/
//////////////////////////////////////////////////////////////////////////////////
//LOAD PACKAGE
//////////////////////////////////////////////////////////////////////////////////
$RELATIVE=".";
$PAGETITLE="Sci2Web Server Site";
include("$RELATIVE/lib/sci2web.php");

//////////////////////////////////////////////////////////////////////////////////
//CHECK LOGIN
//////////////////////////////////////////////////////////////////////////////////
checkAuthentication();

//////////////////////////////////////////////////////////////////////////////////
//PARTICULAR CONFIGURATION
//////////////////////////////////////////////////////////////////////////////////
$cpath="$PROJ[PAGESPATH]/$PHP[PAGEBASENAME]";
$cdir="$PROJ[PAGESDIR]/$PHP[PAGEBASENAME]";
require_once("$cpath/page.conf");
if(!isset($PHP["TabId"])) $PHP["TabId"]=$DEFTAB-1;
else $PHP["TabId"]--;
/*
if(!isset($_SESSION["User"])){
  $PHP["TabId"]=0;
}
*/
$PHP["TabNum"]=$PHP["TabId"]+1;

//////////////////////////////////////////////////////////////////////////////////
//COMPONENTS
//////////////////////////////////////////////////////////////////////////////////
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//PERLIMINARIES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$head="";
$head.=genHead("","");

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//HEADER
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$header=genHeader("$PROJ[IMGDIR]/$PROJ[MAINLOGO]");

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//BODY DECLARATION
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$body="";
$body.=<<<BODY
<body>
BODY;

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//CONTENT
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$content="";

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//FOOTER
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$footer="";
$footer.=genFooter();

//////////////////////////////////////////////////////////////////////////////////
//GENERATE CONTENT
//////////////////////////////////////////////////////////////////////////////////
$DEBUG=genDebug();
echo<<<CONTENT
<html>
<head>
$head
</head>
$body
<!-- ---------------------------------------------------------------------- -->
<!-- HEADER								    -->
<!-- ---------------------------------------------------------------------- -->
$header
<!-- ---------------------------------------------------------------------- -->
<!-- CONTENT								    -->
<!-- ---------------------------------------------------------------------- -->
<div class="tabber maintabber" id="$PHP[TabId]">
  $DEBUG

CONTENT;

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//GET LIST OF FILES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$files=preg_split("/;/",$PROJ["MAINTABS"]);

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//DISPLAY THE CONTENT OF EACH FILE
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$i=1;
foreach($files as $file)
{
  if(isBlank($file)) continue;

  //GET INDEX VALUE
  list($fname,$fid)=preg_split("/:/",$file);
  $file="$fname.php";

  //CREATE THE MAIN PAGE TAB CONTENT THE FIRST TIME
  if(!file_exists("$PROJ[PROJPATH]/pages/main/$file")){
    systemCmd("cp -rf $PROJ[PROJPATH]/doc/install/$file $PROJ[PROJPATH]/pages/main");
    systemCmd("cp -rf $PROJ[PROJPATH]/doc/install/*.html $PROJ[PROJPATH]/pages/main/content");
  }

  //LOAD THE CONTENT
  $imgload=genLoadImg("animated/loader-circle.gif");
$ajaxcmd=<<<AJAX
loadContent
  (
   '$cdir/$file?$PHP[QSTRING]&TabNum=$i',
   '$fid',
   function(element,rtext){
     $(element).html(rtext);
     $(element).css('text-align','left');
   },
   function(element,rtext){
     $(element).html('$imgload');
     $(element).css('text-align','center');
   },
   null,
   -1,
   true
   )
AJAX;
   blankFunc();
   $onload=genOnLoad($ajaxcmd,'load');
   //echo "<textarea>$onload</textarea>";br();
   //exit(0);

   //BUILT THE CONTENT
echo <<<CONTENT
  $onload
  <div class="tabbertab maintab" id="Tab$i">
  <h2>$fid</h2>
  <div class="tabcontent" id="$fid"></div>
  </div>
CONTENT;
  $i++;
}
$TabNum=$i-1;
echo<<<CONTENT
</div>
<div id="CtrlTabId" value="$PHP[TabNum]"></div>
<div id="CtrlTabNum" value="$TabNum"></div>
$footer
</body>
</html>

CONTENT;
//////////////////////////////////////////////////////////////////////////////////
//FINALIZE
//////////////////////////////////////////////////////////////////////////////////
finalizePage();
?>
