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
# INTERACTIVE SCRIPT
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
*/
//////////////////////////////////////////////////////////////////////////////////
//LIBRARY
//////////////////////////////////////////////////////////////////////////////////
$RELATIVE="..";
$PAGETITLE="Configure & Results Window";
include_once("$RELATIVE/lib/sci2web.php");

//////////////////////////////////////////////////////////////////////////////////
//GLOBAL VARIABLES
//////////////////////////////////////////////////////////////////////////////////
$head="";

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//DIRECTORIES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$appname="$_SESSION[AppVersion]";
$appdir="$PROJ[APPSDIR]/$appname";
$apppath="$PROJ[APPSPATH]/$appname";
$contabs=array();
$runsdir="$PROJ[RUNSDIR]/$_SESSION[User]/$appname";
$runspath="$PROJ[RUNSPATH]/$_SESSION[User]/$appname";
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GET RUN INFORMATION
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$runcode=$PHP["RunCode"];
$resmat=mysqlCmd("select * from runs where run_code='$PHP[RunCode]'");
$row=getRow($resmat,0);
foreach(array_keys($DATABASE["Runs"]) as $runfield){
  $$runfield=$row["$runfield"];
}
$rundir="$runsdir/$run_hash";
$runpath="$runspath/$run_hash";

//////////////////////////////////////////////////////////////////////////////////
//READ PARAMETERS
//////////////////////////////////////////////////////////////////////////////////
$resfile="$runpath/sci2web/resultswindow.conf";
readConfig2("$resfile");

//////////////////////////////////////////////////////////////////////////////////
//CONTENT
//////////////////////////////////////////////////////////////////////////////////
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//HEAD
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$head.=genHead("","");

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//CONTENT
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
if(isset($CONFIG["Stacking"])){
  $Stacking=$CONFIG["Stacking"][0];
}else $Stacking="cols";
if(isset($CONFIG["Sizes"])){
  $Sizes=$CONFIG["Sizes"][0];
}else $Sizes="40%,60%";

echo<<<CONTENT
<html>
  <head>
    $head
  </head>
  
  <body>
    <frameset $Stacking="$Sizes">
      <frame src="$PROJ[PROJDIR]/bin/configure.php?RunCode=$PHP[RunCode]&HeightWindow=66%&Closable=false"/>
      <frame src="$PROJ[PROJDIR]/bin/results.php?RunCode=$PHP[RunCode]&HeightWindow=78%&Closable=false"/>
      <noframes>
	Your browser does not support frames
      </noframes>
      
    </frameset>
  </body>
</html>
CONTENT;
