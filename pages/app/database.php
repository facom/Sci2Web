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
$sname="";
$sdate="";
$sstatus="";
$check="";

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

//////////////////////////////////////////////////////////////////////////////////
//HEADER
//////////////////////////////////////////////////////////////////////////////////
$header.=<<<HEADER
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
HEADER;

//////////////////////////////////////////////////////////////////////////////////
//DBRESULT
//////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////
//FOOTER
//////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////
//DB SPECS
//////////////////////////////////////////////////////////////////////////////////
list($tabs,$groups,$vars)=readParamModel("$apppath/sci2web/controlvars.info");
$dbvars="";
foreach($tabs as $tab){
  foreach($groups[$tab] as $group){
    foreach($vars[$tab][$group] as $var){
      list($var,$defval,$datatype,$varname,$vardesc)=
	split("::",$var);
$dbvars.=<<<DB
<tr>
  <td class="term">$var:</td>
  <td class="definition">$varname ($datatype)</td>
</tr>
DB;
    }
  }
}

//////////////////////////////////////////////////////////////////////////////////
//CONTENT
//////////////////////////////////////////////////////////////////////////////////
list($bugbut,$bugform)=genBugForm2("SubmitDatabase","Submission problems");
echo<<<RUNS
<div id="notactions" class="notification" style="display:none"></div>
<h1>Database of Results</h1>
<p>$SCI2WEB creates a database with the results computed by all the users
using this application.  You can search in this database and recover
already computed results.</p>

<p>
Use the following form to perform <i>select-type</i> queries.  See the
Database specification to know the available search variables.
</p>
<div style="position:relative">
<form action="JavaScript:void(null)" 
      onsubmit="queryResultsDatabase(this,'sqlresults','$PROJ[BINDIR]/ajax-query-database.php?')">
<table>
  <tr>
    <td valign="top"><b>SQL Query</b>:</td>
    <td>
      <span style="font-family:courier">
	select * from ${_SESSION["App"]}_${_SESSION["VersionId"]} where
      </span>
      <br/>
      <textarea name="sqlquery" cols="100" rows="5"></textarea>
      <br/>
      <a href="JavaScript:void(null)" onclick="toggleElement('dbspecs')">
	<div style="position:relative">
	  Database specification
      </a>
      <div id="dbspecs" class="displayable" style="display:none">
	<table class="description">
	  <tr>
	    <td class="term">Application:</td><td class="definition">$_SESSION[App]</td>
	  </tr>
	  <tr>
	    <td class="term">Version:</td><td class="definition">$_SESSION[Version]</td>
	  </tr>
	  <tr>
	    <td class="term" style="text-align:center;border-bottom:solid $COLORS[dark] 1px" 
		colspan="2">
	      Variables
	    </td>
	  </tr>
	  $dbvars
	</table>
      </div>
    </td>
  </tr>
  <tr>
    <td></td>
    <td>
      Example: 
      <span style="font-family:courier">
	Field1>0 and Field2<10
      </span>
    </td>
  </tr>
<tr>
  <td colspan="3">
    <button name="ActionDatabase">Submit</button>$bugbut
  </td>
</tr>
</table>
</form>
$bugform
</div>

<p>SQL Results:</p>

<div id="sqlresults"
     style="width:98%;
	    position:relative;
	    background-color:$COLORS[clear];
	    padding:1%;
	    font-size:14px;
	    border:dashed $COLORS[dark] 2px">
  $PROJ[DIVBLANKET]
  $PROJ[DIVOVER]
  <big>No results</big>
</div>

RUNS;
end:
echo $result;
?>
