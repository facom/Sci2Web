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
$RELATIVE="../..";
include("$RELATIVE/lib/sci2web.php");
?>

<?
//////////////////////////////////////////////////////////////////////////////////
//GLOBAL VARIABLES
//////////////////////////////////////////////////////////////////////////////////

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//INITIALIZATION
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$result="";
$controlpanel="";
$tablehead="";
$footer="";
$timeout=2000;
$conflinknew="$PROJ[BINDIR]/configure.php?Action=New";
$sname="";
$sdate="";
$sstatus="";
$check="";

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//CHECK AUTHORIZATION
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
if(!isset($_SESSION["User"])){
$result=<<<RUNS
<center>
<p class="error">
You have to signup before to access the application queue
</p>
</center>
RUNS;
goto end;
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//DIRECTORIES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$appname="$_SESSION[AppVersion]";
$appdir="$PROJ[APPSDIR]/$appname";
$apppath="$PROJ[APPSPATH]/$appname";
$runsdir="$PROJ[RUNSDIR]/$_SESSION[User]/$appname";
$runspath="$PROJ[RUNSPATH]/$_SESSION[User]/$appname";
$savedbpath="$PROJ[RUNSPATH]/db/$appname";

//RUNS PIPELINE
readConfig("$apppath/sci2web/version.conf");
$VERCONFIG=$CONFIG;

//////////////////////////////////////////////////////////////////////////////////
//LOAD ACTION
//////////////////////////////////////////////////////////////////////////////////
function runTable($order="configuration_date"){
  global $PHP,$PROJ;
$ajax_runtable=<<<AJAX
query=$('#filterquery').attr('value');
loadContent
  (
   '$PROJ[BINDIR]/ajax-get-runs.php?$PHP[QSTRING]&Order=$order&FilterQuery='+query,
   'runs_table',
   function(element,rtext){
     $(element).html(rtext);
     $('#DIVBLANKET').css('display','none');
     $('#DIVOVER').css('display','none');
   },
   function(element,rtext){
     //*
     $('#DIVBLANKET').css('display','block');
     $('#DIVOVER').css('display','block');
     //*/
     /*
     $('#DIVBLANKET').css('display','none');
     $('#DIVOVER').css('display','none');
     */
   },
   function(element,rtext){
   },
   -1,
   true
   );
$('input[name=RunAll]').attr('checked',false);
$('input[name=RunAll_Submit]').attr('checked',false);
$('input[name=RunAll_Submit]').attr('value','off');
AJAX;
 return $ajax_runtable;
}
$ajax_runtable=runTable();
$onload_runtable=genOnLoad(runTable(),'load');

$errfile="$PHP[TMPDIR]/phpout-ajax-trans-run-$PHP[SESSID]";
$ajax_action=<<<AJAX
submitForm
  ('formqueue',
   '$PROJ[BINDIR]/ajax-trans-run.php?',
   'notaction',
   function(element,rtext){
    elid=$(element).attr('id');
    eliderr=elid+'_error';
    $('#'+eliderr).css('display','none');
    if(rtext.search(/error/i)>=0){
      $('#'+eliderr).
	html(rtext+'<br/><a href=$errfile target=_blank>See detailed errors</a>');
      $('#'+eliderr).css('display','block');
    }else{
      notDiv(eliderr,rtext);
    }
    $ajax_runtable;
   },
   function(element,rtext){
    elid=$(element).attr('id');
    eliderr=elid+'_error';
   },
   function(element,rtext){
     elid=$(element).attr('id');
   }
   )
AJAX;

//////////////////////////////////////////////////////////////////////////////////
//GENERATE CONTENT
//////////////////////////////////////////////////////////////////////////////////

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//CONTROL PANEL
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

$controlpanel.=<<<HEADER
<tr id="controlpanel" 
    style="display:none"><td colspan=10>
<div style="position:relative;
	    border:solid black 1px;
	    padding:10px;
	    border-radius:10px;
	    background-color:$COLORS[clear];
	    ">
  <div style="position:absolute;
	      top:5px;
	      right:5px;
	      ">
    <a href="JavaScript:void(null)"
       onclick="$('#controlpanel').toggle('fast',null)"
       >
      $BUTTONS[Cancel]
    </a>
  </div>
HEADER;

//==================================================
//NEW FROM TEMPLATE
//==================================================
$files=listFiles("$runspath/templates","*.conf");
list($bugbut,$bugform_new)=
  genBugForm2("NewFromTemplate",
	      "Problems creating new from template",
	      $VERCONFIG["EmailsContributors"]);

$controlpanel.=<<<HEADER
<div class="actionbutton">
  <big>New from template:</big>
</div>
<div class="actionbutton">
<select name="Template" onchange="popOutHidden(this)">
HEADER;
foreach($files as $file){
  preg_match("/(.+)\.conf/",$file,$matches);
  $template=$matches[1];
  $parts=preg_split("/_/",$template);
  $tempname=implode(" ",$parts);
  $controlpanel.="<option id='Template_$template' value='$template'>$tempname";
}
$controlpanel.=<<<HEADER
</select>
<input type="hidden" name="Template_Submit" value="Default">
HEADER;

$controlpanel.=<<<HEADER
<select name="NumRuns" onchange="popOutHidden(this)">
HEADER;
for($i=1;$i<=10;$i++){
  $controlpanel.="<option value='$i'>$i";
}
$controlpanel.=<<<HEADER
</select>
  <input type="hidden" name="NumRuns_Submit" value="1">
HEADER;

$controlpanel.=<<<HEADER
</div>
<div class="actionbutton">
  <button class="image" id="new" 
	  onclick="notDiv('notaction_error','Processing...');
		   $('#actionspec').attr('value','New');
		   $ajax_action;"
	  onmouseover="explainThis(this)" 
	  explanation="Add run from template">
    $BUTTONS[Add]
  </button>
</div>
<div class="actionbutton">
  <button class="image" id="new" 
	  onclick="notDiv('notaction_error','Processing...');
		   $('#actionspec').attr('value','RemoveTemplate');
		   $ajax_action;"
	  onmouseover="explainThis(this)" 
	  explanation="Remove template">
    $BUTTONS[Remove]
  </button>
</div>
<div class="actionbutton">
  $bugbut
</div>
HEADER;

//==================================================
//CONTROL BUTTONS
//==================================================
list($bugbut,$bugform_control)=
  genBugForm2("ControlPanel",
	      "Problems performing actions",
	      $VERCONFIG["EmailsContributors"]);

$controlpanel.=<<<HEADER
<br/>
<div class="actionbutton">
  <big>Valid actions:</big>
</div>
HEADER;

$noactions=preg_split("/,/",$VERCONFIG["InvalidActions"]);
$actions=array_diff($PROJ["Actions"],$noactions);
array_unshift($actions,"Remove");
$links="";
foreach($actions as $action){
  if($action=="Remove") continue;
  $actionlink=$ajax_action;
$links.=<<<LINKS
<div class="actionbutton">
<button class="image" id="Bt_$action" 
	onclick="notDiv('notaction_error','Processing...');
		 $('#actionspec').attr('value','$action');
		 $ajax_action;
		 " 
	onmouseover="explainThis(this)" 
	explanation="$action"
	>
$BUTTONS[$action]
</button> 
</div>
LINKS;
}
$controlpanel.=<<<BUTTONS
$links
BUTTONS;

$controlpanel.=<<<BUTTONS
$bugbut
</td></tr>
</div>
BUTTONS;

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//HEADER
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//REMOVE, FILTER AND UPDATE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
list($bugbut,$bugform_filter)=
  genBugForm2("History",
	      "Problems filtering",
	      $VERCONFIG["EmailsContributors"]);

$tablehead.=<<<HEADER
<tr><td colspan=10>
    <div style="position:relative">
      <div class="actionbutton">
	<button class="image" id="Bt_Remove" 
		onclick="notDiv('notaction_error','Processing...');
			 $('#actionspec').attr('value','Remove');
			 $ajax_action;
			 " 
		onmouseover="explainThis(this)" 
		explanation="Remove runs"
		>
	  $BUTTONS[Remove]
	</button>
      </div>
      <div class="actionbutton">
	<a href="JavaScript:void(null)"
	   onclick="$('#controlpanel').toggle('slow',null)"
	   >
	  Control Panel
	</a>
      </div>
      <div style="position:absolute;top:0px;right:0px">
	<div class="actionbutton">
	  <label><big>Filter runs:</big></label>
	  <input id="filterquery" type="text" name="searchruns" size="50">
	</div>  
	<div class="actionbutton">
	  <button class="image"
		  onclick="$ajax_runtable"
		  onmouseover="explainThis(this)"
		  explanation="Filter Runs">
	    $BUTTONS[Search]
	  </button>
	</div>  
	<div class="actionbutton">
	    $bugbut
	</div>
	<div class="actionbutton">
	  <button class="image" 
		  onclick="$('#notaction_error').css('display','none');
			   $ajax_runtable"
		  onmouseover="explainThis(this)"
		  explanation="Update list">
	    $BUTTONS[Update]
	  </button>
	</div>
      </div>
    </div>
</td></tr>
HEADER;

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//FIELDS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function sortArrow($action){
  global $PROJ,$PHP,$BUTTONS;
  $arrows=$BUTTONS["Sort"];
$str=<<<ARROW
<a href="JavaScript:void(null)"	
   onclick="$action">
$BUTTONS[Sort]
</a>
ARROW;
  return $str;
}
$sdate=sortArrow(runTable("configuration_date"));
$stemplate=sortArrow(runTable("run_template"));
$sname=sortArrow(runTable("run_name"));
$sstatus=sortArrow(runTable("run_status"));

$tablehead.=<<<RUNS
<tr class="header">
  <td width="2%">
  <input type="checkbox" name="RunAll" checked="false"
	 onclick="selectAll('formqueue',this)"
	 onchange="popOutHidden(this)">
  <input id="runall" type="hidden" name="RunAll_Submit" checked="false">
  </td>
  <td width="20%">
    Time
    $sdate
  </td>
  <td width="10%">
    Template 
    $stemplate
  </td>
  <td width="20%">
    Name of run
    $sname
  </td>
  <td width="20%">
    Owner
  </td>
  <td width="20%">
    Status
    $sstatus
  </td>
</tr>
RUNS;

//////////////////////////////////////////////////////////////////////////////////
//CONTENT
//////////////////////////////////////////////////////////////////////////////////
$TabNext=$PHP["TabId"]+1;
$newcode=genRandom(8);

echo<<<RUNS
<div id="notactions" class="notification" style="display:none"></div>


<!--
<div style="width:29%;
	    padding:2%;
	    background-color:lightgreen;
	    display:inline-block">
  <center>
  <big style="font-size:40px">
    Run
  </big>
  </center>
  <p>
    Create now a new run, configure and run it!
  </p>
</div>
-->

<h1>Runs</h1>

<p>In this page you will be able to 

run the application on the fly (<a href="#RunNow">run now</a>), 

create single or multiple instances of the application from predefined
or custom templates and control the running of these instances
(<a href="#History"
onclick="$('#controlpanel').toggle('slow',null)">control panel</a>) or

simply check out the list of runs commited by you
(<a href="#History">history</a>).

</p>

<a name="RunNow"></a>
<button style="font-size:40px"
	onclick="notDiv('notaction_error','Processing...');
		 newcode=randomStr(8);
		 $('#actionspec').attr('value','New');
		 $('#newcode').attr('value',newcode);
		 $ajax_action;
		 Open('$PROJ[BINDIR]/confresults.php?RunCode='+newcode,'Google','')
		 /*setTimeout('Open(\'$PROJ[BINDIR]/confresults.php?RunCode='+newcode+'\',\'Google\',\'$PROJ[SECWIN]\')',500);*/
		 "
	onmouseover="explainThis(this)" 
	explanation="Create a new run">
  <div class="actionbutton">
    Run!
  </div>
  <div class="actionbutton">
    <img class="image" src="$PROJ[IMGDIR]/icons/actions/Run.gif"/>
  </div>
</button>

<a name="History"></a>
<h1>History</h1>
$onload_runtable
<form action="JavaScript:void(null)" id="formqueue">
  <input id="actionspec" type="hidden" name="Action_Submit" value="None">
  <input id="newcode" type="hidden" name="NewCode_Submit" value="00000000">
  <input type="hidden" name="RunMultiple_Submit" value="true">
  <div id="notaction" class="subnotification" style="display:none"></div>
  <div style="position:relative;
	      padding:5px;
	      border:dashed $COLORS[text] 0px">
    $PROJ[DIVBLANKET]
    $PROJ[DIVOVER]
    <table class="queue">
      <thead>
	$controlpanel
	$tablehead
      </thead>
      <tbody id="runs_table">
      </tbody>
      <tfooter>
	$footer
      </tfooter>
    </table>
</form>
<div id="notaction_error" class="suberror" style="display:none"></div>
$bugform_new
$bugform_control
$bugform_filter
</div>
RUNS;
end:
echo $result;
?>
