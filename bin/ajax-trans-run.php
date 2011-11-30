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
//BASIC INFORMATION
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$qerror=false;

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GET INFORMATION ABOUT THE RUN
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$runcode=$PHP["RunCode"];
$runhash=mysqlGetField("select * from runs where run_code='$runcode'",
		       0,"run_hash");

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//DIRECTORIES
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$appname="$_SESSION[AppVersion]";
$appdir="$PROJ[APPSDIR]/$appname";
$apppath="$PROJ[APPSPATH]/$appname";
$runsdir="$PROJ[RUNSDIR]/$_SESSION[User]/$appname";
$runspath="$PROJ[RUNSPATH]/$_SESSION[User]/$appname";
$rundir="$PROJ[RUNSDIR]/$_SESSION[User]/$appname/$runhash";
$runpath="$PROJ[RUNSPATH]/$_SESSION[User]/$appname/$runhash";
$savedbpath="$PROJ[RUNSPATH]/db/$appname";
$srcpath="$PROJ[RUNSPATH]/src/$appname";

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//FORMS INFORMATION
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//READ PARAMETRIZATION MODEL
list($tabs,$groups,$vars)=readParamModel("$apppath/sci2web/parametrization.info");
 
//////////////////////////////////////////////////////////////////////////////////
//ACTION
//////////////////////////////////////////////////////////////////////////////////
if($PHP["Action"]=="Clean")
{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //CLEANING
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  systemCmd("cd $runpath;bash sci2web/bin/s2w-action.sh cleanall");
  if($PHP["?"]){$qerror=true;$error="Failed script clean";goto end;}
  $status=$S2C["clean"];
  mysqlCmd("update runs set run_status='$status' where run_code='$PHP[RunCode]'");
  if($PHP["?"]){$qerror=true;$error="Failed status clean";goto end;}
}

if($PHP["Action"]=="Compile")
{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //CLEANING
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  systemCmd("cd $runpath;bash sci2web/bin/s2w-action.sh clean");
  if($PHP["?"]){$qerror=true;$error="Failed script clean";goto end;}
  $status=$S2C["clean"];
  mysqlCmd("update runs set run_status='$status' where run_code='$PHP[RunCode]'");
  if($PHP["?"]){$qerror=true;$error="Failed status clean";goto end;}
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //COMPILING
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  systemCmd("cd $runpath;bash sci2web/bin/s2w-action.sh compile");
  if($PHP["?"]){$qerror=true;$error="Failed script compile";goto end;}
  $status=$S2C["compiled"];
  mysqlCmd("update runs set run_status='$status' where run_code='$PHP[RunCode]'");
  if($PHP["?"]){$qerror=true;$error="Failed status compile";goto end;}
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //PREPARING
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  systemCmd("cd $runpath;bash sci2web/bin/s2w-action.sh pre");
  if($PHP["?"]){$qerror=true;$error="Failed script preparing";goto end;}
  $status=$S2C["ready"];
  mysqlCmd("update runs set run_status='$status' where run_code='$PHP[RunCode]'");
  if($PHP["?"]){$qerror=true;$error="Failed status preparing";goto end;}
}

if($PHP["Action"]=="Remove")
{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //REMOVING RUN DIRECTORY
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  systemCmd("rm -rf $runpath");
  if($PHP["?"]){$qerror=true;$error="Failed remove";goto end;}
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //REMOVING DATABASE ENTRY
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  mysqlCmd("delete from runs where run_code='$runcode'");
  if($PHP["?"]){$qerror=true;$error="Failed status remove";goto end;}
}

if($PHP["Action"]=="Run")
{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //SUBMIT
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  systemCmd("cd $runpath;bash sci2web/bin/s2w-action.sh submit");
  if($PHP["?"]){$qerror=true;$error="Failed script submit";goto end;}
  $status=$S2C["submit"];
  mysqlCmd("update runs set run_status='$status' where run_code='$PHP[RunCode]'");
  if($PHP["?"]){$qerror=true;$error="Failed status submit";goto end;}
}

if($PHP["Action"]=="Stop")
{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //STOPPING
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  systemCmd("cd $runpath;bash sci2web/bin/s2w-action.sh stop");
  if($PHP["?"]){$qerror=true;$error="Failed script stop";goto end;}
  $status=$S2C["stop"];
  mysqlCmd("update runs set run_status='$status' where run_code='$PHP[RunCode]'");
  if($PHP["?"]){$qerror=true;$error="Failed status stop";goto end;}
}

if($PHP["Action"]=="Pause")
{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //PAUSING
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  systemCmd("cd $runpath;bash sci2web/bin/s2w-action.sh pause");
  if($PHP["?"]){$qerror=true;$error="Failed script pause";goto end;}
  $status=$S2C["pause"];
  mysqlCmd("update runs set run_status='$status' where run_code='$PHP[RunCode]'");
  if($PHP["?"]){$qerror=true;$error="Failed status pause";goto end;}
}

if($PHP["Action"]=="Resume")
{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //RESUMING
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  systemCmd("cd $runpath;bash sci2web/bin/s2w-action.sh resume");
  if($PHP["?"]){$qerror=true;$error="Failed script resume";goto end;}
  $status=$S2C["resume"];
  mysqlCmd("update runs set run_status='$status' where run_code='$PHP[RunCode]'");
  if($PHP["?"]){$qerror=true;$error="Failed status resume";goto end;}
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
    $resmat=mysqlCmd("select * from runs where run_code='$PHP[RunCode]'");
    $row=getRow($resmat,0);
    foreach(array_keys($DATABASE["Runs"]) as $runfield){
      $$runfield=$row["$runfield"];
    }
    //==================================================
    //RUNNING DIRECTORY
    //==================================================
    $status=$C2S[$run_status];
    if($status=="submit" or $status=="resume"){
      $cstatus=systemCmd("cd $runpath;bash sci2web/bin/s2w-action.sh check");
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
  $resmat=mysqlCmd("select * from runs where run_code='$PHP[RunCode]'");
  $row=getRow($resmat,0);
  foreach(array_keys($DATABASE["Runs"]) as $runfield){
    $$runfield=$row["$runfield"];
  }
  //==================================================
  //RUNNING DIRECTORY
  //==================================================
  $status=$C2S[$run_status];
  if($status=="submit" or $status=="resume"){
    $cstatus=systemCmd("cd $runpath;bash sci2web/bin/s2w-action.sh check");
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
  echo getControlButtons($run_code,$status);
  return 0;
}
if($PHP["Action"]=="GetStatus")
{
  $initime_info="";
  $endtime_info="";
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //GET STATUS
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  $resmat=mysqlCmd("select * from runs where run_code='$PHP[RunCode]'");
  $row=getRow($resmat,0);
  foreach(array_keys($DATABASE["Runs"]) as $runfield){
    $$runfield=$row["$runfield"];
  }
  $status=$C2S[$run_status];
  if($status=="submit" or $status=="resume"){
    $cstatus=systemCmd("cd $runpath;bash sci2web/bin/s2w-action.sh check");
    //==================================================
    //IF THERE IS STATUS CHANGE STATUS
    //==================================================
    if(preg_match("/--(\w+)--/",$cstatus,$matches)){
      $status=$matches[1];
    }
  }
  $status_icon=statusIcon($status); 
  $bstatus=systemCmd("cd $runpath;bash sci2web/bin/s2w-action.sh status")*100;
  if(file_exists("$runpath/time_start.oxt")){
    $initime=systemCmd("cat $runpath/time_start.oxt");
    $initime_date=date("r",$initime);
$initime_info=<<<TIME
<tr><td>Initial time:</td><td>$initime_date</td></tr>
TIME;
  }
  if(file_exists("$runpath/end.sig")){
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
  //OUTPUT
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$result=<<<STATUS
<h1>Status</h1>
<table>
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
<td>Status:</td><td>$status_icon</td>
</tr>
<tr>
<td>Complete:</td><td>
<div id="status_text" 
  style="width:200px;text-align:center;border:solid black 1px">
  <div id="status_bar" 
  style="width:$bstatus%;text-align:right;background-color:$COLORS[text];padding:0px;color:$COLORS[dark]">
  $bstatus%
  </div>
  </div>
</td>
</tr>
$initime_info
$endtime_info
</table>
STATUS;
}//END STATUS

//////////////////////////////////////////////////////////////////////////////////
//END
//////////////////////////////////////////////////////////////////////////////////
end:

if($qerror){
  echo "<i>An error has occurred:</i>$error";
}else{
  echo $result;
}

?>
