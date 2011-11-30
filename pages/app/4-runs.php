<!--Runs-->
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
//GENERATE CONTENT
//////////////////////////////////////////////////////////////////////////////////

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//HEADER
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//COMMON ACTIONS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$files=listFiles("$runspath/templates","*.conf");

$header.="<tr><td colspan=10><div>";

//==================================================
//GENERATE LINKS
//==================================================
$header.=<<<HEADER
<a id="new" 
  href="JavaScript:Open('$conflinknew&Template=Default','Configure','$PROJ[SECWIN]')" onmouseover="explainThis(this)" explanation="Add run">
$BUTTONS[Add]
</a>
HEADER;

//==================================================
//GENERATE OPTIONS 
//==================================================
$bugbut=genBugForm("NewFromTemplate","Problems creating new from template");
$header.=<<<HEADER
New run from template : 
<select name='newrun' 
  onchange="configureNew(this,'$conflinknew','$PROJ[SECWIN]')">
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
$bugbut
HEADER;

$header.="</div></td></tr>";

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//FIELDS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$header.=<<<RUNS
<tr class="header">
  <td width="10%">
  Actions
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
  <td width="10%">
  Control
  </td>
</tr>
RUNS;

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
   },
   function(element,rtext){
   },
   function(element,rtext){
   },
   $timeout,
   true
   )
AJAX;
$onload_runtable=genOnLoad($ajax_runtable,'load');

//////////////////////////////////////////////////////////////////////////////////
//CONTENT
//////////////////////////////////////////////////////////////////////////////////
echo<<<RUNS
<div id="notactions" class="notification" style="display:none"></div>
<h1>Application Queue</h1>
$onload_runtable
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
RUNS;
end:
echo $result;
?>
