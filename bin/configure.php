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
$footer="";
$actionresult="";
$errors="";
$onload="";
//BY DEFAULT MESSAGE
$notmsg="Data loaded...";

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//SWITCHES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$qnew=false;
$qerror=false;
$qsave=false;
$qmove=false;

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//DIRECTORIES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$appname="$_SESSION[AppVersion]";
$appdir="$PROJ[APPSDIR]/$appname";
$apppath="$PROJ[APPSPATH]/$appname";
$runsdir="$PROJ[RUNSDIR]/$_SESSION[User]/$appname";
$runspath="$PROJ[RUNSPATH]/$_SESSION[User]/$appname";

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//FORMS INFORMATION
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//ARAMETRIZATION MODEL
list($tabs,$groups,$vars)=readParamModel("$apppath/sci2web/parametrization.info");

//////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////
//ACTIONS
//////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////
if(isset($PHP["Action"])){

  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //NEW
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  if($PHP["Action"]=="New"){

    //==================================================
    //RUN VARIABLES
    //==================================================
    //GENERATE NEW RANDOM CODE
    $PHP["RunCode"]=genRandom(8);
    $runcode=$PHP["RunCode"];
    //CHOOSE TEMPLATE
    $runfile="$runspath/templates/$PHP[Template].conf";
    //COMPUTE RUN HASH
    $runhash=hashFile($runfile);
    $runtmp="$PROJ[TMPPATH]/temprun/$runcode-$runhash";
    $runpath="$runspath/$runhash";
    //DEBUGGING
    $actionresult.="<p>New run with template $PHP[Template]</p>";

    //==================================================
    //CREATE TEMPORAL RUN DIRECTORY AND FILES
    //==================================================
    $out=systemCmd("perl $PROJ[BINPATH]/sci2web-genrun $PROJ[APPSPATH]/$_SESSION[App] $_SESSION[Version] $PHP[Template] $runtmp");
    if($PHP["?"]){$qerror=true;$notmsg=$errors.="<p>Error generating run</p>";goto error;}
    $out=systemCmd("perl $PROJ[BINPATH]/sci2web-genfiles $runfile $runtmp");
    if($PHP["?"]){$qerror=true;$notmsg=$errors.="<p>Error generating files</p>";goto error;}
    $actionresult.="<p>File generated for new run...</p>";

    //==================================================
    //MOVE TEMPORAL TO FINAL DIRECTORY
    //==================================================
    if(is_dir($runpath)){
      //OVERWRITE PREVIOUSLY DIRECTORIES WITH THE SAME NAME
      $out=systemCmd("rm -rf $runpath");
    }
    $out=systemCmd("mv $runtmp $runpath");
    if($PHP["?"]){$qerror=true;$notmsg=$errors.="<p>Error moving run dir</p>";goto end;}
    $actionresult.="<p>New run created...</p>";

    //==================================================
    //GENERATE DATABASE INFORMATION
    //==================================================
    $PHP["run_code"]="$runcode";
    $PHP["run_hash"]="$runhash";
    $PHP["run_name"]="New Run";
    $PHP["configuration_date"]=
      getToday("%year-%mon-%mday %hours:%minutes:%seconds");
    $PHP["run_status"]=$S2C["configured"];
    $PHP["run_pinfo"]="";
    $PHP["permissions"]="rw";
    $PHP["versions_id"]=$_SESSION["VersionId"];
    $PHP["users_email"]=$_SESSION["User"];
    $PHP["run_extra1"]="";
    $PHP["run_extra2"]="";
    $PHP["run_extra3"]="";

    //==================================================
    //READ CONFIGURATION FROM TEMPLATE
    //==================================================
    $numvars=readConfig("$runfile");
    foreach($tabs as $tab) foreach($groups[$tab] as $group) 
      foreach($vars[$tab][$group] as $var){
      list($var,$defval,$datatype,$varname,$vardesc)=split("::",$var);
      $PHP["$var"]=$CONFIG["$var"];
    }
    $actionresult.="<p>Configuration file readed...</p>";

    //==================================================
    //GENERATING RUN INFO
    //==================================================
    $fl=fopen("$runpath/run.info","w");
    fwrite($fl,"run_app=$_SESSION[App]\n");
    fwrite($fl,"run_version=$_SESSION[Version]\n");
    fwrite($fl,"run_author=$_SESSION[User]\n");
    foreach(array_keys($DATABASE["Runs"]) as $runfield){
      if($runfield=="configuration_date") continue;
      if($runfield=="run_hash") continue;
      fwrite($fl,"$runfield=$PHP[$runfield]\n");
    }
    fclose($fl);
    $runconfhash=hashFile("$runpath/run.info");
    systemCmd("echo '#$runconfhash' >> $runpath/run.info");
    if($PHP["?"]){$qerror=true;$notmsg=$errors.="<p>Error creating run.info</p>";goto end;}

    //==================================================
    //PASSING THE CONTROL TO THE SAVE PROCEDURE
    //==================================================
    $qnew=true;
    $PHP["Action"]="Save";
    $notmsg=$actionresult.="<p>New run created...</p>";
  }

  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //SAVE
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  if($PHP["Action"]=="Save"){

    //==================================================
    //GET OLD INFORMATION
    //==================================================
    if(!$qnew){
      //GET HASH
      $oldrunhash=mysqlGetField("select * from runs where run_code='$PHP[RunCode]'",0,"run_hash");
      if($PHP["?"]){$qerror=true;$notmsg=$errors.="<p>Run not found</p>";goto error;}
    }else{$oldrunhash=$runhash;}
    //OLD DIRECTORY
    $oldrunpath="$runspath/$oldrunhash";

    //==================================================
    //UPLOAD FILES (IF APPLICABLE)
    //==================================================
    $uperror=uploadFile($oldrunpath);

    //==================================================
    //GENERATING NEW RUN INFORMATION
    //==================================================
    $och=systemCmd("cat $oldrunpath/run.info");
    $oldrunconfhash=
      systemCmd("tail -n 1 $oldrunpath/run.info | cut -f 2 -d '#'");
    $fl=fopen("$oldrunpath/run.info","w");
    fwrite($fl,"run_app=$_SESSION[App]\n");
    fwrite($fl,"run_version=$_SESSION[Version]\n");
    fwrite($fl,"run_author=$_SESSION[User]\n");
    foreach(array_keys($DATABASE["Runs"]) as $runfield){
      if($runfield=="configuration_date") continue;
      if($runfield=="run_hash") continue;
      fwrite($fl,"$runfield=$PHP[$runfield]\n");
    }
    fclose($fl);
    $runconfhash=hashFile("$oldrunpath/run.info");
    $actionresult.="<p><b>Old run conf. hash</b>:$oldrunconfhash</p>";
    $actionresult.="<p><b>New run conf. hash</b>:$runconfhash</p>";
    systemCmd("echo '#$runconfhash' >> $oldrunpath/run.info");

    //==================================================
    //SAVE CONFIGURATION FILE ACCORDING TO VALUES
    //==================================================
    list($ft,$ftname)=tempFile($PROJ["TMPPATH"]);
    if($qnew) fwrite($ft,"#$runcode\n");
    foreach($tabs as $tab){
      if($tab=="Results") continue;
      foreach($groups[$tab] as $group){
	foreach($vars[$tab][$group] as $var){
	  list($var,$defval,$datatype,$varname,$vardesc)=
	    split("::",$var);
	  $val=$PHP["$var"];
	  fwrite($ft,"$var = $val\n");
	}
      }
    }

    //==================================================
    //CHECK HASH
    //==================================================
    $runhash=hashFile($ftname);
    $actionresult.="<p><b>Old Run hash</b>: $oldrunhash</p>";
    $actionresult.="<p><b>New Run hash</b>: $runhash</p>";
    $runpath="$runspath/$runhash";
    $out=systemCmd("ls $runpath");

    /*INCOMPLETE*/

    //==================================================
    //CHECK CHANGES
    //==================================================
    //RUN INFO CHANGE & RUN CONF. CHANGE
    if($runconfhash!=$oldrunconfhash and $PHP["?"]){
      $actionresult.="<p>Run.Info.change, Run.Conf.change</p>";
      $qsave=true;
      $qmove=true;
    }
    //RUN INFO NOT CHANGE & RUN CONF. CHANGE
    if($runconfhash==$oldrunconfhash and $PHP["?"]){
      $actionresult.="<p>Run.Info.not change, Run.Conf.change</p>";
      $qsave=true;
      $qmove=true;
    }
    //RUN INFO CHANGE & RUN CONF. NOT CHANGE
    if($runconfhash!=$oldrunconfhash and !$PHP["?"]){
      $actionresult.="<p>Run.Info.change, Run.Conf.not change</p>";
      $qsave=true;
      $qmove=false;
    }
    //RUN INFO NOT CHANGE & RUN CONF. NOT CHANGE
    if($runconfhash==$oldrunconfhash and !$PHP["?"]){
      $actionresult.="<p>Run.Info.not change, Run.Conf.not change</p>";
      $qsave=false;
      $qmove=false;
    }
    if(!$uperror){
      $qsave=true;
    }
    if(!$qsave){
      $actionresult.="<p>Report box not change</p>";
      $notmsg=$errors.="<p>No change has been made...</p>";
      $notmsg="Not change...";
      $qerror=true;
      goto error;
    }else{
      $actionresult.="<p>Report box saved</p>";
      $notmsg="Saved...";
    }

    //==================================================
    //SAVE NEW CONFIGURATION FILE
    //==================================================
    systemCmd("cp -rf $ftname $oldrunpath/.app.conf");
    closeTemp($ft,$ftname);

    //==================================================
    //COPY NEW RUN
    //==================================================
    $runpath="$runspath/$runhash";
    if($qmove){

      //==================================================
      //CLEAN AND GENERATE NEW FILES
      //==================================================
      //CLEAN
      $out=systemCmd("cd $oldrunpath;bash sci2web/bin/s2w-action.sh cleanall");
      if($PHP["?"]){$qerror=true;$notmsg=$errors.="<p>Clean failed.</p>";goto error;}

      //GENERATE FILES ACCORDING TO NEW CONFIGURATION FILE
      $out=systemCmd("perl $PROJ[BINPATH]/sci2web-genfiles $oldrunpath/.app.conf $oldrunpath");
      if($PHP["?"]){$qerror=true;$notmsg=$errors.="<p>File generation failed.</p>";goto error;}

      //==================================================
      //MOVING RUN TO NEW HASH DIR
      //==================================================
      $out=systemCmd("mv $oldrunpath $runpath");
      if($PHP["?"]){$qerror=true;$notmsg=$errors.="<p>Directory could not be moved</p>";goto error;}

      //==================================================
      //RESET STATUS TO CONFIGURE
      //==================================================
      $PHP["run_status"]=$S2C["configured"];
      $actionresult.="<p>Status changed</p>";
    }
    $actionresult.="<p>Run saved...</p>\n";

    //==================================================
    //SAVE IN DATABASE
    //==================================================
    $PHP["run_hash"]=$runhash;
    $PHP["configuration_date"]=
      getToday("%year-%mon-%mday %hours:%minutes:%seconds");
    $sqlcmd="replace into runs set ";
    foreach(array_keys($DATABASE["Runs"]) as $runfield){
      $sqlcmd.="$runfield='$PHP[$runfield]',";
    }
    $sqlcmd=rtrim($sqlcmd,",");
    $resmat=mysqlCmd($sqlcmd);
    if($PHP["?"]){$qerror=true;$notmsg=$errors.="<p>Database could not be updated</p>";goto error;}
    $actionresult.="<p>Run saved in database...</p>\n";
  }

  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //CREATE TEMPLATE
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  if($PHP["Action"]=="Template"){

    //==================================================
    //CREATE A NEW TEMPLATE
    //==================================================
    $template=preg_replace("/\s+/","_",$PHP["NewTemplate"]);
    $tempdir="$runspath/templates";
    $notmsg="Saving new template $template";
    if(file_exists("$tempdir/$template.conf")){
      $qerror=true;
      $notmsg=$errors="<p>A template with this name already exist.</p>";
      goto error;
    }else{
      genConfig("$apppath/sci2web/parametrization.info",
		"$tempdir/$template.conf",
		"#T:$PHP[NewTemplate]");
      $notmsg=$actionresult.="<p>Template '$PHP[NewTemplate]' created...</p>";
    }
  }

  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //ERROR
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
 error:
  if($qerror){
$actionresult.=<<<RESULT
<p>Errors:</p>
$errors
RESULT;
  }

  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //CREATE ACTION RESULT TAB
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  if($PROJ["DEBUG"]){
$content.=<<<CONF
<div class="tabbertab sectab">
<h2>Actions</h2>
  <p><b>Action</b>: $PHP[Action]</p>
  <div id="actionresult">$actionresult</div>
</div>
CONF;
  }

  blankFunc();
}

//////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////
//LOAD INFORMATION
//////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//BASIC VARIABLES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$runcode=$PHP["RunCode"];
$runhash=mysqlGetField("select * from runs where run_code='$runcode'",
		       0,"run_hash");

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//READ RUN INFORMATION FROM DATABASE
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$resmat=mysqlCmd("select * from runs where run_code='$PHP[RunCode]'");
$row=getRow($resmat,0);
foreach(array_keys($DATABASE["Runs"]) as $runfield){
  $CONFIG["$runfield"]=$row["$runfield"];
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//DIRECTORIES AND OTHER OBJECTS
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$rundir="$runsdir/$runhash";
$runpath="$runspath/$runhash";
$runfile="$runpath/app.conf";

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//PARAMETRIZATION INFORMATION
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$numconf=readConfig("$runfile");

//////////////////////////////////////////////////////////////////////////////////
//GENERATE CONTENT
//////////////////////////////////////////////////////////////////////////////////
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//BLOCK ACCORDING TO STATUS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//HEADER
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$header.=<<<HEADER
<div id="notconfigure" class="notification" style="display:none"></div>
$onload
HEADER;

if($PROJ["DEBUG"]){
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//EXTRA TABS IN CASE OF DEBUGGING
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

//==================================================
//FILES
//==================================================
$ftable=filesTable($rundir);
$content.=<<<CONF
<div class="tabbertab sectab">
<h2>Files</h2>
$ftable
</div>
CONF;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GNERAL PROPERTIES
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//array_unshift($tabs,"General");
$tabs[]="General";

$groups["General"][]="Buttons";
if(isset($PHP["template"])){
  $tempname=str_replace("_"," ",$PHP["template"]);
  $tempname=str_replace(".conf","",$tempname);
}else{$tempname="$CONFIG[run_name]";}

//==================================================
//ADD INPUT AND BUTTON FOR A NEW TEMPLATE
//==================================================
$vars["General"]["Buttons"][]=<<<BUTTON
New template:<input type="text" name="NewTemplate" value="$tempname">
<button class="image" name='Action' value='Template'>$BUTTONS[Add]</button>
<br/>
BUTTON;

//==================================================
//ADD PROPERTIES
//==================================================
$groups["General"][]="Global";
foreach(array_keys($DATABASE["Runs"]) as $runfield){
  $var=$runfield;
  $val=$CONFIG["$runfield"];
  $varname=$DATABASE["Runs"]["$runfield"];
  $vartype="varchar";
  $protected="readonly";

  //UNPROTECT RUN NAME AND PERMISSIONS
  if($var=="run_name" or $var=="permissions") $protected="";

  //FOR PERMISSIONS SELECT FROM A LIST
  if($var=="permissions") $val="rw;;rx;;xw;;xx==rw";
  
  $vars["General"]["Global"][]="$var::$val::$vartype::$varname::$varname::$protected::";
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//GENERATE TABS ACCORDING TO CONFIGURATION 
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$tabid=1;
foreach($tabs as $tab)
{
  //==================================================
  //AVOID RESULT GROUP OF VARIABLES
  //==================================================
  if($tab=="Results") continue;
  //==================================================
  //CREATE FORM TAB FOR GROUP TAB
  //==================================================
$content.= <<<CONF
<div class="tabbertab">
<h2>$tab</h2>
<table class="configuration">
<tbody>
CONF;
  blankFunc();
  foreach($groups[$tab] as $group){
    //==================================================
    //GROUP BUTTONS ON EACH WINDOW
    //==================================================
    if($group=="Buttons"){
      $content.="<tr class='buttons'><td colspan=5>";
      foreach($vars[$tab][$group] as $button){
	$content.=$button;
      }
      $content.="</tr></td>";
      continue;
    }

    //==================================================
    //HEADER OF THE GROUP
    //==================================================
$content.= <<<CONF
<tr>
<td class="group" colspan=5>
$group
</td>
</tr>
CONF;
   foreach($vars[$tab][$group] as $var){
     //==================================================
     //GET INFORMATION FROM PARAMETRIZATION
     //==================================================
     list($varn,$defval,$datatype,$varname,
	  $vardesc,$protected,$files)=split("::",$var);
     $val=$CONFIG["$varn"];

     //==================================================
     //GENERATE INPUT
     //==================================================
     $inputstyle="";
     if($protected=="readonly"){
       $inputstyle="background-color:lightgray;";
     }    					
     
     $extra="class='confinput' onmouseover='explainThis(this)' 
             explanation='$vardesc' $protected style='$inputstyle'";
     //$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
     //RANGE: SCROLLABLE
     //$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
     if(preg_match("/--/",$defval)){
       list($ranges,$defval)=preg_split("/==/",$defval);
       $parts=preg_split("/--/",$ranges);
       $min=$parts[0];
       $max=$parts[1];
       if(!isset($parts[2])){
	 $delta=($max-$min)/100;
       }else $delta=$parts[2];
       $input=scrollableInput("$varn",$extra,$val,$min,$max,$delta);
     }
     //$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
     //LIST:SELECT
     //$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
     else if(preg_match("/;;/",$defval)){
       list($list,$defval)=preg_split("/==/",$defval);
       $parts=preg_split("/;;/",$list);
       $input=genSelect($parts,"$varn",$val,$extra);
     }     
     //$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
     //BOOLEAN:CHECKBOX
     //$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
     else if($datatype=="boolean"){
       $checked="";
       if(!isBlank($val)){
	 $checked="checked";
	 $val=1;
       }else{
	 $checked="";
	 $val=0;
       }
       $input="<input type='checkbox'
                name='$varn' value='$val' $checked $extra>";
     }
     //$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
     //TEXT:TEXTAREA
     //$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
     else if($datatype=="text"){
       $input="<textarea name='$varn' cols='50' rows='10' $extra>$val</textarea>";
     }
     //$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
     //FILE:
     //$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
     else if($datatype=="file"){
       $flink_edit=$flink_view="void(null)";
       $inputhidden="";
       if(!isBlank($val)){
	 if(!file_exists("$runpath/$val"))
	   $val="File '$val' does not exist.";
	 else{
	   $flink_view=fileWebOpen($rundir,$val,"View");
	   $flink_edit=fileWebOpen($rundir,$val,"Edit");
	   $inputhidden="<input type='hidden' name='$varn' value='$val'>";
	 }
       }else{
	 $val="File not uploaded.";
       }
$input=<<<INPUT
<input type="hidden" name="MAX_FILE_SIZE" value="1000000"/>
$inputhidden
<input type="file" name="$varn" $extra><br/>
<i>$val</i> (
<a href="JavaScript:$flink_view">view</a>|
<a href="JavaScript:$flink_edit">edit</a>
)
INPUT;
     }
     //$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
     //SINGLE VALUE:SIMPLE INPUT
     //$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
     else{
       $input="<input type='text' name='$varn' value='$val' $extra size='30%'>";
     }
     if(isBlank($val)){$val=$defval;}
$content.= <<<CONF
<tr class="var">
  <td class="varname" valign="top">$varname</td>
  <td class="varval">
  $input
  </td>
</tr>
CONF;
     blankFunc();
   }//END FOR BAR
  }//END FOR GROUP
  //==================================================
  //CLOSE TABLE
  //==================================================
$content.= <<<CONF
</tbody>
</table>
</div>
CONF;
}//END FOR TAB

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//WINDOW CONTROLS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$bugbut=genBugForm($_SESSION["User"],$PHP["PAGENAME"],
		   "ConfigureGeneral","General problems with configuration");
$controls.=<<<CONF
</div>
<div class="formbuttons" id="buttons">
  <!-- -------------------- SAVE BUTTON -------------------- -->
  <button id="savebutton" class="image" name="Action" value="Save"
  onmouseover="explainThis(this)" explanation="Save">
  $BUTTONS[Save]
  </button> 
  <!-- -------------------- RESULTS BUTTON -------------------- -->
  <button class="image" name="Action" value="Results"
  onclick="Open('$PROJ[BINDIR]/results.php?RunCode=$runcode','Results','$PROJ[SECWIN]')"
  onmouseover="explainThis(this)" explanation="Results" style="">
  $BUTTONS[Results]
  </button>
</div>

<div class="close">
  <button class="image" onclick="window.close()">
    $BUTTONS[Cancel]
  </button>
</div>
CONF;

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//RUN CONTROLS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$ajaxcmd=<<<AJAX
loadContent
  (
   '$PROJ[BINDIR]/ajax-trans-run.php?RunCode=$runcode&Action=GetControls',
   'runcontrols',
   function(element,rtext){
     element.innerHTML=rtext;
   },
   function(element,rtext){
   },
   function(element,rtext){
   },
   1000,
   true
   )
AJAX;
$runcontrols=<<<CONTROL

<div id="runcontrols" class="control"></div>
CONTROL;
$onload=genOnLoad($ajaxcmd,'load');
//////////////////////////////////////////////////////////////////////////////////
//CONTENT DISPLAY
//////////////////////////////////////////////////////////////////////////////////
end:

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//LAST MINUTE ELEMENTS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$notification=genOnLoad("notDiv('notconfigure','$notmsg')");
$DEBUG=genDebug("bottom");
$head="";
$TabNum=$PHP["TabId"]+1;
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
$notification
$header
$onload

<form method="post" action="?" enctype="multipart/form-data">
<input id="TabId" type="hidden" name="TabId" value="$TabNum">
<div class="tabber sectabber" id="$PHP[TabId]">
$DEBUG
<input type="hidden" name="RunCode" value="$runcode">
$content
</div>
$controls
</form>
$runcontrols
<div class="close" style="right:50px;top:10px">$bugbut</div>
</body>
</html>

CONTENT;
?>
