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
# LIBRARY
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
*/
//////////////////////////////////////////////////////////////////////////////////
//EXTERNAL LIBRARIES 
//////////////////////////////////////////////////////////////////////////////////
$PHP["PROJPATH"]=rtrim(shell_exec("cd $RELATIVE;pwd"));
$PHP["TMPPATH"]="$PHP[PROJPATH]/tmp";
include_once("phplib.php");

//////////////////////////////////////////////////////////////////////////////////
//CONFIGURATION
//////////////////////////////////////////////////////////////////////////////////
include_once("sci2web.conf");

//////////////////////////////////////////////////////////////////////////////////
//CONNECT TO DATABASE
//////////////////////////////////////////////////////////////////////////////////
dbConnect($PROJ["DBSERVER"],$PROJ["DBUSER"],$PROJ["DBPASS"],$PROJ["DBNAME"]);

//////////////////////////////////////////////////////////////////////////////////
//VARIABLES
//////////////////////////////////////////////////////////////////////////////////
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//PAGE INFORMATION
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
if(!isset($PAGETITLE)){$PROJ["PAGETITLE"]="Page";}
else{$PROJ["PAGETITLE"]=$PAGETITLE;}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//DIRECTORIES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
if(!isset($RELATIVE)){$RELATIVE=".";}
$PROJ["PROJDIR"]="/$PROJ[PROJBASE]/$PROJ[PROJNAME]";
if(!isset($_SESSION["PROJDIR"]))
   $_SESSION["PROJDIR"]=$PROJ["PROJDIR"];
$PROJ["PROJPATH"]=rtrim(shell_exec("cd $RELATIVE;pwd"));
$PROJ["IMGDIR"]="$PROJ[PROJDIR]/images";
$PROJ["IMGPATH"]="$PROJ[PROJPATH]/images";
$PROJ["PAGESDIR"]="$PROJ[PROJDIR]/pages";
$PROJ["PAGESPATH"]="$PROJ[PROJPATH]/pages";
$PROJ["APPSDIR"]="$PROJ[PROJDIR]/apps";
$PROJ["APPSPATH"]="$PROJ[PROJPATH]/apps";
$PROJ["TMPDIR"]=$PHP["TMPDIR"]="$PROJ[PROJDIR]/tmp";
$PROJ["TMPPATH"]=$PHP["TMPPATH"]="$PROJ[PROJPATH]/tmp";
$PROJ["PAGEDIR"]="$PROJ[PROJDIR]/pages/$PHP[PAGEBASENAME]";
$PROJ["RUNSDIR"]="$PROJ[PROJDIR]/runs";
$PROJ["RUNSPATH"]="$PROJ[PROJPATH]/runs";
$PROJ["BINDIR"]="$PROJ[PROJDIR]/bin";
$PROJ["BINPATH"]="$PROJ[PROJPATH]/bin";
$PROJ["LIBDIR"]="$PROJ[PROJDIR]/lib";
$PROJ["LIBPATH"]="$PROJ[PROJPATH]/lib";
$PROJ["JSDIR"]="$PROJ[PROJDIR]/js";
$PROJ["JSPATH"]="$PROJ[PROJPATH]/js";

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//AUTHENTICATION
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$PROJ["LOGIN"]=true;
$PROJ["ROOTEMAIL"]="sci2web@gmail.com";

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//COMMON ELEMENTS
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$PROJ["ELBLANKET"]="<div id='ELBLANKET' style='display:none'></div>";
$PROJ["ELOVER"]="<img id='ELOVER' class='ELOVER' src='$PROJ[IMGDIR]/animated/loader-circle.gif'/ style='display:none'/>";

$PROJ["DIVBLANKET"]="<div id='DIVBLANKET' class='DIVBLANKET' style='display:none'></div>";
$PROJ["DIVOVER"]="<img id='DIVOVER' class='DIVOVER' src='$PROJ[IMGDIR]/animated/loader-circle.gif'/ style='display:none'/>";

$PROJ["CANCELBUTTON"]="<a href='#'><img class='cancel' src='$PROJ[IMGDIR]/icons/actions/Cancel.gif' height='30px' onclick='window.close()'></a>";

//SCI2WEB MACRO
$SCI2WEB="<a class='sci2web' href='http://google.com'>Sci2Web</a>";

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//DATABASE
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$DATABASE["Runs"]=
  array(
	"run_name"=>"Run Name",
	"run_code"=>"Run Code",
	"run_hash"=>"Run Hash",
	"configuration_date"=>"Configuration Date",
	"run_status"=>"Run Status",
	"run_pinfo"=>"Run process information",
	"permissions"=>"Permissions",
	"versions_id"=>"Version ID",
	"users_email"=>"User e-mail",
	"run_extra1"=>"Extra field 1",
	"run_extra2"=>"Extra field 2",
	"run_extra3"=>"Extra field 3"
	);

$PS2W=array("ImageFile","DataFiles","XCols","YCols",
	    "LinesInformation","XRange","YRange",
	    "XScale","YScale","Title","XLabel","YLabel",
	    "LegendLocation","SetGrid","GridStyle","ExtraCode",
	    "ExtraDecoration");

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//STATUS DICTIONARY
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$C2S=array("error","configured","clean","compiled","ready","submit",
	   "run","pause","resume","stop","fail","end","finish");
$S2C=invertHash($C2S);

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//BUTTONS
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$BUTTONS=array();
function listButtons(){
  global $PHP,$PROJ,$BUTTONS;
  exec("ls $PROJ[IMGPATH]/icons/actions/*.gif",$filesButtons,$status);
  foreach($filesButtons as $file){
    $fbase=shell_exec("basename $file");
    list($bname,$ext)=preg_split("/\./",$fbase);
$BUTTONS["$bname"]=<<<BUTT
<img src=$PROJ[IMGDIR]/icons/actions/$fbase 
  height="20px" alt="$bname" align="top">
BUTT;
  }
}
listButtons();

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//ACTIONS
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$PROJ["Actions"]=array("Clean","Compile","Run","Pause","Stop","Resume");

//////////////////////////////////////////////////////////////////////////////////
//AUTHENTICATION
//////////////////////////////////////////////////////////////////////////////////
function checkAuthentication()
{
  global $PROJ,$PHP;

  if(isset($PHP["UserOperation"])){
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //CHANGE PASSWORD
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    if($PHP["UserOperation"]=="ChangePass"){
      $pass=mysqlGetField("select * from users where email='$_SESSION[User]'",0,"password");
      $codepass=md5($PHP["OldPassword"]);
      $newpass=md5($PHP["NewPassword"]);
      if($pass==$codepass){
	if($PHP["NewPassword"]==$PHP["ConfirmPassword"]){
	  mysqlCmd("update users set password='$newpass',activate='0' where email='$_SESSION[User]'");
	  systemCmd("rm -rf $PROJ[TMPPATH]/*$PHP[SESSID]*");
	  session_unset();
	  $onload=genOnLoad("notDiv('notlogin','Password changed<br/>You need to activate your account again')");
	  echo "$onload";
	}else{
	  $onload=genOnLoad("notDiv('notlogin','Passwords does not match')");
	  echo "$onload";
	}
      }else{
	$onload=genOnLoad("notDiv('notlogin','Old password invalid')");
	echo "$onload";
      }
    }
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //LOGOUT
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    if($PHP["UserOperation"]=="Logout"){
      //REMOVE TEMPORAL FILES
      systemCmd("rm -rf $PROJ[TMPPATH]/*$PHP[SESSID]*");
      session_unset();
      $onload=genOnLoad("notDiv('notlogin','¡Hasta la vista!')");
      echo "$onload";
    }
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //ACTIVATE ACCOUNT
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    if($PHP["UserOperation"]=="Activate"){
      //CHECK ACTIVATION CODE
      $actcode=mysqlGetField("select * from users where email='$PHP[SignupEmail]'",0,"actcode");
      if($actcode==$PHP["ActivationCode"]){
	mysqlCmd("update users set activate='1' where email='$PHP[SignupEmail]'");
	$_SESSION["User"]=$PHP["SignupEmail"];
	$onload=genOnLoad("notDiv('notlogin','Your account has been activated')");
	echo "$onload";
      }else{
	$onload=genOnLoad("notDiv('notlogin','Invalid activation code')");
	echo "$onload";
      }
      //$_SESSION["User"]=$PHP["SignupEmail"];
    }
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //RECOVER PASS
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    if($PHP["UserOperation"]=="RecoverPass"){
      $resmat=mysqlCmd("select * from users where email='$PHP[LoginEmail]'");
      if($PHP["?"]){
	$onload=
	  genOnLoad("notDiv('notlogin','Your e-mail is not recognized')");
      }else{
	$onload=
	  genOnLoad("notDiv('notlogin','Check your e-mail for a new password')");
      }
      echo "$onload";
    }
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //LOGIN
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    if($PHP["UserOperation"]=="Login"){
      $activate=mysqlGetField("select * from users where email='$PHP[LoginEmail]'",0,"activate");
      if($activate){
	$pass=mysqlGetField("select * from users where email='$PHP[LoginEmail]'",0,"password");
	$codepass=md5($PHP["LoginPassword"]);
	if($codepass==$pass){
	  $_SESSION["User"]=$PHP["LoginEmail"];
	  $onload=genOnLoad("notDiv('notlogin','Welcome')");
	  echo "$onload";
	}else{
	  $onload=genOnLoad("notDiv('notlogin','Password invalid')");
	  echo "$onload";
	}
      }else{
	$onload=genOnLoad("notDiv('notlogin','Your account has not been activated.<br/>Check your e-mail')");
	echo "$onload";
      }	
    }
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //SIGNUP
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    if($PHP["UserOperation"]=="Signup"){
      //==================================================
      //CHECK FOR E-MAIL ALREADY USED
      //==================================================
      $resmat=mysqlCmd("select * from users where email='$PHP[SignupEmail]'");
      if($PHP["?"]){
	//==================================================
	//CHECK FOR UNMATCHED PASSWORD CONFIRMATION
	//==================================================
	if($PHP["SignupPassword"]==$PHP["SignupConfirm"]){
	  $codepass=md5($PHP["SignupPassword"]);
	  mysqlCmd("replace into users set email='$PHP[SignupEmail]',username='$PHP[SignupName]',password='$codepass',activate='0',actcode='$PHP[RANDID]'");
	  //EMAIL
	  $acturl="$PHP[SERVER]/$PROJ[PROJDIR]/main.php?UserOperation=Activate&SignupEmail=$PHP[SignupEmail]&ActivationCode=$PHP[RANDID]";
	  if($PROJ["ENABLEMAIL"]){
$text=<<<TEXT
<p>Welcome to Sci2Web,</p>
<p>
Somebody has tried to sign-up using your
e-mail <i>$PHP[SignupEmail]</i> in the <b>Sci2Web</b> platform at
the <b>$PROJ[SCI2WEBSITE]</b>.  If you are who is trying to get an
account use the following link to activate your account:
</p>
<a href="$acturl">Activation link</a>
TEXT;
	    blankFunc();
	    $email=$PHP["SignupEmail"];
	    $subject="[Sci2Web] Activate your account";
	    $from=$replyto=$PROJ["ROOTEMAIL"];
	    sendMail($email,$subject,$text,$from,$replyto);
	  }else{
	    $fl=fopen("$PHP[TMPPATH]/$PHP[SignupEmail]-activationurl","w");
	    fwrite($fl,"$acturl\n");
	    fclose($fl);
	  }
	  //NOTIFICATION
	  $onload=genOnLoad("notDiv('notlogin','Your account has been created.<br/>Check your e-mail.')");
	  echo "$onload";
	}else{
	  $onload=genOnLoad("notDiv('notlogin','Passwords does not match')");
	  echo "$onload";
	}
      }else{
	$onload=genOnLoad("notDiv('notlogin','User already exists')");
	echo "$onload";
      }
    }//END SIGNUP
  }//END IF USER OPERATION
}
//////////////////////////////////////////////////////////////////////////////////
//ROUTINES
//////////////////////////////////////////////////////////////////////////////////

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GENERATE HEAD OF HTML FILES
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function genHead($refresh,$time)
{
  global $PHP,$PROJ,$RELATIVE,$COLORS;

  $head="";
  //========================================
  //REFRESH PAGE
  //========================================
  if(!isBlank($refresh) and !isBlank($time)){
    $refline="<meta http-equiv=refresh content='$time;URL=$refresh'>";
  }else{
    $refline="<!--NO REFRESH-->";
  }

  //========================================
  //HEAD
  //========================================
$head.=<<<HEAD
<head>
  <span id="RULER" style="display:none"></span>
  <meta http-equiv="content-type" content="text/html" charset="UTF-8">
  $refline
  <link rel="icon" href="$PROJ[IMGDIR]/favicon.ico" type="image/x-icon">
  <link rel="shortcut icon" href="$PROJ[IMGDIR]/favicon.ico" type="image/x-icon"> 
  <title>$PROJ[TITLE] - $PROJ[PAGETITLE]</title>

HEAD;
  blankFunc();

  //========================================
  //LOAD JS
  //========================================
  //JQUERY
$head.=<<<HEAD
  <script type='text/javascript' src='$PROJ[PROJDIR]/js/jquery/jquery-1.7.js'></script>
  <script type='text/javascript' src='$PROJ[PROJDIR]/js/domwindow//jquery.DOMWindow.js'></script>
  <script type='text/javascript' src='$PROJ[PROJDIR]/js/tabber/tabber.js'></script>
  <script type='text/javascript' src='$PROJ[PROJDIR]/js/sci2web.js'></script>
  <script type='text/javascript' src='$PROJ[PROJDIR]/js/jshash/md5.js'></script>
  <script type='text/javascript' src='$PROJ[PROJDIR]/js/ckeditor/ckeditor.js'></script>
  <script type='text/javascript'>
  CKEDITOR.editorConfig=function(config)
  {
  config.language='en';
  config.uiColor='$COLORS[text]';
  }
  </script>
HEAD;
  blankFunc();
  //========================================
  //LOAD CSS
  //========================================
  require_once("$PROJ[PROJPATH]/lib/sci2web.css");
  $head.=$PROJ["STYLES"];
  return $head;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GENERATE HEADER OF HTML FILES
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function genHeader($logo,$style="")
{
  global $PROJ,$PHP,$COLORS,$BUTTONS;
  $header="";
  
  //==================================================
  //HEADER
  //==================================================
$header.=<<<HEADER
<div class="header_container">
  <div class="header_content" style="height:2em;text-align:right;">
  <div id="notlogin" class="notification" style="display:none;$style"></div>
HEADER;

  //==================================================
  //IF USER HAS BEEN AUTHENTICATED
  //==================================================
  $header.="<form method='get' action='?' enctype='multipart/form-data'>";

  if(!isset($_SESSION["User"])){
$header.=<<<HEADER
  <!-- BASIC LINKS -->
  <a href="$PROJ[PROJDIR]">Home</a> 
  | 
  <div style="display:inline">
  <a href="#" onclick="toggleElement('signup')">
  Sign up 
  </a>
  </div>
  <div id="signup" class="userbox">
  <table>
  <tr><td>Name:</td><td><input type="text" name="SignupName"></td></tr>
  <tr><td>e-mail:</td><td><input type="text" name="SignupEmail"></td></tr>
  <tr><td>Password:</td><td><input type="password" name="SignupPassword"></td></tr>
  <tr><td>Confirm:</td><td><input type="password" name="SignupConfirm"></td></tr>
  <tr>
  <td colspan=10><button name="UserOperation" value="Signup">Sign Up</button></td>
  </tr>
  </table>
  </div>
  | 
  <div style="display:inline">
  <a href="#" onclick="toggleElement('login')">
  Login 
  </a>
  </div>
  <div id="login" class="userbox">
  <form>
  <table>
  <tr><td>e-mail:</td><td><input type="text" name="LoginEmail"></td></tr>
  <tr><td>password:</td><td><input type="password" name="LoginPassword"></td></tr>
  <tr>
  <td colspan=10>
  <button name="UserOperation" value="Login">Login</button>
  <button name="UserOperation" value="RecoverPass">Recover password</button>
  </td>
  </tr>
  </table>
  </div>
HEADER;

  }else{
    $name=mysqlGetField("select username from users where email='$_SESSION[User]'",0,"username");

$header.=<<<HEADER
User $name | 
  <div style="display:inline">
  <a href="#" onclick="toggleElement('changepass')">
  Your account
  </a>
  </div>
  <div id="changepass" class="userbox">
  <form>
  User e-mail: $_SESSION[User]
  <table>
  <tr><td>Old password:</td><td><input type="password" name="OldPassword"></td></tr>
  <tr><td>New password:</td><td><input type="password" name="NewPassword"></td></tr>
  <tr><td>Confirm password:</td><td><input type="password" name="ConfirmPassword"></td></tr>
  <tr>
  <td colspan=10>
  <button name="UserOperation" value="ChangePass">Change password</button>
  </td>
  </tr>
  </table>
  </div>
| 
<a href="$PROJ[PROJDIR]">Home</a> 
| 
<a href="$PROJ[PROJDIR]/main.php?UserOperation=Logout">Logout</a>
HEADER;
  }    

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //BUG REPORT
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  $bugbut=genBugForm("UserOperation","Logout");

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //CLOSE HEADER
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$header.=<<<HEADER
  </form>
  $bugbut
  </div>
  <div class="header_content logo" >
  <!-- LOGO -->
  <img src="$logo" class="mainlogo"/>
  </div>
  <div class="header_content" style="height:50px">
  <!-- MENUS -->
  </div>
</div>
HEADER;

 return $header;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GENERATE FOOTER
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function genFooter()
{
  global $PHP,$PROJ,$COLORS;

$footer=<<<FOOTER
  <div class="footer_container">
  <div class="footer_contain">
  Version <b>$PROJ[VERSION]</b><br/>
  Developed by Jorge Zuluaga, 
  <i style="color:$COLORS[dark];font-decoration:underline">
  zuluagajorge at gmail dot com
  </i><br/>
  Powered by 
  <a href="http://php3.de"><img src="$PROJ[IMGDIR]/php.gif" height="30px" align="center"></a><br/>
  </div>
  </div>

FOOTER;
 return $footer;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GENERATE DEBUGGING INFORMATION
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function genDebug($position="top")
{
  global $PHP,$PROJ;

  if(!isset($_SESSION["User"])) return "";
  if(!strstr($PROJ["ROOTEMAIL"],$_SESSION["User"]) or !$PROJ["DEBUG"]) 
    return "";
  if(!$PHP["DEBUG"]) return "";

  $debug="";
  
  $file_opts="$PROJ[BINDIR]/file.php?Action=Get&Dir=$PROJ[TMPDIR]&Mode=View";
  
  //==================================================
  //GET LIST OF DEBUG FILES 
  //==================================================
  $files=listFiles("$PROJ[TMPPATH]","php*-*-$PHP[SESSID]");
  $debug.="<div class='debug_$position'>\n";
$debug.=<<<DEBUG
<select name='dbfile' onchange="fileDebug(this,'$file_opts','$PROJ[SECWIN]')">
DEBUG;
  $lfiles=":";

  //==================================================
  //GENERATE OPTIONS 
  //==================================================
  foreach($files as $file){
    preg_match("/php\w+-(.+)-$PHP[SESSID]/",$file,$matches);
    $page=$matches[1];
    if(!preg_match("/:$page:/",$lfiles)){
      $lfiles.="$page:";
      $optsel="";
      if($page==$PHP["PAGEBASENAME"]) $optsel="selected='true'";
      $debug.="<option value='$page-$PHP[SESSID]' $optsel>$page";
    }
  }
  $debug.="</select name='dbfile'>";

  //==================================================
  //GENERATE LINKS
  //==================================================
$debug.=<<<DEBUG
  <a id="server" href="JavaScript:Open('$file_opts&File=$PHP[DBFILE]','File','$PROJ[SECWIN]')">
  server
</a> |
  <a id="stdout" href="JavaScript:Open('$file_opts&File=$PHP[CMDOUTFILE]','File','$PROJ[SECWIN]')">
  stdout
</a> |
  <a id="stderr"  href="JavaScript:Open('$file_opts&File=$PHP[CMDERRFILE]','File','$PROJ[SECWIN]')">
  stderr
</a> |
  <a id="mysql"  href="JavaScript:Open('$file_opts&File=$PHP[SQLFILE]','File','$PROJ[SECWIN]')">
  mysql
</a> 
DEBUG;

  $debug.="</div>";
  return $debug;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//ON LOAD DUMMY IMAGE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function genOnLoad($javas,$id='onload')
{
  global $PHP,$PROJ;
  $onload="";
$onload=<<<ONLOAD
<img id="${id}_$PHP[RANDID]" 
src="$PROJ[IMGDIR]/php.gif" style="display:none" 
onload="$javas">
ONLOAD;
 return $onload;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GENERATE NORMAL IMAGE TAG
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function genLoadImg($image,$class="img",$id="img",$style="")
{
  global $PHP,$PROJ,$COLORS;

  $img="";
$img=<<<IMG
<img id=$id class=$class src=$PROJ[IMGDIR]/$image style=$style/>
IMG;
  return $img;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//READ PARAMETRIZATION MODEL
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function readParamModel($varsconf)
{
  global $PHP;

  $lines=loadFile($varsconf);
  $it=0;
  foreach($lines as $line){
    if(isBlank($line)){
      continue;
    }else if(preg_match("/^#TAB:/",$line)){
      $ig=0;
      list($id,$tab)=split(":",$line);
      $tabs[]=$tab;
      $it++;
    }else if(preg_match("/^#GROUP:/",$line)){
      list($id,$group)=split(":",$line);
      $groups["$tab"][]=$group;
      $ig++;
    }else{
      $vars[$tab][$group][]=$line;
    }
  }
  return array($tabs,$groups,$vars);
  
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//TABLE OF FILES
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function filesTable($dir,$options=""){
  global $PHP,$PROJ,$BUTTONS;
  $id="file";

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //TABLE HEADER
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

$ajax_filelist=<<<AJAX
loadContent
  (
   '$PROJ[BINDIR]/ajax-trans-file.php?Action=GetList&Dir=$dir&$options&Start=0',
   'listfiles',
   function(element,rtext){
     element.innerHTML=rtext;
     $('#DIVBLANKET$id').css('display','none');
     $('#DIVOVER$id').css('display','none');
   },
   function(element,rtext){
     $('#DIVBLANKET$id').css('display','block');
     $('#DIVOVER$id').css('display','block');
   },
   function(element,rtext){
     element.innerHTML='Error';
   },
   -1,
   true
   )
AJAX;
   blankFunc();
   $onload=genOnLoad($ajax_filelist,'load');

$ajax_down=<<<AJAX
submitForm
  ('formfiles',
   '$PROJ[BINDIR]/ajax-trans-file.php?Dir=$dir',
   'down_divlink',
   function(element,rtext){
     element.innerHTML=rtext;
     $('#down_wait').css('display','none');
   },
   function(element,rtext){
     element.innerHTML='Packing...';
     $('#down_wait').css('display','block');
   },
   function(element,rtext){
     element.innerHTML='Error';
   }
   )
AJAX;

$table=<<<TABLE
$onload
<form id="formfiles" action="JavaScript:void(null)" method="get"
      enctype="multipart/form-data"
      onsubmit="$ajax_down">
<table class="files">
<!-- ---------------------------------------------------------------------- -->
<!-- BUTTON HEADER							    -->
<!-- ---------------------------------------------------------------------- -->
<thead>
  <tr class="buttons">
    <td colspan=4>
      <div style="position:relative">
	<div style="position:absolute;float:right;top:0px;right:0px">
	  <a href="JavaScript:$ajax_filelist">$BUTTONS[Update]</a>
	</div>
	<div style="position:relative;float:left;top:0px;left:0px;">
	  <button id="downbut" class="image" name="Action" value="Download"
		  onmouseover="explainThis(this)"
		  explanation="Download">
	    $BUTTONS[Down]
	  </button>
	  <input type="hidden" name="Action_Submit" value="Download">
	</div>
	<div id="down_wait" 
	     style="prosition:absolute;float:left;bottom:0px;display:none;">
	  $BUTTONS[Wait]
	</div>
	<div id="down_divlink"
	     style="prosition:absolute;float:left;bottom:0px;">
	</div>
      </div>
    </td>
</tr>
<!-- ---------------------------------------------------------------------- -->
<!-- COMMON HEADER							    -->
<!-- ---------------------------------------------------------------------- -->
<tr class="head">
<td class="check">
<input type="checkbox" 
       name="objall" 
       value="all"
       onchange="popOutHidden(this)" 
       onclick="selectAll('formfiles',this)">
<!--<input type="hidden" name="objall_Submit" value="off">-->
</td>
<td>
Filename
</td>
<td>
Properties
</td>
<td>
Action
</td>
</tr>
</thead>
TABLE;

  blankFunc();
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //LIST FILES
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$table.=<<<TABLE
<tbody id="listfiles">
<tr><td colspan=10></td></tr>
</tbody>
TABLE;

  //TABLE FOOTER
$table.=<<<TABLE
</table>
</form>
TABLE;

  //WRAP
  divBlanketOver("$id");

$table=<<<TABLE
<div style="position:relative">
$PROJ[DIVBLANKET]
$PROJ[DIVOVER]
$table
</div>
TABLE;

  return $table;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//CHOOSE FILE ICON
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function  chooseFileIcon($file)
{
  global $PHP,$PROJ;

  $filename=shell_exec("basename $file");

  preg_match("/\.(\w+)$/",$filename,$regs);
  
  if(isset($regs[1])) $ext=$regs[1];
  else $ext="txt";

  if(is_dir($file)){
    $ext="dir";
  }
  $image="$ext.gif";
  if(!file_exists("$PROJ[PROJPATH]/images/icons/mimetypes/$image")){
    $image="gen.gif";
  }
  
  return "$image";
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//CHOOSE FILE ICON
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function filedType($file)
{
  global $PHP,$PROJ;
  $type=systemCmd("file $file");
  $ftype="UNRECOGNIZED";
  if(preg_match("/executable/",$type)){
    $ftype="EXECUTABLE";
  }
  if(preg_match("/image/",$type)){
    $ftype="IMAGE";
  }
  if(preg_match("/directory/",$type)){
    $ftype="DIRECTORY";
  }
  if(preg_match("/text/",$type) or
     preg_match("/empty/",$type)){
    $ftype="TEXT";
  }
  if(preg_match("/script/",$type)){
    $ftype="SCRIPT";
  }
  if(preg_match("/archive/",$type)){
    $ftype="ARCHIVE";
  }
  if(preg_match("/compressed/",$type)){
    if(preg_match("/\.tar\.gz/",$file)){
      $ftype="TGZ";
    }else{
      $ftype="ZIP";
    }
  }
  if(preg_match("/symbolic link/",$type)){
    $ftype="LINK";
  }
  //echo "FTYPE:$ftype";br();
  return $ftype;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//OPEN FILE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function fileWebOpen($dir,$file,$mode)
{
  global $PHP,$PROJ;
  
$link=<<<LINK
Open('$PROJ[BINDIR]/file.php?Dir=$dir&File=$file&Mode=$mode','File $file','$PROJ[SECWIN]')
LINK;
  
  return $link;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//OPEN FILE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function genRunLink($runcode,$action,$id="")
{
  global $PHP,$PROJ;
  blankFunc();
$ajaxcmd=<<<AJAX
loadContent
  (
   '$PROJ[BINDIR]/ajax-trans-run.php?RunCode=$runcode&Action=$action',
   'result',
   function(element,rtext){
     element.innerHTML=rtext;
     $('#DIVBLANKET$id').css('display','none');
     $('#DIVOVER$id').css('display','none');
   },
   function(element,rtext){
     $('#DIVBLANKET$id').css('display','block');
     $('#DIVOVER$id').css('display','block');
   },
   function(element,rtext){
   },
   -1,
   true
   )
AJAX;
  blankFunc();
  return $ajaxcmd;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GENERATE TEMPLATE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function genConfig($parmodel,$fileconf,$header)
{
  global $PHP;

  list($tabs,$groups,$vars)=readParamModel($parmodel);
  $fl=fileOpen($fileconf,"w");
  fwrite($fl,"$header\n");
  foreach($tabs as $tab){
    foreach($groups[$tab] as $group){
      foreach($vars[$tab][$group] as $var){
	list($var,$defval,$datatype,$varname,$vardesc)=
	  split("::",$var);
	$val=$PHP["$var"];
	fwrite($fl,"$var = $val\n");
      }
    }
  }
  fclose($fl);
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GENERATE TEMPLATE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function toggleButtons($status)
{
  $none="none";$disp="inline";
  $display=array("Clean"=>$none,
		 "Compile"=>$none,
		 "Run"=>$none,
		 "Pause"=>$none,
		 "Stop"=>$none,
		 "Resume"=>$none,
		 "Remove"=>$none,
		 "Configure"=>$none
		 );
  if($status=="clean"){
    $display["Compile"]=$disp;
    $display["Remove"]=$disp;
    $display["Configure"]=$disp;
  }
  if($status=="configured"){
    $display["Compile"]=$disp;
    $display["Remove"]=$disp;
    $display["Configure"]=$disp;
  }
  if($status=="ready"){
    $display["Clean"]=$disp;
    $display["Run"]=$disp;
    $display["Remove"]=$disp;
    $display["Configure"]=$disp;
  }
  if($status=="pause"){
    $display["Stop"]=$disp;
    $display["Resume"]=$disp;
    $display["Remove"]=$disp;
  }
  if($status=="submit" or
     $status=="run"){
    $display["Pause"]=$disp;
    $display["Stop"]=$disp;
    $display["Stop"]=$disp;
  }
  if($status=="end" or
     $status=="fail" or
     $status=="stop"){
    $display["Clean"]=$disp;
    $display["Run"]=$disp;
    $display["Remove"]=$disp;
    $display["Configure"]=$disp;
  }
  return $display;
}

function toggleButtons2($status)
{
  $none="style='opacity:0.5' disabled";
  $disp="";
  $display=array("Clean"=>$none,
		 "Compile"=>$none,
		 "Run"=>$none,
		 "Pause"=>$none,
		 "Stop"=>$none,
		 "Resume"=>$none,
		 "Remove"=>$none,
		 "Configure"=>$none
		 );
  if($status=="clean"){
    $display["Compile"]=$disp;
    $display["Remove"]=$disp;
    $display["Configure"]=$disp;
  }
  if($status=="configured"){
    $display["Compile"]=$disp;
    $display["Remove"]=$disp;
    $display["Configure"]=$disp;
  }
  if($status=="ready"){
    $display["Clean"]=$disp;
    $display["Run"]=$disp;
    $display["Remove"]=$disp;
    $display["Configure"]=$disp;
  }
  if($status=="pause"){
    $display["Stop"]=$disp;
    $display["Resume"]=$disp;
    $display["Remove"]=$disp;
  }
  if($status=="submit" or
     $status=="run"){
    $display["Pause"]=$disp;
    $display["Stop"]=$disp;
    $display["Stop"]=$disp;
  }
  if($status=="end" or
     $status=="fail" or
     $status=="stop"){
    $display["Clean"]=$disp;
    $display["Run"]=$disp;
    $display["Remove"]=$disp;
    $display["Configure"]=$disp;
  }
  return $display;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//REPLACE A GIVEN ELEMENT BY AN EDITOR
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function replaceByEditor($element)
{
  global $PHP,$PROJ,$COLORS;
  $ckfinder="$PROJ[PROJDIR]/js/ckfinder";
$replconf=<<<REPLACE
<script type="text/javascript">
  CKEDITOR.replace("$element",
  {
  /*toolbar:'Basic',*/
  uiColor:'$COLORS[text]',
 filebrowserBrowseUrl : '$ckfinder/ckfinder.html',
  filebrowserImageBrowseUrl:'$ckfinder/ckfinder.html?Type=Images',
  filebrowserFlashBrowseUrl:'$ckfinder/ckfinder.html?Type=Flash',
  filebrowserUploadUrl:'$ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
  filebrowserImageUploadUrl:'$ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
  filebrowserFlashUploadUrl:'$ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash'
  });
  </script>
REPLACE;
 return $replconf;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GENERATE BLANK PLOT CONFIGURATION
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function genPlotConf($datafile,$imgfile)
{
  global $PHP,$PROJ;
  blankFunc();

$conf=<<<CONF
ImageFile='$imgfile'
DataFiles=['$datafile']

XCols=[1]
YCols=[[2]]
LinesInformation=[[('Label','black','-',1,'+',2)]]

XRange='Auto'
YRange='Auto'

XScale='linear'
YScale='linear'

Title='Data file'
XLabel='X'
YLabel='Y'

LegendLocation='best'

SetGrid='No'
GridStyle=(1,':','black')

ExtraCode=''
ExtraDecoration=''
CONF;

  blankFunc();
  return $conf;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GENERATE BLANK PLOT CONFIGURATION
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function savePlotConf($confpath)
{
  global $PHP,$PROJ,$PS2W;
  blankFunc();
  
  //==================================================
  //CONVERT TO THE PS2W FORMAT
  //==================================================
  plainTops2w($PHP);
  $conf="";
  foreach($PS2W as $field){
    if(isBlank($PHP[$field])){
      $PHP[$field]="''";
    }
    $conf.="$field = $PHP[$field]\n";
  }  

  blankFunc();
  $fl=fileOpen($confpath,"w");
  fwrite($fl,$conf);
  fclose($fl);
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//CONVERT FROM THE PS2W FORMAT TO A PLAIN
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function ps2wToPlain(&$vector)
{
  global $PHP,$PROJ,$PS2W;

  foreach($PS2W as $field){
    $vector[$field]=preg_replace("/[\[\]]/","",$vector[$field]);
    /*
    $vector[$field]=preg_replace("/\s*r'/","",$vector[$field]);
    */
  }
  return 0;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//CONVERT FROM THE PLAIN FORMAT TO PS2W 
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function plainTops2w(&$vector)
{
  global $PHP,$PROJ,$PS2W;

  foreach($PS2W as $field){
    /*
    $vector[$field]=
      preg_replace("/([,\(\)\s]*)([^,\(\)]+)([,\(\)\s]*)/",
		   "$1r'$2'$3",$vector[$field]);
    */
  }
  $vector["ImageFile"]="'$vector[ImageFile]'";
  $vector["DataFiles"]="['$vector[DataFiles]']";
  $vector["XCols"]="[$vector[XCols]]";
  $vector["YCols"]="[[$vector[YCols]]]";
  $vector["LinesInformation"]="[[$vector[LinesInformation]]]";

  return 0;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//SCROLLABLE INPUT
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function scrollableInput($id,$extra,$inival,$min,$max,$delta,$num=100)
{
  global $PHP,$PROJ;
  $input="";
  blankFunc();

$input.=<<<INPUT
  <!-- ------------------------------------------------------------ -->
  <!-- BASIC INPUT						    -->
  <!-- ------------------------------------------------------------ -->
  
  <div class="inputscroll">
    <img src="$PROJ[IMGDIR]/php.gif" style="display:none"
	 onload="valueSlider('${id}',0,0,$inival)">
    <input type="text" name="$id" value="$min" id="$id"
	   max="$max" min="$min" delta="$delta" 
	   onchange="checkInputValue('$id','minmax',{min:$min,max:$max});
		     updateSlider('$id');"
	   $extra>
    <br/>
    <div id="report"></div>
  </div>

  <!-- ------------------------------------------------------------ -->
  <!-- SCROLL AREA						    -->
  <!-- ------------------------------------------------------------ -->
  <div class="extremescroll">$min</div>
  <div class="backscroll" id="${id}_container" onmousemove="moveSlider('$id')">
    <!-- ------------------------------------------------------------ -->
    <!-- ARROWS      						      -->
    <!-- ------------------------------------------------------------ -->
    <div class="arrowscroll" style="left:0px" id="${id}_button">
      <div class="buttonscroll" onclick="valueSlider('${id}',0,-1.0,0)">
	<img class="buttonscroll" src="$PROJ[IMGDIR]/icons/actions/Left.gif"/>
      </div>
    </div>

    <div class="arrowscroll" style="right:0px" id="${id}_button">
      <div class="buttonscroll" onclick="valueSlider('${id}',0,+1.0,0)">
	<img class="buttonscroll" src="$PROJ[IMGDIR]/icons/actions/Right.gif"/>
      </div>
    </div>
    <!-- ------------------------------------------------------------ -->
    <!-- SLIDER BAR  						      -->
    <!-- ------------------------------------------------------------ -->
    <div class="sliderscroll" id="${id}_bar">
      <div class="barsliderscroll"
	   dragged="false"
	   onmousedown="$(this).attr('dragged','true')"
	   onmouseup="$(this).attr('dragged','false')"
	   id="${id}_slider">
      </div>
    </div>
  </div>
  <div class="extremescroll">$max</div>
INPUT;
  blankFunc();
  return $input;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//ICON STATUS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function statusIcon($status)
{
  $icon="";
  //$status="end";
  switch($status){
  case "configured":
    $status_text="Configured";
    $status_color="black";
    $status_bg="lightgray";
    break;
  case "clean":
    $status_text="Cleaned";
    $status_color="black";
    $status_bg="orange";
    break;
  case "ready":
    $status_text="Ready";
    $status_color="white";
    $status_bg="green";
    break;
  case "run":
    $status_text="Running";
    $status_color="white";
    $status_bg="red";
    break;
  case "pause":
    $status_text="Paused";
    $status_color="red";
    $status_bg="yellow";
    break;
  case "stop":
    $status_text="Stopped";
    $status_color="black";
    $status_bg="lightgray";
    break;
  case "end":
    $status_text="Ended";
    $status_color="white";
    $status_bg="blue";
    break;
  }

$icon=<<<ICON
<div style="float:center;
	    color:$status_color;
	    border-radius:5px;
	    text-align:center;
	    background-color:$status_bg">
<b>$status_text</b>
</div>
ICON;

  return $icon;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GET CONTROL BUTTONS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function getControlButtons($run_code,$status,$id="")
{
  global $PHP,$PROJ,$BUTTONS;

  divBlanketOver($id);
  $display=toggleButtons2($status);
  $actions=$PROJ["Actions"];
  $links="";
  foreach($actions as $action){
    $actionlink=genRunLink($run_code,$action,$id);
    $actionextra="";
$links.=<<<LINKS
<button href="JavaScript:void(null)" 
	class="image" id="Bt_$action" 
	onclick="$actionlink" 
	$display[$action]>
$BUTTONS[$action]
</button> 
LINKS;
  }
$controls=<<<CONTROLS
  $PROJ[DIVBLANKET]
  $PROJ[DIVOVER]
  $links
CONTROLS;
  return $controls;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//DIVBLANKET & DIVOVER
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function divBlanketOver($id)
{
  global $PROJ;

  $PROJ["DIVBLANKET"]="<div id='DIVBLANKET$id' class='DIVBLANKET' style='display:none'></div>";
  $PROJ["DIVOVER"]="<img id='DIVOVER$id' class='DIVOVER' src='$PROJ[IMGDIR]/animated/loader-circle.gif'/ style='display:none'/>";
}

function getStatusBar($status)
{
  global $PHP,$PROJ,$COLORS;
$bstatus=<<<STATUS
<div id="status_text" 
     style="width:200px;
	    text-align:center;
	    border:solid $COLORS[dark] 1px">
  <div id="status_bar" 
       style="width:$status%;
	      text-align:right;
	      background-color:$COLORS[text];
	      padding:0px;color:$COLORS[dark];">
    $status%
  </div>
</div>
STATUS;
   return $bstatus;
}

function genBugForm($module,$subject)
{
  global $PHP,$PROJ,$BUTTONS;
  $id=md5("$user$page$module$subject");
  $bugform="";
  $user="anonymous";
  $page=$PHP["PAGENAME"];
  if(isset($_SESSION["User"])){
    $user=$_SESSION["User"];
$ajax_bug=<<<AJAX
submitForm
  ('bugreport',
   '$PROJ[BINDIR]/ajax-bug-report.php?',
   'casa',
   function(element,rtext){
     element.innerHTML='';
   },
   function(element,rtext){
     element.innerHTML='Reporting bug...';
   },
   function(element,rtext){
     element.innerHTML='Error';
   }
   )
AJAX;

$bugform=<<<BUG
  <a href="JavaScript:void(null)" onclick="toggleElement('$id')">$BUTTONS[Bug]</a>
  <div id="casa" style="position:fixed;
			bottom:10px;
			right:10px;
			z-index:10000">
  </div>
  <div style="position:relative;">
    <div id="$id" class="bugbox">
      <form id="bugreport" action="JavaScript:void(null)" method="get" 
	    enctype="multipart/form-data">
      <table>
	<tr>
	</tr><tr>
	  <td colspan=2><b>Bug Report</b></td>
	</tr><tr>
	  <td>From:</td>
	  <td>
	    <input type="text" name="BugUser" value="$user" onchange="popOutHidden(this)">
	    <input type="hidden" name="BugUser_Submit" value="$user">
	  </td>
	</tr><tr>
	  <td>Page:</td>
	  <td>
	    <input type="text" name="BugPage" value="$page" disabled onchange="popOutHidden(this)">
	    <input type="hidden" name="BugPage_Submit" value="$page">
	  </td>
	</tr><tr>
	  <td>Module:</td>
	  <td>
	    <input type="text" name="BugModule" value="$module" disabled onchange="popOutHidden(this)">
	    <input type="hidden" name="BugModule_Submit" value="$module">
	  </td>
	</tr><tr>
	  <td>Subject:</td>
	  <td>
	    <input type="text" name="BugSubject" value="$subject" onchange="popOutHidden(this)">
	    <input type="hidden" name="BugSubject_Submit" value="$subject">
	  </td>
	</tr><tr>
	  <td valign="top">Bug report:</td>
	  <td>
	    <textarea cols="30" rows="10" name="BugReport" onchange="popOutHidden(this)"></textarea>
	    <input type="hidden" name="BugReport_Submit" value="">
	  </td>
	</tr><tr>
	  <td colspan="2" style="text-align:right">
	    <button name="UserOperation" value="BugReport"
		    onclick="$ajax_bug">
	      Report
	    </button>
	    <br/><i>Use the same bug link to close this window</i><br/>
	  </td>
	</tr><tr>
	</tr>
      </table>
      </form>
    </div>
  </div>
BUG;
   }

  return $bugform;
}

function checkSuperUser()
{
  global $PROJ;
  $condition=(isset($_SESSION["User"]) and 
	      strstr("$PROJ[ROOTEMAIL]","$_SESSION[User]"));
  return $condition;
	      
}
?>
