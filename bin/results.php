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
$TabNum=1;

//////////////////////////////////////////////////////////////////////////////////
//GLOBAL VARIABLES
//////////////////////////////////////////////////////////////////////////////////
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//INPUT VARIABLES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$header="";
$content="";
$title="";
$errors="";
$onload="";
$extrastyle="margin-left:10px;margin-right:10px;";
$imgload=genLoadImg("animated/loader-circle.gif");
$hidvars="";
$closebutton="";
$qclosable=true;

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//DIRECTORIES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$appname="$_SESSION[AppVersion]";
$appdir="$PROJ[APPSDIR]/$appname";
$apppath="$PROJ[APPSPATH]/$appname";
$contabs=array();
$runsdir="$PROJ[RUNSDIR]/$_SESSION[User]/$appname";
$runspath="$PROJ[RUNSPATH]/$_SESSION[User]/$appname";

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//WINDOW PARAMETERS
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
if(isset($PHP["Closable"])){
  if($PHP["Closable"]=="false"){
    $hidvars.="<input type='hidden' name='Closable' value='$PHP[Closable]'>";
    $qclosable=false;
  }
}
if(!isset($PHP["HeightWindow"])){
  $PHP["HeightWindow"]="70%";
}
$hidvars.="<input type='hidden' name='HeightWindow' value='$PHP[HeightWindow]'>";
$extrastyle.="height:$PHP[HeightWindow];";

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//GENERATE BUG FORM
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
list($bugbutton,$bugform)=genBugForm2("ResultsRun","Results Run",$_SESSION["Contributors"]);

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
$resfile="$runpath/sci2web/resultswindow.conf";
readConfig2("$resfile");

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//STATUS SUMMARY 
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$ajax_briefstatus=<<<AJAX
loadContent
  ('$PROJ[BINDIR]/ajax-trans-run.php?Action=GetStatus&Summary=true&RunCode=$runcode',
   'briefstatus',
   function(element,rtext){
    hash=$(element).attr('hash');
    if(hash!=hex_md5(rtext)){
      $(element).attr('hash',hex_md5(rtext));
      element.innerHTML=rtext;
      if(hash && $('#statusicon').attr('status')=='end'){
	window.location.reload();
      }
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
$onloadbriefstatus=genOnLoad($ajax_briefstatus,'load');

//////////////////////////////////////////////////////////////////////////////////
//GENERATE CONTENT
//////////////////////////////////////////////////////////////////////////////////
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//TABS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
for($i=0;$i<$CONFIG["Tab"]["Num"];$i++){
  $tabtitle=$CONFIG["Tab"][$i];
  $tabcontent=$CONFIG["Content"][$i];
  $tabrefresh=$CONFIG["RefreshTime"][$i];

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //SCAN CONTENTS
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$tabcont=<<<TAB
<div class="tabbertab">
<h2>$tabtitle</h2>
TAB;
  blankFunc();
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
   },
   function(element,rtext){
   },
   function(element,rtext){
     element.innerHTML='ERROR';
   },
   $tabrefresh,
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
    </a>
  </div>
</div>
STATUS;
      }
      //::::::::::::::::::::::::::::::
      //LIST OF FILES
      //::::::::::::::::::::::::::::::
      else if(preg_match("/^ListFiles(.*)/",$module,$matches)){
	$opts=preg_replace("/\?/","",$matches[1]);
	$ftable=filesTable($rundir,$opts,"Blank");
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
   $tabrefresh,
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
<div style="position:relative;height:30px">
  <div class="actionbutton">
    $file:
    <a href="JavaScript:$flink" style="font-size:14px">
      View
    </a> | 
    <a href="$rundir/$file" 
       style="font-size:14px">
      Download
    </a>
  </div>
  <div class="actionbutton"
       style="position:absolute;right:0px;top:0px;">
    <div class="actionbutton">
      <a href="JavaScript:void(null)" onclick="$ajax_file">
	$BUTTONS[Update]
      </a>
    </div>
  </div>
</div>
<div style="position:relative">
  $PROJ[DIVBLANKET]
  $PROJ[DIVOVER]
  <div class="viewarea" id="fileout_${i}_${j}">
    $fcontent
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
//TITLE & HEADER
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$title.=<<<TITLE
<div style="text-align:center;
	    font-size:20px;
	    font-weight:bold;
	    border-bottom:solid $COLORS[dark] 2px
	    ">
Results of Run $PHP[RunCode]
</div>
TITLE;

if($qclosable){
$closebutton=<<<CLOSE
  <!-- -------------------- CLOSE BUTTON -------------------- -->
  <div class="actionbutton">
    <a href="JavaScript:void(null)" class="image" onclick="window.close()">
      $BUTTONS[Cancel]
    </a>
  </div>
CLOSE;
}
$header.=<<<HEADER
<div class="actionbutton">
  <span style="font-size:18px"><b>Results for Run</b>: $PHP[RunCode]</span>
</div>
<div class="actionbutton"
     style="position:absolute;right:0px;top:10px;">
  <div class="actionbutton">
    <input id="TabId" type="hidden" name="TabId" value="$TabNum">
    <input type="hidden" name="RunCode" value="$PHP[RunCode]">
    $hidvars
    <!--onclick="window.location.reload()"-->
    $onloadbriefstatus
    <div class="actionbutton" id="briefstatus" 
	 style="position:relative;
		border:solid black 0px;
		text-align:right;
		width:200px">
    </div>
    <div class="actionbutton">
      $bugbutton
    </div>
    <div class="actionbutton">
      <a href="JavaScript:void(null)" class="image" 
	 onclick="Reload()"
	 onmouseover="explainThis(this)" explanation="Results">
	$BUTTONS[Update]
      </a>
    </div>
    $closebutton
  </div>
</div>
HEADER;

//////////////////////////////////////////////////////////////////////////////////
//CONTENT DISPLAY
//////////////////////////////////////////////////////////////////////////////////
end:
$TabNum=$PHP["TabId"]+1;
$head="";
$head.=genHead("","");

echo<<<CONTENT
<html>
  <head>
    $head
  </head>

  <body>
    <div style="position:relative">
      <!-- -------------------------------------------------------- -->
      <!-- HEADER AREA -->
      <!-- -------------------------------------------------------- -->
      <div style="position:relative">
	$title
      </div>
      <div style="position:relative;padding:10px">
	$header
      </div>
      <!-- -------------------------------------------------------- -->
      <!-- TABS AREA -->
      <!-- -------------------------------------------------------- -->
      <div style="position:relative">
	<div class="tabber" id="$PHP[TabId]"
	     style="$extrastyle">
	  $content
	</div>
      </div>
    </div>
    $bugform
</body>
</html>

CONTENT;

finalizePage();
?>
