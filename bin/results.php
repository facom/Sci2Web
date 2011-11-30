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
if(!isset($PHP["TabId"])) $PHP["TabId"]=1;
$PHP["TabId"]--;

//////////////////////////////////////////////////////////////////////////////////
//GLOBAL VARIABLES
//////////////////////////////////////////////////////////////////////////////////
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//INPUT VARIABLES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$header="";
$content="";
$controls="";
$errors="";
$onload="";
$notmsg="";
$imgload=genLoadImg("animated/loader-circle.gif");

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//DIRECTORIES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$appname="$_SESSION[AppVersion]";
$appdir="$PROJ[APPSDIR]/$appname";
$apppath="$PROJ[APPSPATH]/$appname";
$contabs=array();
$runsdir="$PROJ[RUNSDIR]/$_SESSION[User]/$appname";
$runspath="$PROJ[RUNSPATH]/$_SESSION[User]/$appname";

//////////////////////////////////////////////////////////////////////////////////
//LOAD INFORMATION
//////////////////////////////////////////////////////////////////////////////////
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GET RUN INFORMATION
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$runcode=$PHP["RunCode"];
$resmat=mysqlCmd("select * from runs where run_code='$PHP[RunCode]'");
$row=getRow($resmat,0);
foreach(array_keys($DATABASE["Runs"]) as $runfield){
  $$runfield=$row["$runfield"];
}
$rundir="$runsdir/$run_hash";
$runpath="$runspath/$run_hash";

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GET TAB DESCRIPTION
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$resfile="$runpath/sci2web/results-window.conf";
readConfig2("$resfile");

//////////////////////////////////////////////////////////////////////////////////
//GENERATE CONTENT
//////////////////////////////////////////////////////////////////////////////////

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//HEADER
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$header.=<<<HEADER
<div id="notresults" class="notification" style="display:none"></div>
HEADER;

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//TABS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
for($i=0;$i<$CONFIG["Tab"]["Num"];$i++){
  $tabtitle=$CONFIG["Tab"][$i];
  $tabcontent=$CONFIG["Content"][$i];
  $tabload=$CONFIG["Load"][$i];
  $tabrefresh=$CONFIG["RefreshTime"][$i];

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //SCAN CONTENTS
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$tabcont=<<<TAB
<div class="tabbertab sectab">
<h2>$tabtitle</h2>
TAB;

  $tcont=split(";",$tabcontent);
  $j=0;
  foreach($tcont as $t){
    $id="${i}_${j}";
    //==================================================
    //MODULE
    //==================================================
    if(false){}
    else if(preg_match("/^Module:(.+)/",$t,$matches)){
      $module=$matches[1];
      //print "Tab content module: $module";br();
      if(false){}
      //::::::::::::::::::::::::::::::
      //STATUS MODULE
      //::::::::::::::::::::::::::::::
      else if(preg_match("/^Status(.*)/",$module,$matches)){
$ajax_status=<<<AJAX
loadContent
  ('$PROJ[BINDIR]/ajax-trans-run.php?Action=GetStatus&RunCode=$runcode',
   'runstatus',
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
     element.innerHTML='ERROR';
   },
   -1,
   true
   )
AJAX;
	blankFunc();
	$onloadstatus=genOnLoad($ajax_status,'load');
	divBlanketOver("$id");
$tabcont.=<<<STATUS
$onloadstatus
<div style="position:relative">
$PROJ[DIVBLANKET]
$PROJ[DIVOVER]
<div id="runstatus" class="module"></div>
<div class="update">
<a href="JavaScript:void(null)" onclick="$ajax_status">
$BUTTONS[Update]
</div>
</div>
STATUS;
      }
      //::::::::::::::::::::::::::::::
      //LIST OF FILES
      //::::::::::::::::::::::::::::::
      else if(preg_match("/^ListFiles(.*)/",$module,$matches)){
	$opts=preg_replace("/\?/","",$matches[1]);
	$ftable=filesTable($rundir,$opts);
$tabcont.=<<<TAB
$ftable
TAB;
      }
    }
    //==================================================
    //FILE
    //==================================================
    else if(preg_match("/^File:(.+)/",$t,$matches)){
      $file=$matches[1];
      //print "Tab content file: $file";br();
      blankFunc();
$ajax_file=<<<AJAX
loadContent
  (
   '$PROJ[BINDIR]/ajax-trans-file.php?Action=Get&Dir=$rundir&File=$file&Mode=View',
   'fileout_${i}_${j}',
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
   },
   -1,
   true
   )
AJAX;
      blankFunc();
      $onloadfile=genOnLoad($ajax_file,'load$j');
      $flink=fileWebOpen($rundir,$file,'View');
      divBlanketOver("$id");
      $fcontent="";
$tabcont.=<<<FILE
$onloadfile
<div style="font-size:14px">
  <a href="JavaScript:$flink">
  $file
  </a>
</div>
<div style="position:relative">
$PROJ[DIVBLANKET]
$PROJ[DIVOVER]
<div class="plainarea" id="fileout_${i}_${j}">
$fcontent
</div>
<div class="update">
<a href="JavaScript:void(null)" onclick="$ajax_file">
$BUTTONS[Update]
</div>
</div>
<p></p>
FILE;
    }
    //==================================================
    //SCRIPT
    //==================================================
    else if(preg_match("/^Script:(.+)/",$t,$matches)){
      $script=$matches[1];
$tabcont.=<<<SCRIPT
$script
SCRIPT;
    }
    $j++;
  }
$contabs[].=<<<TAB
$tabcont
</div>
TAB;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//JOIN TABS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
foreach($contabs as $tabcont){
  $content.=$tabcont;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//CONTROLS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$controls.= <<<CONF
<div class="close">
  <button class="image" onclick="window.close()">
    $BUTTONS[Cancel]
  </button>
</div>
CONF;

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//RUN CONTROLS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$ajax_controls=<<<AJAX
loadContent
  (
   '$PROJ[BINDIR]/ajax-trans-run.php?RunCode=$runcode&Action=GetControls',
   'runcontrols',
   function(element,rtext){
     hash=$(element).attr('hash');
     if(hash!=hex_md5(rtext)){
       $(element).attr('hash',hex_md5(rtext));
       element.innerHTML=rtext;
     }
   },
   function(element,rtext){
   },
   function(element,rtext){
   },
   2000,
   true
   )
AJAX;
$runcontrols=<<<CONTROL
<div id="runcontrols" class="control"></div>
CONTROL;
$onloadruncontrols=genOnLoad($ajax_controls,'load');

//////////////////////////////////////////////////////////////////////////////////
//CONTENT DISPLAY
//////////////////////////////////////////////////////////////////////////////////
end:
$notification=genOnLoad("notDiv('notconfigure','$notmsg')");
$DEBUG=genDebug("bottom");
$head="";
$TabNum=$PHP["TabId"]+1;
$head.=genHead("","");
$bugbut=genBugForm("ResultsGeneral","General problems with results");

echo<<<CONTENT
<html>
<head>
$head
</head>

<body>
$PROJ[ELBLANKET]
$PROJ[ELOVER]  
$notification
$header
$onloadruncontrols
$runcontrols

<div class="tabber sectabber" id="$PHP[TabId]">
$DEBUG
<input type="hidden" name="RunCode" value="$runcode">
$content
</div>
$controls
<div class="close" style="right:50px;top:10px">$bugbut</div>
</body>
</html>

CONTENT;
?>
