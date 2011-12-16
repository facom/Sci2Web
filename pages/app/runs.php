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
$header="";
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

//////////////////////////////////////////////////////////////////////////////////
//LOAD ACTION
//////////////////////////////////////////////////////////////////////////////////
$ajax_runtable=<<<AJAX
loadContent
  (
   '$PROJ[BINDIR]/ajax-get-runs.php?$PHP[QSTRING]',
   'runs_table',
   function(element,rtext){
     $(element).html(rtext);
     $('#DIVBLANKET').css('display','none');
     $('#DIVOVER').css('display','none');
   },
   function(element,rtext){
     $('#DIVBLANKET').css('display','block');
     $('#DIVOVER').css('display','block');
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
$onload_runtable=genOnLoad($ajax_runtable,'load');

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
    /*notDiv(elid,'Processing...');*/
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
//HEADER
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$files=listFiles("$runspath/templates","*.conf");

//==================================================
//START HEADER
//==================================================
$header.=<<<HEADER
<tr>
<td colspan=10>
<div style="position:relative">
<div id="notaction" class="subnotification" style="display:none"></div>
<input id="actionspec" type="hidden" name="Action_Submit" value="None">
<input type="hidden" name="RunMultiple_Submit" value="true">
HEADER;

//==================================================
//CONTROL BUTTONS
//==================================================
$actions=$PROJ["Actions"];
array_unshift($actions,"Remove");
$links="";
foreach($actions as $action){
  $actionlink=$ajax_action;
$links.=<<<LINKS
<div class="actionbutton">
<button class="image" id="Bt_$action" 
	onclick="$('#actionspec').attr('value','$action');
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
$header.=<<<BUTTONS
$links
BUTTONS;

//==================================================
//GENERATE LINKS
//==================================================
//$actionlink="Open('$conflinknew&Template=Default','Configure','$PROJ[SECWIN]')";
$actionlink=$ajax_action;
$header.=<<<HEADER
<div class="actionbutton">
HEADER;

//==================================================
//GENERATE LIST OF TEMPLATES
//==================================================
$bugbut=genBugForm("NewFromTemplate","Problems creating new from template");
$header.=<<<HEADER
Template: 
<select name='Template' onchange='popOutHidden(this)'>
HEADER;
foreach($files as $file){
  preg_match("/(.+)\.conf/",$file,$matches);
  $template=$matches[1];
  $parts=preg_split("/_/",$template);
  $tempname=implode(" ",$parts);
  $header.="<option value='$template'>$tempname";
}
$header.=<<<HEADER
</select>
<input type="hidden" name="Template_Submit" value="Default">
HEADER;

$header.=<<<HEADER
</div>
<div class="actionbutton">
<button class="image" id="new" 
	onclick="$('#actionspec').attr('value','New');
		 $ajax_action;"
	onmouseover="explainThis(this)" 
	explanation="Add run">
$BUTTONS[Add]
</button>
</div>
HEADER;

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//UPDATE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$header.=<<<HEADER
<div class="actionbutton"
     style="position:absolute;right:0px;top:0px;">
  <button class="image" onclick="$ajax_runtable">
    $BUTTONS[Update]
  </button>
</div>
HEADER;

$header.=<<<HEADER
</div>
</td></tr>
HEADER;

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//FIELDS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$header.=<<<RUNS
<tr class="header">
  <td width="2%">
  <input type="checkbox" name="RunAll" checked="false"
	 onclick="selectAll('formqueue',this)"
	 onchange="popOutHidden(this)">
  <input id="runall" type="hidden" name="RunAll_Submit" checked="false">
  </td>
  <td width="20%">
  Time $sdate</td>
  <td width="20%">
  Name of run
  </td>
  <td width="20%">
  Owner
  </td>
  <td width="20%">
  Status$sstatus
  </td>
</tr>
RUNS;

//////////////////////////////////////////////////////////////////////////////////
//CONTENT
//////////////////////////////////////////////////////////////////////////////////
echo<<<RUNS
<div id="notactions" class="notification" style="display:none"></div>
<h1>Application Queue</h1>
$onload_runtable
<div style="position:relative;padding:5px;border:dashed $COLORS[text] 0px">
$PROJ[DIVBLANKET]
$PROJ[DIVOVER]
<form action="JavaScript:void(null)" id="formqueue">
<table class="queue">
<thead>
$header
</thead>
<tbody id="runs_table">
</tbody>
<tfooter>
$footer
</tfooter>
</table>
</form>
<div id="notaction_error" class="suberror" style="display:none"></div>
</div>
RUNS;
end:
echo $result;
?>
