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
# AJAX SCRIPT
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
*/
//////////////////////////////////////////////////////////////////////////////////
//LIBRARY
//////////////////////////////////////////////////////////////////////////////////
$RELATIVE="..";
include_once("$RELATIVE/lib/sci2web.php");

//////////////////////////////////////////////////////////////////////////////////
//GLOBAL VARIABLES
//////////////////////////////////////////////////////////////////////////////////
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//COMMON VARS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$appname="$_SESSION[AppVersion]";
$appdir="$PROJ[APPSDIR]/$appname";
$apppath="$PROJ[APPSPATH]/$appname";
$runsdir="$PROJ[RUNSDIR]/$_SESSION[User]/$appname";
$runspath="$PROJ[RUNSPATH]/$_SESSION[User]/$appname";
$savedbpath="$PROJ[RUNSPATH]/db/$appname";
$srcpath="$PROJ[RUNSPATH]/src/$appname";
$qselrun=false;
$actionresult="";
$result="";
$error="";

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//LIST OF RUNS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
if(isset($PHP["RunMultiple"])){
  $nruns=$PHP["RunNum"];
  $runlist=$PHP["RunCodes"];
  $runall=preg_split("/;/",$runlist);
  $runcodes=array();
  for($i=1;$i<=$nruns;$i++){
    if(isset($PHP["Run$i"])){
      if($PHP["Run$i"]=="on"){
	$runcodes[]=$runall[$i-1];
	$qselrun=true;
      }
    }
  }
  if(isset($PHP["RunAll"])){
    if($PHP["RunAll"]=="on"){
      $runcodes=$runall;
    }else if(!$qselrun){
      $runcodes=array();
    }
  }
}else{
  $runcodes=array($PHP["RunCode"]);
}
if($PHP["Action"]=="New" or 
   $PHP["Action"]=="RemoveTemplate"){
  $runcodes=array("00000000");
}
/*
echo "Multiple:$PHP[RunMultiple]";br();
echo "RUNS: $PHP[RunCodes]";br();
printArray($runcodes,"RUNS");
goto end;
*/
$nruns=0;
foreach($runcodes as $runcode){
  if(isBlank($runcode)) continue;
  $nruns++;
}
if($nruns<1){
  $result.="No runs selected for $PHP[Action].";
  goto end;
}else{
  $result.="Action *$PHP[Action]* in $nruns runs...<br/>";
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//READ PARAMETRIZATION MODEL
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
list($tabs,$groups,$vars)=readParamModel("$apppath/sci2web/controlvars.info");
 
foreach($runcodes as $runcode){
if(isBlank($runcode)) continue;
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//BASIC INFORMATION
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$qerror=false;

/*
$result.="Action $PHP[Action] on $runcode with hash $runhash<br/>";
continue;
*/

//////////////////////////////////////////////////////////////////////////////////
//ACTION
//////////////////////////////////////////////////////////////////////////////////
if($PHP["Action"]=="RemoveTemplate")
{
  if($PHP["Template"]=="Default"){
    $result.="You cannot remove the Default template.";
  }else{
    $ftemplate="$runspath/templates/$PHP[Template].conf";
    if(file_exists($ftemplate)){
      systemCmd("rm -rf $ftemplate");
      $onload=genOnLoad("$('#Template_$PHP[Template]').attr('disabled',true)");
      $result.="Template removed $PHP[Template]$onload";
    }else{
      $result.="Template file not found";
    }
  }
}
if($PHP["Action"]=="New")
{
  echo "Number of runs: $PHP[NumRuns]";br();
  echo "New code: $PHP[NewCode]";br();
  for($nr=1;$nr<=$PHP["NumRuns"];$nr++){
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //NEW 
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //==================================================
  //RUN VARIABLES
  //==================================================
  //GENERATE NEW RANDOM CODE
  if(!isset($PHP["NewCode"]) or
     $PHP["NewCode"]=="00000000"){
    $PHP["RunCode"]=genRandom(8);
  }else{
    $PHP["RunCode"]=$PHP["NewCode"];
  }
  $runcode=$PHP["RunCode"];
  //CHOOSE TEMPLATE
  systemCmd("(cat $runspath/templates/$PHP[Template].conf;echo '#$runcode')>$PROJ[TMPPATH]/run.conf.$PHP[RANDID]");
  $runfile="$PROJ[TMPPATH]/run.conf.$PHP[RANDID]";
  //COMPUTE RUN HASH
  $runhash=hashFile($runfile);
  $runtmp="$PROJ[TMPPATH]/temprun/$runcode-$runhash";
  $runpath="$runspath/$runhash";
  //DEBUGGING
  $actionresult.="<p>New run with template $PHP[Template]</p>";

  //==================================================
  //CREATE TEMPORAL RUN DIRECTORY AND FILES
  //==================================================
  $out=systemCmd("perl $PROJ[BINPATH]/sci2web.pl genrun --appdir $PROJ[APPSPATH]/$_SESSION[App]/$_SESSION[Version] --rundir $runtmp");
  if($PHP["?"]){$qerror=true;$error.="<p>Error generating run</p>";goto next;}
  $out=systemCmd("perl $PROJ[BINPATH]/sci2web.pl genfiles --runconf $runfile --rundir $runtmp");
  if($PHP["?"]){$qerror=true;$error.="<p>Error generating files</p>";goto next;}
  $actionresult.="<p>File generated for new run...</p>";

  //==================================================
  //MOVE TEMPORAL TO FINAL DIRECTORY
  //==================================================
  if(is_dir($runpath)){
    //OVERWRITE PREVIOUSLY DIRECTORIES WITH THE SAME NAME
    $out=systemCmd("rm -rf $runpath");
  }
  $out=systemCmd("mv $runtmp $runpath");
  if($PHP["?"]){$qerror=true;$error.="<p>Error moving run dir</p>";goto next;}
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
  $PHP["run_template"]="$PHP[Template]";
  $PHP["permissions"]="rw";
  $PHP["versions_code"]=$_SESSION["Version"];
  $PHP["apps_code"]=$_SESSION["App"];
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
  if($PHP["?"]){$qerror=true;$error.="<p>Error creating run.info</p>";goto next;}
    
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
  if($PHP["?"]){$qerror=true;$error.="<p>Database could not be updated</p>";goto next;}
  }
  $result.="$PHP[NumRuns] runs created...";
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GET INFORMATION ABOUT THE RUN
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$runhash=mysqlGetField("select * from runs where run_code='$runcode'",
		       0,"run_hash");

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//DIRECTORIES
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$rundir="$PROJ[RUNSDIR]/$_SESSION[User]/$appname/$runhash";
$runpath="$PROJ[RUNSPATH]/$_SESSION[User]/$appname/$runhash";

if($PHP["Action"]=="Clean")
{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //CLEANING
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  systemCmd("cd $runpath;bash sci2web/bin/sci2web.sh cleanall");
  if($PHP["?"]){$qerror=true;$error.="$runcode not cleaned.";goto next;}
  $status=$S2C["clean"];
  mysqlCmd("update runs set run_status='$status' where run_code='$runcode'");
  if($PHP["?"]){$qerror=true;$error.="Failed status clean for $runcode";goto next;}
}

if($PHP["Action"]=="Compile")
{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //CLEANING
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  systemCmd("cd $runpath;bash sci2web/bin/sci2web.sh clean");
  if($PHP["?"]){$qerror=true;$error.="$runcode not cleaned.";goto next;}
  $status=$S2C["clean"];
  mysqlCmd("update runs set run_status='$status' where run_code='$runcode'");
  if($PHP["?"]){$qerror=true;$error.="Failed status clean for $runcode";goto next;}
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //COMPILING
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  systemCmd("cd $runpath;bash sci2web/bin/sci2web.sh compile");
  if($PHP["?"]){$qerror=true;$error.="$runcode not compiled.";goto next;}
  $status=$S2C["compiled"];
  mysqlCmd("update runs set run_status='$status' where run_code='$runcode'");
  if($PHP["?"]){$qerror=true;$error.="Failed status compile for $runcode";goto next;}
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //PREPARING
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  systemCmd("cd $runpath;bash sci2web/bin/sci2web.sh pre");
  if($PHP["?"]){$qerror=true;$error.="$runcode not prepared.";goto next;}
  $status=$S2C["ready"];
  mysqlCmd("update runs set run_status='$status' where run_code='$runcode'");
  if($PHP["?"]){$qerror=true;$error.="Failed status preparing for $runcode";goto next;}
}

if($PHP["Action"]=="Remove")
{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //REMOVING RUN DIRECTORY
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  systemCmd("rm -rf $runpath");
  if($PHP["?"]){$qerror=true;$error.="$runcode not removed.";goto next;}
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //REMOVING DATABASE ENTRY
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  mysqlCmd("delete from runs where run_code='$runcode'");
  if($PHP["?"]){$qerror=true;$error.="Failed status remove for $runcode";goto next;}
}

if($PHP["Action"]=="Run")
{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //SUBMIT
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  systemCmd("cd $runpath;bash sci2web/bin/sci2web.sh submit");
  if($PHP["?"]){$qerror=true;$error.="$runcode not submited.";goto next;}
  $status=$S2C["submit"];
  mysqlCmd("update runs set run_status='$status' where run_code='$runcode'");
  if($PHP["?"]){$qerror=true;$error.="Failed status submit for $runcode";goto next;}
}

if($PHP["Action"]=="Stop")
{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //STOPPING
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  systemCmd("cd $runpath;bash sci2web/bin/sci2web.sh stop");
  if($PHP["?"]){$qerror=true;$error.="$runcode not stopped.";goto next;}
  $status=$S2C["stop"];
  mysqlCmd("update runs set run_status='$status' where run_code='$runcode'");
  if($PHP["?"]){$qerror=true;$error.="Failed status stop for $runcode";goto next;}
}

if($PHP["Action"]=="Kill")
{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //STOPPING
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  systemCmd("cd $runpath;bash sci2web/bin/sci2web.sh kill");
  if($PHP["?"]){$qerror=true;$error.="$runcode not killed.";goto next;}
  $status=$S2C["kill"];
  mysqlCmd("update runs set run_status='$status' where run_code='$runcode'");
  if($PHP["?"]){$qerror=true;$error.="Failed status kill for $runcode";goto next;}
}

if($PHP["Action"]=="Pause")
{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //PAUSING
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  systemCmd("cd $runpath;bash sci2web/bin/sci2web.sh pause");
  if($PHP["?"]){$qerror=true;$error.="$runcode not paused.";goto next;}
  $status=$S2C["pause"];
  mysqlCmd("update runs set run_status='$status' where run_code='$runcode'");
  if($PHP["?"]){$qerror=true;$error.="Failed status pause for $runcode";goto next;}
}

if($PHP["Action"]=="Resume")
{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //RESUMING
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  systemCmd("cd $runpath;bash sci2web/bin/sci2web.sh resume");
  if($PHP["?"]){$qerror=true;$error.="$runcode not resumed.";goto next;}
  $status=$S2C["resume"];
  mysqlCmd("update runs set run_status='$status' where run_code='$runcode'");
  if($PHP["?"]){$qerror=true;$error.="Failed status resume for $runcode";goto next;}
}


if($PHP["Action"]=="BlockStatus")
{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //BLOCK STATUS
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  $qblock=true;
  while($qblock){
    sleep(2);
    //==================================================
    //GET INFORMATION ABOUT THE RUN
    //==================================================
    $resmat=mysqlCmd("select * from runs where run_code='$runcode'");
    $row=getRow($resmat,0);
    foreach(array_keys($DATABASE["Runs"]) as $runfield){
      $$runfield=$row["$runfield"];
    }
    //==================================================
    //RUNNING DIRECTORY
    //==================================================
    $status=$C2S[$run_status];
    if($status=="submit" or $status=="resume"){
      $cstatus=systemCmd("cd $runpath;bash sci2web/bin/sci2web.sh check");
      //==================================================
      //IF THERE IS STATUS CHANGE STATUS
      //==================================================
      if(preg_match("/--(\w+)--/",$cstatus,$matches)){
	$status=$matches[1];
      }
    }
    systemCmd("cd $runpath;ps $(cat pid.oxt) | tail -n 1 | grep $(cat pid.oxt)");
    if($status=="run" or 
       $status=="pause" or 
       $status=="submit" or
       !$PHP["?"]) 
      $qblock=true;

    else $qblock=false;
  }
}
if($PHP["Action"]=="GetControls")
{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //GET CONTROLS
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //==================================================
  //GET INFORMATION ABOUT THE RUN
  //==================================================
  $resmat=mysqlCmd("select * from runs where run_code='$runcode'");
  $row=getRow($resmat,0);
  foreach(array_keys($DATABASE["Runs"]) as $runfield){
    $$runfield=$row["$runfield"];
  }
  //==================================================
  //RUNNING DIRECTORY
  //==================================================
  $status=$C2S[$run_status];
  if($status=="submit" or $status=="resume"){
    $cstatus=systemCmd("cd $runpath;bash sci2web/bin/sci2web.sh check");
    //==================================================
    //IF THERE IS STATUS CHANGE STATUS
    //==================================================
    if(preg_match("/--(\w+)--/",$cstatus,$matches)){
      $status=$matches[1];
    }
  }
  //==================================================
  //BUTTONS TO DISPLAY
  //==================================================
  $exclude=array();
  if(isset($PHP["ExcludeActions"])){
    $exclude=preg_split("/,/",$PHP["ExcludeActions"]);
  }
  echo getControlButtons($run_code,$status,"controls",$exclude);
  return 0;
}
if($PHP["Action"]=="GetStatus")
{
  $initime_info="";
  $endtime_info="";
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //GET STATUS
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  $resmat=mysqlCmd("select * from runs where run_code='$runcode'");
  $row=getRow($resmat,0);
  foreach(array_keys($DATABASE["Runs"]) as $runfield){
    $$runfield=$row["$runfield"];
  }
  $status=$C2S[$run_status];
  if($status=="submit" or $status=="resume"){
    $cstatus=systemCmd("cd $runpath;bash sci2web/bin/sci2web.sh check");
    //==================================================
    //IF THERE IS STATUS CHANGE STATUS
    //==================================================
    if(preg_match("/--(\w+)--/",$cstatus,$matches)){
      $status=$matches[1];
    }
  }
  $bstatus=systemCmd("cd $runpath;bash sci2web/bin/sci2web.sh status")*100;
  $status_icon=statusIcon($status); 
  if(isset($PHP["Summary"])){
    $result="";
    if($status=="run" or $status=="pause"){
      $result.=statusBar($bstatus);
    }
    $result.="$status_icon";
    goto next;
  }
  if(file_exists("$runpath/time_start.oxt")){
    $initime=systemCmd("cat $runpath/time_start.oxt");
    $initime_date=date("r",$initime);
$initime_info=<<<TIME
<tr><td>Initial time:</td><td>$initime_date</td></tr>
TIME;
  }
  if(file_exists("$runpath/end.sig")){
    $eltime=0;
    $endtime=systemCmd("cat $runpath/time_end.oxt");
    $endtime_date=date("r",$endtime);
    $exetime=$endtime-$initime;
    //==================================================
    //DETERMINE TIME INCLUDING PAUSES
    //==================================================
    if(file_exists("$runpath/time_pause.oxt")){
      $ptimes=loadFile("$runpath/time_pause.oxt");
      $rtimes=loadFile("$runpath/time_resume.oxt");
      $npauses=count($ptimes)-1;
      for($i=0;$i<=$npauses;$i++){
	if($i==0){
	  $dtime=$ptimes[0]-$initime;
	}
	else if($i<$npauses){
	  //$dtime=$ptimes[$i]-$rtimes[$i-1];
	}
	else{
	  $dtime=$endtime-$rtimes[$i-1];
	}
	$eltime+=$dtime;
      }
    }
    if($eltime==0) $eltime=$exetime;
    $exetime=sprintf("%.4f",$exetime);
    $eltime=sprintf("%.4f",$eltime);
$endtime_info=<<<TIME
<tr><td>End time:</td><td>$endtime_date</td></tr>
<tr>
    <td>Elapsed time:<br/><i>including waiting time</i></td>
    <td>$exetime secs</td>
</tr>
<tr><td>Real Execution time:</td><td>$eltime secs</td></tr>
TIME;
  }

  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //COMPLETE BAR
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  if($status=="run" or $status=="pause"){
$status_complete=<<<COMPLETE
<div id="status_text" 
     style="width:200px;
	    text-align:center;
	    border:solid black 1px">
  <div id="status_bar" 
       style="width:$bstatus%;
	      text-align:right;
	      background-color:$COLORS[text];
	      padding:0px;
	      color:$COLORS[dark]">
    $bstatus%
  </div>
</div>
COMPLETE;
  }else{
    $status_complete="";
  }

  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //OUTPUT
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  $reptime=getToday("%year-%mon-%mday %hours:%minutes:%seconds");
$result=<<<STATUS
<h1>Status</h1>
<table>
<tr>
<td>Time of Report:</td><td>$reptime</td>
</tr>
<tr>
<td>Run:</td><td>$run_name</td>
</tr>
<tr>
<td>Code:</td><td>$run_code</td>
</tr>
<tr>
<td>Hash:</td><td>$run_hash</td>
</tr>
<tr>
<td valign="top">Status:</td>
<td>
$status_icon $status_complete
</td>
</tr>
$initime_info
$endtime_info
</table>
STATUS;
}//END STATUS
next:
}//END OF RUN CODES
//////////////////////////////////////////////////////////////////////////////////
//END
//////////////////////////////////////////////////////////////////////////////////
end:

if($qerror){
  echo "<i>An error has occurred:</i><br/>$error";
}else{
  echo $result;
}

?>
