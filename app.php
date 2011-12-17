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
$PAGETITLE="Sci2Web Application Page";
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

//////////////////////////////////////////////////////////////////////////////////
//COMPONENTS
//////////////////////////////////////////////////////////////////////////////////
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//PERLIMINARIES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$refresh="";
$head="";
if(isset($PHP["SaveContent"]) or
   isset($PHP["SetApp"])){
  $refresh=0;
}
$head.=genHead("$PROJ[PROJDIR]/$PHP[PAGENAME]",$refresh);
if($qexpire){
  $head.=genHead("$PROJ[PROJDIR]","0");
echo<<<CONTENT
<html>
$head
<body onload="alert('We are sorry, your session has expired or it has not started yet. Please login again.')">
</body>
</html>
CONTENT;
 return 0;
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//HEADER
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$header=genHeader($PAGELOGO);

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
$files=listFiles($cpath,"*.php");
if(count($files)==0){
echo<<<CONTENT
  <div class="tabbertab" id="maintab">
  <h2>Blank</h2>
  </div>

CONTENT;
}
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//DISPLAY THE CONTENT OF EACH FILE
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
readConfig("$PROJ[APPSPATH]/$_SESSION[App]/$_SESSION[VersionId]/sci2web/version.conf");
$files=preg_split("/;/",$CONFIG["VerTabs"]);

$i=1;
foreach($files as $file)
{
  if(isBlank($file)) continue;

  //GET INDEX VALUE
  list($fname,$fid)=preg_split("/:/",$file);
  $file="$fname.php";

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

   //BUILT THE CONTENT
echo <<<CONTENT
  $onload
  <div class="tabbertab maintab">
  <h2>$fid</h2>
  <div class="tabcontent" id="$fid"></div>
  </div>

CONTENT;
  $i++;
}
echo<<<CONTENT
</div>
$footer
</body>
</html>

CONTENT;
//////////////////////////////////////////////////////////////////////////////////
//FINALIZE
//////////////////////////////////////////////////////////////////////////////////
finalizePage();
?>
