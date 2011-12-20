<!--Applications-->
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
# WEB PAGE
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
*/
//////////////////////////////////////////////////////////////////////////////////
//LOAD PACKAGE
//////////////////////////////////////////////////////////////////////////////////
$RELATIVE="../..";
include("$RELATIVE/lib/sci2web.php");

//////////////////////////////////////////////////////////////////////////////////
//VARIABLES
//////////////////////////////////////////////////////////////////////////////////
$content="";
$checkuser="";
$bugbut="";

//////////////////////////////////////////////////////////////////////////////////
//DYNAMIC CONTENT
//////////////////////////////////////////////////////////////////////////////////
$content.=<<<APPS
<form method="get" action="app.php" enctype="multipar/form-data" target="_blank">
<input type="hidden" name="SetApp" value="true">
<table class="apps">
<tr>
<td width="10%"></td><td width="40%"></td>
<td width="10%"></td><td width="40%"></td>
APPS;

$apptable=mysqlCmd("select * from apps");

$i=0;
$bugforms=array();
foreach($apptable as $approw){
  $appcode=$approw["app_code"];
  $appdate=$approw["creation_date"];
  $appauthor=$approw["users_emails_author"];

  $appdir="$PROJ[APPSDIR]/$appcode";
  $apppath="$PROJ[APPSPATH]/$appcode";
  
  if(($i%2)==0){
    $content.="</tr><tr>";
  }
  if(!file_exists("$apppath/app.conf")){
$content.=<<<CONTENT
<td colspan="2" style="font-size:18px;background-color:pink">
  <i>Application "$appcode" has not been properly installed: no application directory found.</i>
</td>
CONTENT;
 $i++;
 continue;
  }
  readConfig("$apppath/app.conf");
  $appname=$CONFIG["Application"];
  $appcomplete=$CONFIG["AppCompleteName"];
  $appdesc=$CONFIG["AppBrief"];

  //BUG BUTTON
  list($bugbut,$bugforms[])=genBugForm2("AppplicationAccess","Access to application $appname","$appauthor");
  $appvers=$approw["versions_codes"];
  if(!preg_match("/[\w\d]/",$appvers)) continue;
  $vers=preg_split("/;/",$appvers);
  $verstr="";
  foreach($vers as $ver){
    if(isBlank($ver)) continue;
    $version=getRow(mysqlCmd("select * from versions where version_code='$ver' and apps_code='$appcode' order by release_date"),0);
    $opsel="";
    if($version["version_code"]=="dev") $opsel="selected";
    $verstr.="<option $opsel value='$version[version_code]'>Version $version[version_code] ($version[release_date])";
  }
$content.=<<<APPS
<!-- ------------------------------------------------------------ -->
<!-- $appcode APP						  -->
<!-- ------------------------------------------------------------ -->
<td class="button" valign="top">
<button class="image" name="App" value="$appcode"
onmouseover="this.style.border='solid $COLORS[dark] 2px';explainThis(this)"
onmouseout="this.style.border='solid $COLORS[dark] 0px'"
explanation="Open application web page"
 $checkuser>
<img src="$appdir/$appcode-icon.jpg" height="100px">
</button>
</td>
<td class="description">
<a href="JavaScript:Open('$appdir/$appcode-desc.html','Application description','$PROJ[SECWIN]')">
<b onmouseover="explainThis(this)"
   explanation="See a brief of the application">$appname</b>
</a><br/>
$appcomplete
<br/>
<b>Author</b>: $appauthor<br/>
<b>Date</b>: $appdate<br/>
<b>Brief description</b>:<br/>
<div class="quote">
$appdesc
</div>
<b>Version</b>:
<select name="VersionId">
$verstr
</select>
$bugbut
</td>
APPS;
 $i++;
}

if($i==0){
$content.=<<<CONTENT
</tr>
<tr><td colspan="4" style="font-size:18px;background-color:$COLORS[clear]">
<i>No apps served yet.</i>
</td>
CONTENT;
}
$content.=<<<APPS
</tr>
</table>
</form>
APPS;
foreach($bugforms as $bugform){
  echo $bugform;
}
//////////////////////////////////////////////////////////////////////////////////
//CONTENT
//////////////////////////////////////////////////////////////////////////////////
$content.=<<<APPS
APPS;

echo<<<APPS
<h1>Served Applications</h1>

<p>Below are listed the applications served at
the <b>$PROJ[SCI2WEBSITE]</b>.  You can browse the web page or search
into the results database of any application served here but in order
to submit a run you will need an active user account
(<a href="JavaScript:void(null)"
onclick="toggleElement('signup')">sign up</a> for an account
or <a href="JavaScript:void(null)"
onclick="toggleElement('login')">login</a> if you have already one).</p>

$content
APPS;
?>
