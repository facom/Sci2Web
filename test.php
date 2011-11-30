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
#TEST PAGE
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
*/
//////////////////////////////////////////////////////////////////////////////////
//LOAD PACKAGE
//////////////////////////////////////////////////////////////////////////////////
$nerror=0;
$report="";
$RELATIVE=".";
$PAGETITLE="Test Page";
$LIBFILE="$RELATIVE/lib/sci2web.php";
$report.="<p class='testsession'>Library</p>";
if(file_exists($LIBFILE)){
  $report.="<p class='testsuccess'>File '$LIBFILE' load.</p>";
  include($LIBFILE);
}else{
  echo "<p>Library file '$LIBFILE' is not accesible</p>";
  phpinfo();  
  return 1;
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//CHECK VARIABLES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$report.="<p class='testsession'>Variables test</p>";
if(!isset($PROJ) or !isset($PHP)){
$report.=<<<REPORT
<p class="testerror">Critical variables not load</p>
REPORT;
$error++;
goto report;
}else{
  $proj_critvars=array("PROJBASE","PROJNAME","ENTRYPAGE");
  $php_critvars=array("DEBUG","WEBUSER","WEBGROUP");
  $vars="";
  foreach($proj_critvars as $var){
    $vars.="<li>PROJECT VAR '$var' = $PROJ[$var]</li>";
  }
  foreach($php_critvars as $var){
    $vars.="<li>PHP VARS '$var' = $PHP[$var]</li>";
  }
$report.=<<<REPORT
<p class="testsuccess">
Critical variables:
<ul>
$vars
</ul>
</p>
REPORT;
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//CHECK STYLES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$report.="<p class='testsession'>Styles load</p>";
$cssfile="$PROJ[PROJPATH]/lib/sci2web.css";
if(file_exists($cssfile)){
  $report.="<p class='testsuccess'>File '$cssfile' load.</p>";
  require_once("$PROJ[PROJPATH]/lib/sci2web.css");
}else{
  echo "<p>Styles file '$cssfile' is not accesible</p>";
  phpinfo();  
  return 1;
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//CHECK DATABASE
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$resmat=mysqlCmd("show tables;");
$database=print_r($resmat,true);
$report.="<p class='testsession'>Database test</p>";
if(1 or !$PHP[DB]){
$report.=<<<REPORT
<p class="testsuccess">Database:$PHP[DB]</p>
<p class="testsuccess">List of tables:$database</p>
REPORT;
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//CHECK PERMISSIONS
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$report.="<p class='testsession'>Permissions test</p>";
$dirs=array("$PROJ[TMPPATH]/test",
	    "$PROJ[APPSPATH]/template/application-desc.html",
	    "$PROJ[JSPATH]/ckfinder/userfiles/test",
	    "$PROJ[RUNSPATH]/test");
	    
foreach($dirs as $dir){
  systemCmd("touch $dir");
  if($PHP["?"]){
    $report.="<p class='testerror'>Error accessing '$dir'</p>";
    $error++;
  }else{
    $report.="<p class='testsuccess'>Success accessing '$dir'</p>";
    systemCmd("rm $dir");
  }
}

report:
$classerr="testsuccess";
if($nerror) $classerr="testerror";

echo<<<CONTENT
<html>
  <head>
    $PROJ[STYLES]
    <style type="text/css">
      p.testsession{
      font-size:18px;
      font-weight:bold;
      color:blue;
      }
      p.testerror{
      color:red;
      }
      p.testsuccess{
      color:green;
      }
    </style>
    <script type="text/javascript" src="$PROJ[PROJDIR]/js/jquery/jquery-1.7.js"></script>
    <script type="text/javascript" src="$PROJ[PROJDIR]/js/domwindow/jquery.DOMWindow.js"></script>
    <script type="text/javascript" src="$PROJ[PROJDIR]/js/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="$PROJ[PROJDIR]/js/tabber/tabber.js"></script>
    <script type="text/javascript" src="$PROJ[PROJDIR]/js/sci2web.js"></script>
  </head>
  <body>
    <div class="tabber" id=0>
      <div class="tabbertab">
	<h2>Test $PROJ[PROJNAME]</h2>
	<div style="background-color:$COLORS[text];padding:20px">
	<p class="$classerr">Number of errors: $nerror<p>
	</div>
	$report
      </div>
    </div>
  </body>
</html>
CONTENT;
?>
