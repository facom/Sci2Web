<?
////////////////////////////////////////////////////////////////////////
// .______    __    __  .______    __       __  .______    	      //
// |   _  \  |  |  |  | |   _  \  |  |     |  | |   _  \              //
// |  |_)  | |  |__|  | |  |_)  | |  |     |  | |  |_)  |  	      //
// |   ___/  |   __   | |   ___/  |  |     |  | |   _  <   	      //
// |  |      |  |  |  | |  |      |  `----.|  | |  |_)  |  	      //
// | _|      |__|  |__| | _|      |_______||__| |______/   	      //
//   	     	   	  	  				      //
// Jorge Zuluaga (2011)						      //
////////////////////////////////////////////////////////////////////////
/*
  PHPlib is a set of general purpose routines in php and javascript
  intended to create dynamic web sites.
*/
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//CONFIGURATION
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
include_once("phplib.conf");

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//SESSION
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
if(!isset($_SESSION)) session_start();
srand ((float)microtime()*10000000);
if(isset($_COOKIE["PHPSESSID"]))
  $PHP["SESSID"]=$_COOKIE["PHPSESSID"];
else
  $PHP["SESSID"]=genRandom(5);

$PHP["RANDID"]=genRandom(5);
?>

<?
//////////////////////////////////////////////////////////////////////////////////
//GLOBAL PHP VARIABLES
//////////////////////////////////////////////////////////////////////////////////
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//DIRECTORIES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$PHP["ROOTPATH"]=$_SERVER["DOCUMENT_ROOT"];
if(!isset($PHP["TMPPATH"]))
  $PHP["TMPPATH"]=sys_get_temp_dir();

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//PAGE INFORMATION
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$PHP["SERVER"]="http://$_SERVER[HTTP_HOST]";
$PHP["PAGENAME"]=basename($_SERVER["SCRIPT_NAME"]);
$PHP["PAGEDIR"]=dirname($_SERVER["SCRIPT_NAME"]);
list($PHP["PAGEBASENAME"],$ext)=split("\.",$PHP["PAGENAME"]);
if(isset($_SERVER["HTTP_REFERER"])){
    $PHP["REFERER"]=$_SERVER["HTTP_REFERER"];
    $PHP["REFERERNAME"]=preg_split("/\?/",$_SERVER["HTTP_REFERER"]);
}else{
    $PHP["REFERER"]="index.php";
    $PHP["REFERERNAME"]="index.php";
}
    
$PHP["REFERERNAME"]=$PHP["REFERERNAME"][0];

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//FILES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$PHP["SESPAGEPREFIX"]="$PHP[PAGEBASENAME]-$PHP[SESSID]";
$PHP["DBFILE"]="phpserver-$PHP[SESPAGEPREFIX]";
$PHP["CMDOUTFILE"]="phpout-$PHP[SESPAGEPREFIX]";
$PHP["SQLFILE"]="phpsql-$PHP[SESPAGEPREFIX]";

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//DEBUGGING MODE
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
shell_exec("(date;echo -e 'Output File:\n') > $PHP[TMPPATH]/$PHP[CMDOUTFILE]");
$PHP["SYSCOUNTER"]=1;
if($PHP["DEBUG"]){
  $PHP["DBCOUNTER"]=$PHP["SQLCOUNTER"]=1;
  $PHP["FL"]=fopen("$PHP[TMPPATH]/$PHP[DBFILE]","w");
  shell_exec("(date;echo -e 'SQL File:\n') > $PHP[TMPPATH]/$PHP[SQLFILE]");
}


//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//INPUT VARIABLES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
dPrint("Input vars:\n\n");
dPrint("POST:\n");
$PHP["QSTRING"]=$_SERVER["QUERY_STRING"];
$PHP["QSTRALL"]=$PHP["QSTRING"];
foreach(array_keys($_POST) as $key){
  $PHP["$key"]=$_POST["$key"];
  dPrint("$key = $PHP[$key]\n");
  $PHP["QSTRALL"].="&".$key."=".preg_replace("/[\s\n]/","+",$PHP["$key"]);
}
foreach(array_keys($_REQUEST) as $key){
  $PHP["$key"]=$_REQUEST["$key"];
  dPrint("$key = $PHP[$key]\n");
  $PHP["QSTRALL"].="&".$key."=".preg_replace("/[\s\n]/","+",$PHP["$key"]);
}
dPrint("GET:\n");
foreach(array_keys($_GET) as $key){
  $PHP["$key"]=$_GET["$key"];
  dPrint("$key = $PHP[$key]\n");
  $PHP["QSTRALL"].="&".$key."=".preg_replace("/[\s\n]/","+",$PHP["$key"]);
}
dPrint("SESSION:\n");
foreach(array_keys($_SESSION) as $key){
  //$PHP["$key"]=$_SESSION["$key"];
  dPrint("$key = $_SESSION[$key]\n");
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//CONSTANTS
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$PHP["DATE"]=getdate();
$PHP["DOCTYPE"]=<<<DOCTYPE
<!doctype html public "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
DOCTYPE;
$PHP["TMPPREF"]="phptmp";

$PHP["SUSFILES"]=array("shell","php","exe","elf");

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//BEHAVIOR
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//AUXILIAR GLOBAL VARIABLES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$CONFIG=array();

//////////////////////////////////////////////////////////////////////////////////
//MAIN PHP LIBRARY
//////////////////////////////////////////////////////////////////////////////////

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//SYSTEM
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//COMPUTE THE HASH OF A FILE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function hashFile($file)
{
  if(file_exists($file)){
    $hash=systemCmd("md5sum $file | awk '{print \$1}'");
  }else{
    $hash="00000000000000000000000000000000";
  }
  return $hash;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//EXCUTE AN EXTERNAL COMMAND
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function systemCmd($cmd,$preserve=false)
{
  global $PHP;

  $ocmd=$cmd;
  $PHP["?"]=0;

  if($PHP["DEBUG"] or true){
$dbout=<<<DBOUT
================================================================================
Command $PHP[SYSCOUNTER]:
    $ocmd
DBOUT;
    blankFunc();
    shell_exec("echo \"$dbout\" >> $PHP[TMPPATH]/$PHP[CMDOUTFILE]");
    $cmd="($cmd) 2>> $PHP[TMPPATH]/$PHP[CMDOUTFILE]";
  }

  //========================================
  //EXECUTE COMMAND
  //========================================
  exec("$cmd",$outs,$status);

  //========================================
  //GET OUTPUT
  //========================================
  $PHP["?"]=$status;
  if($preserve) $sep="\n";
  else $sep="";
  $out=implode($sep,$outs);

  //========================================
  //SAVE COMMAND INFORMATION
  //========================================
  if($PHP["DEBUG"] or true){
    $eout=implode("\n",$outs);
$dbout=<<<DBOUT
Output:
$eout
DBOUT;
    blankFunc();
    shell_exec("echo \"$dbout\" >> $PHP[TMPPATH]/$PHP[CMDOUTFILE]");
    $PHP["SYSCOUNTER"]++;
  }
  
  return $out;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GET LIST OF FILES
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function listFiles($path,$criterium="*",$opts="",$exclude="",$search="*")
{
  global $PHP;

  //FILTER NAME
  $scol="awk '{print \$NF}'";
  
  //BASIC COMMAND
  $lcmd="ls -ld $opts $criterium";
  $excl="";
  foreach(preg_split("/;/",$exclude) as $excludecond){
    if(isBlank($excludecond)) continue;
    $excl.="|egrep -v '$excludecond\$'";
  }
  $srch="";
  foreach(preg_split("/\s+/",$search) as $searchcond){
    if(isBlank($searchcond)) continue;
    $srch.="|egrep '$searchcond\$'";
  }
  /*
  echo "LIST: $lcmd";br();
  echo "EXCLUDE: $excl";br();
  echo "SEARCH: $srch";br();
  //*/
  //COMMAND TO SORT DIRS, FILES, LINKS
  $cmd="($lcmd | grep '^d' $excl;$lcmd | grep '^-' $excl $srch;$lcmd | grep '^l' $excl $srch) | $scol";
  //echo "COMMAND: $cmd";br();
  //EXECUTE COMMAND
  $out=systemCmd("cd $path;$cmd",true);
  
  //TRIM RESULT
  $out=trim($out);

  $files=array();
  if(!isBlank($out)){
    //GENERATE LIST OF FILES
    $files=preg_split("/\s+/",$out);
  }else $files=array();

  return $files;
}


//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//FORMS
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GENERATE SELECION LIST
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

function genSelect($array,$name,$selected,$actions="",$style="")
{
  global $PhpGlobal;

  if(array_count_values($array)){
    $opts=array_keys($array);
    $vals=array_values($array);
  }else{
    $opts=array();
    $vals=array();
  }
  
  $code="";
  $code.="<select name=\"$name\" style='$style' $actions>";
  foreach($opts as $opt){
    if(isBlank($selected)){
      $code.=sprintf("<option value=\"%s\">%s\n",$array[$opt],$array[$opt]);
    }else if($array[$opt]==$selected){
      $code.=sprintf("<option selected value=\"%s\">%s\n",$array[$opt],$array[$opt]);
    }else{
      $code.=sprintf("<option value=\"%s\">%s\n",$array[$opt],$array[$opt]);
    }
  }
  $code.="</select>";

  return $code;
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//INPUT/OUTPUT
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//UPLOAD FILE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function uploadFile($tgtpath)
{
  global $PHP;
  if(count($_FILES)<1){
    return true;
  }else{
    foreach(array_keys($_FILES) as $filename){
      $file=$_FILES[$filename];

      if(isBlank($file["name"])) break;
      $PHP[$filename]=$file["name"];
      //==================================================
      //DO NOT ALLOW THE UPLOADING OF SUSPICIOUS FILES
      //==================================================
      foreach($PHP["SUSFILES"] as $susfile){
	if(preg_match("/$susfile/",$file["type"])){
	  echo "<p>You are trying to upload suspicious files.</p>";
	  exit(1);
	}
      }
      //==================================================
      //CHECK FILE
      //==================================================
      systemCmd("mv $file[tmp_name] $tgtpath/$file[name]");
    }
    return $file["error"];
  }
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//CHECK OWNER
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function checkPermissions($file)
{
  global $PHP;
  $user=shell_exec("stat -c %U $file");
  $user=rtrim($user);
  if($PHP["WEBUSER"]==$user)
    return true;
  return false;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//CREATE TEMPORAL FILE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function tempFile($tmpdir)
{
  global $PHP;
  if(!is_dir($tmpdir)){
    $tmpdir=$PHP["TMPPATH"];
  }
  $ftname=tempnam($tmpdir,"$PHP[TMPPREF]-");
  $ft=fopen($ftname,"w");

  return array($ft,$ftname);
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//CLOSE TEMPORAL FILE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function closeTemp($ft,$ftname)
{
  global $PHP;
  fclose($ft);
  unlink($ftname);
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//READ CONFIGURATION
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function readConfig($config)
{
  global $PHP,$CONFIG;
  
  $lines=loadFile("$config");
  $index=array();
  $i=0;
  foreach($lines as $line){
    if(preg_match("/=/",$line) and
       !preg_match("/^\/\//",$line) and
       !preg_match("/^#/",$line)){
      $fields=preg_split("/\s*=\s*/",$line);
      $CONFIG[$fields[0]]=$fields[1];
      //echo "Reading $fields[0] = ".$CONFIG[$fields[0]];br();
      $i++;
    }else if(preg_match("/\w+/",$line) and
	     !preg_match("/^\/\//",$line) and
	     !preg_match("/^#/",$line)){
      //echo "Orphan line for $fields[0] = ".$line;br();
      $CONFIG[$fields[0]].="\n$line";
    }
  }

  return $i;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//READ CONFIGURATION
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function readConfig2($config)
{
  global $PHP,$CONFIG;
  
  $lines=loadFile("$config");
  $index=array();
  $i=0;
  $fieldstr=":";
  foreach($lines as $line){
    if(preg_match("/=/",$line) and
       !preg_match("/^\/\//",$line) and
       !preg_match("/^#/",$line)){
      $fields=preg_split("/\s*=\s*/",$line);
      $field=$fields[0];
      $value="";
      $i=1;
      while(isset($fields[$i])){
	$value.=$fields[$i]."=";
	$i++;
      }
      $value=rtrim($value,"=");
      if(preg_match("/:$field:/",$fieldstr)){
	$NUMREP[$field]++;
      }else{
	if(!isset($NUMREP[$field])) $NUMREP[$field]=0;
	$fieldstr.="$field:";
      }
      $CONFIG[$field][$NUMREP[$field]]=$value;
      //echo "Reading $fields[0] = ".$CONFIG[$fields[0]];br();
      $i++;
    }else if(preg_match("/\w+/",$line) and
	     !preg_match("/^\/\//",$line) and
	     !preg_match("/^#/",$line)){
      //echo "Orphan line for $field($NUMREP[$field]) = ".$line;br();
      $CONFIG[$field][$NUMREP[$field]].="\n$line";
    }
  }

  //CHECK FIELDS WITHOUT REPETITIONS
  foreach(array_keys($CONFIG) as $iconfig){
    $CONFIG[$iconfig]["Num"]=$NUMREP[$iconfig]+1;
  }

  return $i;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//LOAD FILE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function loadFile($file)
{
  global $PHP;

  $fl=fileOpen($file,"r");
  $info=array();
  $i=0;
  while(!feof($fl)){
    $f=fgets($fl);
    $f=rtrim($f);
    array_push($info,$f);
    $i++;
  }
  fclose($fl);
  
  return $info;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//FINAL ACTIONS WHEN PAGE HAS COMPLETELY LOAD
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function finalizePage()
{
  global $PHP;
  shell_exec("find $PHP[TMPPATH] -name '*$PHP[RANDID]*' -exec rm -rf {} \\;");
  return 0;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//VERBOSE PRINT
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function dPrint($string)
{
  global $PHP;  if($PHP["DEBUG"]){
    fwrite($PHP["FL"],$string);
  }
  return 0;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//FILE OPEN
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function fileOpen($file,$type)
{
  global $PHP;
  $fl=fopen($file,$type);
  
  if(!$fl){
    echo "<h2>FILE '$file' COULD NOT BE OPEN AS $type</h2>";
    exit(1);
  }
  
  return $fl;
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//UTILITARY
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GET TODAY
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function getToday($format)
{
  global $PHP;
  $fields=array("seconds","minutes","hours","mday","wday","mon","year","yday",
		"weekday","month","0");
 
  $date=$format;
  foreach($fields as $field){
    $date=str_replace("%$field",$PHP["DATE"]["$field"],$date);
  }
  return $date;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//BREAK LINE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function br($num=0){
  for($i=0;$i<=$num;$i++){
    echo "<br/>\n";
  }
  return 0;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GENERATE A RANDOM STRING
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function genRandom($num)
{
  global $PHP;
  $PHP["SYMBOLS"]=array("a","b","c","d","e","f","g","h","i","j",
			"k","l","m","n","o","p","q","r","s","t",
			"u","v","w","x","y","z",1,2,3,4,5,6,7,8,9,0);
  $pass="";
  $nsymbs=count($PHP["SYMBOLS"]);
  for($i=0;$i<$num;$i++){
    $ind=rand(0,$nsymbs-1);
    $char=$PHP["SYMBOLS"][$ind];
    $pass.="$char";
  }

  return $pass;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GENERATE A RANDOM STRING
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function isBlank($var)
{
  $noblank=(!preg_match("/[^\s]+/",$var));
  return $noblank;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//NULL FUNCTION
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function blankFunc()
{
  return 0;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GENERATE A LOT OF BR
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function genBr($n){
  $br="";
  for($i=0;$i<=$n;$i++){
    $br.="<br/>\n";
  }
  return $br;
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//HTML
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//ARRAYS SPECIAL
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//INVERT HASH
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function printArray($array,$arrayname="Array",$output=false)
{
  global $PHP;

  $print="Array $arrayname:<br/>";

  foreach(array_keys($array) as $var){
    if(is_array($array["$var"]))
      $value=print_r($array["$var"],true);
    else
      $value=$array["$var"];
    $print.="$arrayname [$var] = $value<br/>\n";
  }
  if(!$output) echo $print;
  else return $print;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//INVERT HASH
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function invertHash($hash)
{
  $ihash=array();
  foreach(array_keys($hash) as $key){
    $val=$hash["$key"];
    $ihash["$val"]=$key;
  }
  return $ihash;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//COMPUTE THE ROWS OF A MATRIX
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function numRows($mat){
  global $PHP;
  return count($mat);
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GET A ROW FROM A MATRIX
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function getRow($mat,$i)
{
  global $PHP;
  return $mat[$i];
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GET A COL FROM A MATRIX
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function getCol($mat,$i)
{
  global $PHP;
  foreach($mat as $row){
    $col[]=$row[$i];
  }
  return $col;
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//DATABASE ROUTINES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//CONNECT TO DATABASE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function dbConnect($server,$user,$pass,$db)
{
  global $PHP;
  $PHP["DB"]=
    mysql_connect($server,$user,$pass);
  mysql_select_db($db,$PHP["DB"]); 
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//EXECUTE MYSQL COMMAND
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function mysqlCmd($cmd)
{
  global $PHP;

  if($PHP["DEBUG"]){
$dbsql=<<<SQL
================================================================================
SQL query $PHP[SQLCOUNTER]:
    $cmd
Result size or errors:
SQL;
    blankFunc();
    shell_exec("echo \"$dbsql\" >> $PHP[TMPPATH]/$PHP[SQLFILE]");
  }
  $output=mysql_query($cmd,$PHP["DB"]);
  $PHP["?"]=0;

  if(!$output){
    $PHP["?"]=1;
    if($PHP["DEBUG"]){
      $dbsql=mysql_error();
      shell_exec("echo \"$dbsql\" >> $PHP[TMPPATH]/$PHP[SQLFILE]");      
    }
    $resmat=array();
  }else{
    if(is_resource($output)){
      while($info=mysql_fetch_array($output)){
	$resmat[]=$info;
      }
    }else $resmat[]="";
  }
  if(!isset($resmat)) $resmat="";

  if(!is_array($resmat)) $PHP["?"]=1;

  if($PHP["DEBUG"]){
    $dbsql=numRows($resmat);
    shell_exec("echo \"$dbsql\" >> $PHP[TMPPATH]/$PHP[SQLFILE]");      
    $PHP["SQLCOUNTER"]++;
  }

  return $resmat;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GET A FIELD FROM IROW STARTING WITH AN SQL COMMAND
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function mysqlGetField($sql,$irow,$field)
{
  $resmat=mysqlCmd($sql);
  $row=getRow($resmat,$irow);
  $val=$row["$field"];
  return $val;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GET THE LIST OF ATTRIBUTES FROM A RESULT MATRIX
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function getAttrs($resmat)
{
  global $PHP;

  foreach(array_keys($resmat[0]) as $key){
    if(!preg_match("/^\d$/",$key)){
      $attrs[]=$key;
    }
  }
  return $attrs;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//ARRAY INVERT
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function arrayInvert($oldarray)
{
  $newarray=array();
  $arraysize=count($oldarray);
  for($i=$arraysize-1;$i>=0;$i--){
    $newarray[]=$oldarray[$i];
  }
  return $newarray;
}

//////////////////////////////////////////////////////////////////////////////////
//FINALIZE
//////////////////////////////////////////////////////////////////////////////////
dPrint("\nAll variables:\n\n");
foreach(array_keys($PHP) as $VAR){
  if(is_array($PHP["$VAR"]))
    $VALUE=print_r($PHP["$VAR"],true);
  else
    $VALUE=$PHP["$VAR"];
  dPrint("$VAR = $VALUE\n");
}

if($PHP["DEBUG"])
  fclose($PHP["FL"]);

/*
  ********************************************************************************
  Copyright (C) 2006 Jorge I. Zuluaga, <zuluagajorge@gmail.com>

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or (at
  your option) any later version.

  This program is distributed in the hope that it will be useful, but
  WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
  General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
  02110-1301, USA.
  ********************************************************************************
*/
?>
