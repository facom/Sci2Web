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
# TAB PAGE
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
*/
//////////////////////////////////////////////////////////////////////////////////
//LOAD PACKAGE
//////////////////////////////////////////////////////////////////////////////////
$RELATIVE="../";
include("$RELATIVE/lib/sci2web.php");

//////////////////////////////////////////////////////////////////////////////////
//GLOBAL VARIABLES
//////////////////////////////////////////////////////////////////////////////////
if(!isset($PHP["Order"])){$PHP["Order"]="configuration_date";}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//STRINGS
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$remdisp="";
$i=0;
$runcodes="";

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//DIRECTORIES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$appname="$_SESSION[AppVersion]";
$appdir="$PROJ[APPSDIR]/$appname";
$apppath="$PROJ[APPSPATH]/$appname";
$runsdir="$PROJ[RUNSDIR]/$_SESSION[User]/$appname";
$runspath="$PROJ[RUNSPATH]/$_SESSION[User]/$appname";
$savedbpath="$PROJ[RUNSPATH]/db/$appname/templates";
if(!is_dir("$runspath/templates")){
  systemCmd("mkdir -p $runspath/templates");
  systemCmd("cp -rf $apppath/sci2web/templates/*.conf $runspath/templates");
}
if(!is_dir("$savedbpath")) systemCmd("mkdir -p $savedbpath");

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//GET DATABASE INFORMATION
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$runs=mysqlCmd("select * from runs 
where users_email='$_SESSION[User]' and apps_code='$_SESSION[App]' and versions_code='$_SESSION[Version]'
order by $PHP[Order]");

//////////////////////////////////////////////////////////////////////////////////
//ACTION
//////////////////////////////////////////////////////////////////////////////////
if(!is_array($runs)){
  echo "<tr><td colspan=10><p class='error'>No runs available</p></td></tr>";
  goto end;
}
$i=1;
$runcodes="";
foreach($runs as $run){

  //==================================================
  //RECOVER INFORMATION
  //==================================================
  $run_code=$run["run_code"];
  $runcodes.="$run_code;";
  $run_hash=$run["run_hash"];
  $run_name=$run["run_name"];
  $configuration_date=$run["configuration_date"];
  $users_email=$run["users_email"];
  $status=$C2S[$run["run_status"]];
  $rundir="$runsdir/$run_hash";
  $runpath="$runspath/$run_hash";

  //==================================================
  //CHECK STATUS
  //==================================================
  if($status=="submit" or $status=="resume"){
    $cstatus=systemCmd("cd $runpath;bash sci2web/bin/sci2web.sh check");
    //IF THERE IS RUN STATUS, CHANGE STATUS
    if(preg_match("/--(\w+)--/",$cstatus,$matches)){
      $status=$matches[1];
    }
  }
  $status_icon=statusIcon($status); 

  //==================================================
  //STATUS BAR
  //==================================================
  $bstatus="";
  if($status=="run" or $status=="pause"){
    $bstatus=systemCmd("cd $runpath;bash sci2web/bin/sci2web.sh status")*100;
    $bstatus=getStatusBar($bstatus);
  }

  //==================================================
  //ACTION BUTTONS
  //==================================================
  $conflink="$PROJ[BINDIR]/configure.php?RunCode=$run_code&HeightWindow=68%";
  $conflink="Open('$conflink','Configure','$PROJ[SECWIN]')";
  $reslink="$PROJ[BINDIR]/results.php?RunCode=$run_code&HeightWindow=75%";
  $reslink="Open('$reslink','Results','$PROJ[SECWIN]')";
  $removelink=genRunLink($run_code,"Remove");
  if($status=="run" or $status=="pause"){
    $conflink="alert('You cannot configure a running instance.')";
    $removelink="alert('You cannot remove a running instance.')";
  }

  //==================================================
  //TABLE ROW
  //==================================================
echo <<<QUEUE
<tr class="entry">
  <td>
    <input type="checkbox" name="Run$i" value="$run_code"
	   onchange="popOutHidden(this);">
  </td>
  <td>$configuration_date</td>
  <td>
    $run_name
    <a href="JavaScript:$conflink" 
       onmouseover="explainThis(this)" explanation="Configure $run_code ($run_hash)">
       $BUTTONS[Configure]
    </a>
    <a href="JavaScript:$reslink" 
       onmouseover="explainThis(this)" explanation="Results for $run_code ($run_hash)">
       $BUTTONS[Results]
    </a>
    <a href="JavaScript:$conflink" 
       onmouseover="explainThis(this)" explanation="Configure $run_code ($run_hash)">
       $BUTTONS[ConfigureResults]
    </a>
  </td>
  <td>$users_email</td>
  <td>$status_icon$bstatus</td>
</tr>
QUEUE;
 $i++;
}

end:
echo<<<RUNS
<input type="hidden" name="RunNum_Submit" value="$i">
<input type="hidden" name="RunCodes_Submit" value="$runcodes">
RUNS;
return 0;
?>
