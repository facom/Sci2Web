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
$RELATIVE=".";
$PAGETITLE="Test Page";
include("$RELATIVE/lib/sci2web.php");

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//CHECK STYLES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
require_once("$PROJ[PROJPATH]/css/sci2web.css");
$resmat=mysqlCmd("show tables;");
$database=print_r($resmat,true);

echo<<<CONTENT
<html>
<head>
$PROJ[STYLES]
<script type="text/javascript" src="$PROJ[PROJDIR]/lib/jquery-1.7.js"></script>
<script type="text/javascript" src="$PROJ[PROJDIR]/lib/jquery.DOMWindow.js"></script>
<script type="text/javascript" src="$PROJ[PROJDIR]/lib/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="$PROJ[PROJDIR]/lib/tabber.js"></script>
<script type="text/javascript" src="$PROJ[PROJDIR]/lib/sci2web.js"></script>
</head>
<body>
<div class="tabber sectabber" id=0>
<div class="tabbertab sectab">
<h2>Test $PROJ[PROJNAME]</h2>
<p>LOGO:<img src="$PROJ[IMGDIR]/sci2web-mainlogo.jpg" align="center"></p>
<hr/>
<p>DATABASE: $PHP[DB]</p>

<hr/>
<p>TABLES: $database</p>
<hr/>
CONTENT;

echo<<<CONTENT
<p>CHECK PERMISSIONS:</p><ul>
CONTENT;

$dir="$PROJ[TMPPATH]/test";
systemCmd("touch $dir");
if($PHP["?"]){
  echo "<li style='color:red'>Error accessing '$dir'</li>";
}else{
  echo "<li style='color:green'>Success accessing '$dir'</li>";
  systemCmd("rm $dir");
}

$dir="$PHP[TMPPATH]/test";
systemCmd("touch $dir");
if($PHP["?"]){
  echo "<li style='color:red'>Error accessing '$dir'</li>";
}else{
  echo "<li style='color:green'>Success accessing '$dir'</li>";
  systemCmd("rm $dir");
}

$dir="$PROJ[APPSPATH]/templates/template-desc.html";
systemCmd("touch $dir");
if($PHP["?"]){
  echo "<li style='color:red'>Error accessing '$dir'</li>";
}else{
  echo "<li style='color:green'>Success accessing '$dir'</li>";
}

$dir="$PROJ[RUNSPATH]/test";
systemCmd("touch $dir");
if($PHP["?"]){
  echo "<li style='color:red'>Error accessing '$dir'</li>";
}else{
  echo "<li style='color:green'>Success accessing '$dir'</li>";
  systemCmd("rm $dir");
}

echo<<<CONTENT
</ul>
</div>
</div>
</body>
</html>
CONTENT;
?>
