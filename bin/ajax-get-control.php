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
//GET INFORMATION ABOUT THE RUN
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$appname="$_SESSION[AppVersion]";
$appdir="$PROJ[APPSDIR]/$appname";
$apppath="$PROJ[APPSPATH]/$appname";
$runsdir="$PROJ[RUNSDIR]/$_SESSION[User]/$appname";
$runspath="$PROJ[RUNSPATH]/$_SESSION[User]/$appname";

$resmat=mysqlCmd("select * from runs where run_code='$PHP[RunCode]'");
$row=getRow($resmat,0);
foreach(array_keys($DATABASE["Runs"]) as $runfield){
  $$runfield=$row["$runfield"];
}
if(isBlank($run_hash)){
  echo "<p><i>Run does not exists</i></p>";
  return 0;
}
$rundir="$runsdir/$run_hash";
$runpath="$runspath/$run_hash";
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GET STATUS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
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
//////////////////////////////////////////////////////////////////////////////////
//GET ACTIONS
//////////////////////////////////////////////////////////////////////////////////
$display=toggleButtons($status);
$actions=array("Clean","Compile","Run","Pause","Stop","Resume");
$links="";
foreach($actions as $action){
  if($action=="Remove");
  else $break="";
  $actionlink=genRunLink($run_code,$action);
$links.=<<<LINKS
$break
<a id="Bt_$action" href="JavaScript:$actionlink" 
  style="display:$display[$action]">
$BUTTONS[$action]
</a> 
LINKS;
}
//////////////////////////////////////////////////////////////////////////////////
//DISPLAY BUTTONS
//////////////////////////////////////////////////////////////////////////////////
echo<<<CONTROL
<h1>Control</h1>
$links
<p><i id="result"></i></p>
CONTROL;

finalizePage();
?>