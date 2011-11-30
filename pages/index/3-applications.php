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

//////////////////////////////////////////////////////////////////////////////////
//DYNAMIC CONTENT
//////////////////////////////////////////////////////////////////////////////////
$content.=<<<APPS
<form method="get" action="app.php" enctype="multipar/form-data">
<input type="hidden" name="SetApp" value="true">
<table class="apps">
APPS;

$apptable=mysqlCmd("select * from apps");

$i=1;
foreach($apptable as $approw){
  $appcode=$approw["app_code_name"];
  $appname=$approw["app_complete_name"];
  $appdesc=$approw["brief_description"];
  $appdate=$approw["creation_date"];
  $appauthor=$approw["users_emails_author"];

  $appdir="$PROJ[APPSDIR]/$appcode";
  $apppath="$PROJ[APPSPATH]/$appcode";
  
  $appvers=$approw["versions_ids"];
  $vers=preg_split("/;/",$appvers);
  $verstr="";
  foreach($vers as $ver){
    if(isBlank($ver)) continue;
    $version=getRow(mysqlCmd("select * from versions where version_id='$ver' order by release_date"),0);
    $opsel="";
    if($version["version_code"]=="dev") $opsel="selected";
    $verstr.="<option $opsel value='$version[version_id]'>Version $version[version_code] ($version[release_date])";
  }
  $bugbut=genBugForm($_SESSION["User"],$PHP["PAGENAME"],
		     "ApplicationVersions_${appcode}_${ver}","No version display");
$content.=<<<APPS
<!-- ------------------------------------------------------------ -->
<!-- $appcode APP						  -->
<!-- ------------------------------------------------------------ -->
<tr>
<td class="button">
<button class="image" name="App" value="$appcode"
onmouseover="this.style.border='solid $COLORS[dark] 2px'"
onmouseout="this.style.border='solid $COLORS[dark] 0px'"
 $checkuser>
<img src="$appdir/$appcode-icon.jpg" height="100px">
</button>
</td>
<td class="description">
<a href="JavaScript:Open('$PROJ[BINDIR]/file.php?Dir=$appdir&File=$appcode-desc.html&Mode=Display&Title=$appname&Type=html','File','$PROJ[SECWIN]')">
<b>$appname</b></a><br/>
<b>Author</b>: $appauthor<br/>
<b>Date</b>: $appdate<br/>
<b>Brief description</b>:<br/>
<text class="quote">
$appdesc
</text>
<br/>
<b>Version</b>:
<select name="VersionId">
$verstr
</select>
$bugbut
</td>
APPS;

}

$content.=<<<APPS
</table>
</form>
APPS;
//////////////////////////////////////////////////////////////////////////////////
//CONTENT
//////////////////////////////////////////////////////////////////////////////////
$content.=<<<APPS
APPS;

echo<<<APPS
<h1>Applications</h1>

<p>Below are listed the applications installed at the <b>$PROJ[SCI2WEBSITE]</b>.  To use one of the application press the respective icon (only for registered users).</p>

$content
APPS;
?>
