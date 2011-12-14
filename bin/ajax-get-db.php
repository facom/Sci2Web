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

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//DIRECTORIES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$appname="$_SESSION[AppVersion]";
$appdir="$PROJ[APPSDIR]/$appname";
$apppath="$PROJ[APPSPATH]/$appname";
$runsdir="$PROJ[RUNSDIR]/$_SESSION[User]/$appname";
$runspath="$PROJ[RUNSPATH]/$_SESSION[User]/$appname";
$savedbpath="$PROJ[RUNSPATH]/db/$appname";

//CREATE APPLICATION DIRECTORIES FOR THE USER IN CASE THEY DO NOT EXIST
if(!is_dir("$runspath/templates")){
  systemCmd("mkdir -p $runspath/templates");
  systemCmd("cp -rf $apppath/sci2web/templates/*.conf $runspath/templates");
}
if(!is_dir("$savedbpath")) systemCmd("mkdir -p $savedbpath");

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//GET DATABASE INFORMATION
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$runs=mysqlCmd("select * from runs 
where users_email='$_SESSION[User]' 
order by $PHP[Order]");

//////////////////////////////////////////////////////////////////////////////////
//ACTION
//////////////////////////////////////////////////////////////////////////////////
if(!is_array($runs)){
  echo "<tr><td colspan=10><p class='error'>No runs available</p></td></tr>";
  goto end;
}
$i=1;
foreach($runs as $run){

  //==================================================
  //RECOVER INFORMATION
  //==================================================
  $run_code=$run["run_code"];
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
  $conflink="$PROJ[BINDIR]/configure.php?RunCode=$run_code";
  $reslink="$PROJ[BINDIR]/results.php?RunCode=$run_code";
  $removelink=genRunLink($run_code,"Remove");

$action_buttons=<<<BUTTONS
<a id="Bt_Remove" href="JavaScript:$removelink" 
   style="$remdisp"
   onmouseover="explainThis(this)"
   explanation="Remove"
   onclick="notDiv('notactions','Removing')">
  $BUTTONS[Remove]
</a> 
<a href="JavaScript:Open('$conflink','Configure','$PROJ[SECWIN]')"
   onmouseover="explainThis(this)" explanation="Configure">
  $BUTTONS[Configure]
</a> 
<a href="JavaScript:Open('$reslink','Results','$PROJ[SECWIN]')"
   onmouseover="explainThis(this)" explanation="Results">
  $BUTTONS[Results]
</a>
BUTTONS;

  //==================================================
  //CONTROL BUTTONS
  //==================================================
  $runcontrols=getControlButtons($run_code,$status,"run$i");

  //==================================================
  //TABLE ROW
  //==================================================
echo <<<QUEUE
<tr class="entry">
  <td>$action_buttons</td>
  <td>$configuration_date</td>
  <td>
    <a href="JavaScript:Open('$conflink','Configure','$PROJ[SECWIN]')" 
       onmouseover="explainThis(this)" explanation="Configure $run_hash">
      $run_name
    </a>
  </td>
  <td>$users_email</td>
  <td>$status_icon$bstatus</td>
  <td>
  <div style="position:relative">
  <div class="controlbox">
  $runcontrols
  </div>
  </div>
  </td>
</tr>
QUEUE;
 $i++;
}

echo "<input type='hidden' name='NumberRuns' value='$i'>";
end:
return 0;
?>
