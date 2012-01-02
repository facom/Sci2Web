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
$queue="";
if(!isset($PHP["Order"])){
  $PHP["Order"]="configuration_date";
}
if(!isset($PHP["OrderDirection"])){
  $PHP["OrderDirection"]="";
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//STRINGS
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$remdisp="";
$i=0;
$runcodes="";
$extraselect="";

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
if(!isBlank($PHP["FilterQuery"])){
   $extraselect="and ($PHP[FilterQuery])";
}
$runs=mysqlCmd("select * from runs 
where users_email='$_SESSION[User]' and apps_code='$_SESSION[App]' and versions_code='$_SESSION[Version]' $extraselect
order by $PHP[Order] $PHP[OrderDirection]");
if($PHP["?"]){
   $runs=mysqlCmd("select * from runs 
where users_email='$_SESSION[User]' and apps_code='$_SESSION[App]' and versions_code='$_SESSION[Version]'
order by $PHP[Order]");
   echo genOnLoad("$('#filterquery').css('background-color','pink')","error");
 }else{
   echo genOnLoad("$('#filterquery').css('background-color','lightgreen')","error");
 }

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
  $run_template=$run["run_template"];
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
  $PHP["RunCode"]=$run_code;
  $status_icon=statusIcon($status); 
  //echo "<textarea>$status_icon</textarea>";br();

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

  $browselink="$PROJ[BINDIR]/file.php?Dir=$runsdir&File=$run_hash&Mode=View";
  $browselink="Open('$browselink','Configure','$PROJ[SECWIN]')";

  $reslink="$PROJ[BINDIR]/results.php?RunCode=$run_code&HeightWindow=75%";
  $reslink="Open('$reslink','Results','$PROJ[SECWIN]')";

  $confreslink="$PROJ[BINDIR]/confresults.php?RunCode=$run_code";
  $confreslink="Open('$confreslink','Configure & Results','$PROJ[PLOTWIN]')";

  $fullstatuslink="$PROJ[BINDIR]/watch.php?Watch=FullStatus&RunCode=$run_code";
  $fullstatuslink="Open('$fullstatuslink','Full job Status','$PROJ[SECWIN]')";

$ajax_down=<<<AJAX
action=$('#actiondown').attr('value');
loadContent
  (
   '$PROJ[BINDIR]/ajax-trans-run.php?RunCode=$run_code&Action='+action,
   'notaction_error',
   function(element,rtext){
     $(element).html(rtext);
     $(element).fadeIn(2000,null);
   },
   function(element,rtext){
   },
   function(element,rtext){
   }
   -1,
   true
   );
AJAX;

  $downresultslink="$('#actiondown').attr('value','DownloadResults');$ajax_down";
  $downsourceslink="$('#actiondown').attr('value','DownloadSources');$ajax_down";

  $removelink=genRunLink($run_code,"Remove");

  //==================================================
  //TABLE ROW
  //==================================================
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //CHECK
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$row_check=<<<ROW
<td>
  <input type="checkbox" name="Run$i" 
	 onchange="popOutHidden(this);"
	 onclick="deselectAll('RunAll');
		  clickRow(this);"
	 color_unchecked="$COLORS[clear]"
	 color_checked="$COLORS[text]">
  <input type="hidden" name="Run${i}_Submit" checked="false">
</td>
ROW;

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //NAME
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$row_name=<<<ROW
<td id="name$i" 
    onmouseover="
		 $('#actionarrow$i').css('display','block');
		 "
    onmouseout="
		$('#actionarrow$i').css('display','none');
		">
  <div style="position:relative">
    <b onmouseover="explainThis(this)"
       explanation="Code $run_code, Hash $run_hash">
      $run_name
    </b>
    <div id="actionarrow$i" 
	 style="display:none;
		position:absolute;
		right:0px;top:0px">
      <a href="JavaScript:void(null)" 
	 onclick="
		  toggleElement('runactions$i');
		  ">
	$BUTTONS[Down]
      </a>
    </div>
    <div id="runactions$i"
	 class="contextual"
	 style="position:absolute;
		right:0px;
		display:none;
		text-align:left;
		font-size:12px;"
	 onmouseover="$('#runactions$i').css('display','block');"
	 onmouseout="$('#runactions$i').css('display','none');"
	 >
      $BUTTONS[Configure] 
      <a href="JavaScript:$conflink">
	Configure 
      </a>
      <br/>
      $BUTTONS[Results] 
      <a href="JavaScript:$reslink">
	Results
      </a>
      <br/>
      $BUTTONS[ConfigureResults] 
      <a href="JavaScript:$confreslink">
	Control panel 
      </a>
      <br/>
      $BUTTONS[Open] 
      <a href="JavaScript:$fullstatuslink">
	Run status
      </a>
      <br/>
      $BUTTONS[Down] 
      <input id="actiondown" type="hidden" value="">
      <a href="JavaScript:void(null)" 
	 onclick="$downresultslink;">
	Download results (press once)
      </a>
      <br/>
      $BUTTONS[Compile] 
      <a href="JavaScript:void(null)" 
	 onclick="$downsourceslink">
	Download sources (press once)
      </a>
      <br/>
      $BUTTONS[Browse] 
      <a href="JavaScript:void(null)" 
	 onclick="$browselink">
	Browse run
      </a>
    </div>
  </div>
</td>
ROW;

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //DATE
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$row_date=<<<ROW
<td>$configuration_date</td>
ROW;

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //TEMPLATE
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$row_template=<<<ROW
<td>$run_template</td>
ROW;

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //STATUS
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$row_status=<<<ROW
<td>$status_icon</td>
ROW;

$queue.=<<<QUEUE
<tr class="entry">
  $row_check
  $row_name
  $row_date
  $row_template
  $row_status
</tr>
QUEUE;
 $i++;
}
echo $queue;
echo<<<DEBUG
<tr><td colspan=10>
    <!--<textarea rows=10 cols=50>$queue</textarea>-->
    <!--Filter Query: $PHP[FilterQuery]-->
</td></tr>
DEBUG;

end:
echo<<<RUNS
<input type="hidden" name="RunNum_Submit" value="$i">
<input type="hidden" name="RunCodes_Submit" value="$runcodes">
RUNS;

finalizePage();
return 0;
?>
