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
$PAGETITLE="Configure Run";
include_once("$RELATIVE/lib/sci2web.php");

//////////////////////////////////////////////////////////////////////////////////
//GLOBAL VARIABLES
//////////////////////////////////////////////////////////////////////////////////
$head="";
$content="";
$error_content="";
$qerror=false;
$appname="$_SESSION[AppVersion]";
$appdir="$PROJ[APPSDIR]/$appname";
$apppath="$PROJ[APPSPATH]/$appname";
$runsdir="$PROJ[RUNSDIR]/$_SESSION[User]/$appname";
$runspath="$PROJ[RUNSPATH]/$_SESSION[User]/$appname";

$extrastyle="margin-left:10px;margin-right:10px;height:82%;";

//////////////////////////////////////////////////////////////////////////////////
//PROCEED
//////////////////////////////////////////////////////////////////////////////////
if(isset($PHP["RunCode"])){
  $runhash=mysqlGetField("select * from runs where run_code='$PHP[RunCode]'",0,"run_hash");
  if(isBlank($runhash)){$error="Run '$PHP[RunCode]' does not exist.";$qerror=true;goto error;}
  $runpath="$runspath/$runhash";
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//WHAT DO YOU WANT TO WATCH
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
if($PHP["Watch"]=="RunStatus"){
  $cmd="cd $runpath;bash sci2web/bin/sci2web.sh qstatus";
  $watch_title="Status of run $PHP[RunCode]";
}

if($PHP["Watch"]=="FullStatus"){
  $cmd="cd $runpath;bash sci2web/bin/sci2web.sh fullinfo";
  $watch_title="Full status of run $PHP[RunCode]";
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//WATCH!
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$ajax_watch=<<<AJAX
loadContent
  (
   /*'$PROJ[BINDIR]/ajax-trans-file.php?Action=Get&Dir=$PROJ[TMPDIR]&File=watch.$PHP[RANDID]&Mode=View',*/
   '$PROJ[BINDIR]/ajax-trans-file.php?Action=Check&ToCheck=$cmd',
   'watch',
   function(element,rtext){
     element.innerHTML=rtext;
     $('#DIVBLANKET').css('display','none');
     $('#DIVOVER').css('display','none');
   },
   function(element,rtext){
     $('#DIVBLANKET').css('display','block');
     $('#DIVOVER').css('display','block');
   },
   function(element,rtext){
   },
   -1,
   true
   )
AJAX;
$onloadwatch=genOnLoad($ajax_watch,'loadwatch');

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//ERROR
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
error:
if($qerror){
$error_content=<<<CONTENT

An error has occurred in the watch command:

$error
CONTENT;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GENERATE CONTENT
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$content.=<<<CONTENT
<div class="tabbertab">
<h2>Watch window</h2>
$onloadwatch
<div style="position:relative;height:30px">
  <div class="actionbutton">
  <big><b>$watch_title</b></big>
  </div>
  <div class="actionbutton"
       style="position:absolute;right:0px;top:0px;">
    <a href="JavaScript:void(null)" onclick="$ajax_watch">
      $BUTTONS[Update]
    </a>
  </div>
</div>
<div style="position:relative">
  $PROJ[DIVBLANKET]
  $PROJ[DIVOVER]
  <div class="viewarea" id="watch"
       style="height:90%"
       >
    $error_content</div>
</div>
CONTENT;

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//HEAD
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$head.=genHead("","");

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//CONTENT
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
echo<<<CONTENT
<html>
  <head>
    $head
  </head>
  
  <body>
    $PROJ[ELBLANKET]
    $PROJ[ELOVER]
    <br/>
    <div class="actionbutton"
	 style="position:absolute;right:10px;top:10px">
      <a href="JavaScript:void(null)" class="image" onclick="window.close()">
	$BUTTONS[Cancel]
      </a>
    </div>
    <div class="tabber" id="0" style=$extrastyle>
      $content
    </div>
</body>
</html>

CONTENT;

finalizePage();
?>
