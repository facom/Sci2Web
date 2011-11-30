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
# AJAX SCRIPT
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
*/
//////////////////////////////////////////////////////////////////////////////////
//LOAD PACKAGE
//////////////////////////////////////////////////////////////////////////////////
$RELATIVE="../";
include("$RELATIVE/lib/sci2web.php");

//////////////////////////////////////////////////////////////////////////////////
//VARIABLES
//////////////////////////////////////////////////////////////////////////////////
$result="";

$appname="$_SESSION[AppVersion]";
$appdir="$PROJ[APPSDIR]/$appname";
$apppath="$PROJ[APPSPATH]/$appname";
$savedbdir="$PROJ[RUNSDIR]/db/$appname";
$savedbpath="$PROJ[RUNSPATH]/db/$appname";
list($tabs,$groups,$vars)=readParamModel("$apppath/sci2web/parametrization.info");

//////////////////////////////////////////////////////////////////////////////////
//BUILD QUERY
//////////////////////////////////////////////////////////////////////////////////
if(isBlank($PHP["Query"])){
  $PHP["Query"]="dbdate";
}
$dbname="$_SESSION[App]_$_SESSION[Version]";
$query="select * from $dbname where $PHP[Query]";

//////////////////////////////////////////////////////////////////////////////////
//QUERY DATABASE
//////////////////////////////////////////////////////////////////////////////////
$qresult=mysqlCmd($query);

//////////////////////////////////////////////////////////////////////////////////
//GENERATE RESULT
//////////////////////////////////////////////////////////////////////////////////

if(!is_array($qresult)){
  $result="<p style='font-style:italic'>No results found.</p>";
}else{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //KEYS OF DATABASE
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  $allkeys=array();
  $keys=array();
  $reskeys=array();
  foreach(array_keys($qresult[0]) as $key){
    if(preg_match("/^\d+$/",$key)) continue;
    $allkeys[]=$key;
    if(preg_match("/$key/",join("++",$vars["Results"]["General"]))){
      $reskeys[]=$key;
    }else{
      $keys[]=$key;
    }
  }

  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //COMMA SEPARATED VALUES FILE
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  $filecsv="dbres-$PHP[RANDID].csv";
  $filecsv_dir="$PROJ[TMPDIR]/$filecsv";
  $filecsv_path="$PROJ[TMPPATH]/$filecsv";
  $fl=fopen($filecsv_path,"w");
  foreach($allkeys as $key){
    fwrite($fl,"\"$key\",");
  }
  fwrite($fl,"\n");
  foreach($qresult as $row){
    foreach($allkeys as $key){
      fwrite($fl,"\"$row[$key]\",");
    }
    fwrite($fl,"\n");
  }  
  fwrite($fl,"\n");
  fclose($fl);
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //GRAPHICAL TABLE
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //TABLE INITIALIZATION
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  $dir="$PROJ[PROJDIR]/runs/db/$appname";

$ajax_down=<<<AJAX
submitForm
  ('formdbres',
   '$PROJ[BINDIR]/ajax-trans-file.php?Dir=$dir',
   'down_divlink',
   function(element,rtext){
     element.innerHTML=rtext;
     $('#down_wait').css('display','none');
   },
   function(element,rtext){
     element.innerHTML='Packing results...';
     $('#down_wait').css('display','block');
   },
   function(element,rtext){
     element.innerHTML='Error';
   }
   )
AJAX;

$result.=<<<RESULT
CSV result file: 
<a href="$filecsv_dir">download</a>,
<a href="JavaScript:Open('$PROJ[BINDIR]/file.php?Dir=$PROJ[TMPDIR]&File=$filecsv&Mode=View','CSV File','$PROJ[SECWIN]')">view</a>
<form id="formdbres" action="JavaScript:void(null)"
      onsubmit="$ajax_down">
<table class="sqlresults">
<thead>
<tr class="sqlhead">
RESULT;

  blankFunc();
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //GENERATE HEADER
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$result.=<<<RESULT
<td>Run hash</td>
<td>Run code</td>
<td>Author</td>
<td>Date</td>
<td>Input parameters</td>
RESULT;

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //RESULT VARIABLES HEADER
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  blankFunc();
  foreach($reskeys as $key){
    $result.="<td>$key</td>";
  }

$result.=<<<RESULT
<td>
  <input type="checkbox" 
	 name="objall" 
	 value="all"
	 onchange="popOutHidden(this)" 
	 onclick="selectAll('formdbres',this)">
</td>
<td>Run Files</td>
RESULT;

$result.=<<<RESULT
</tr>
</thead>
<tbody>
RESULT;

  $i=0;
  foreach($qresult as $row){
    $result.="<tr>";

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //NORMAL VARIABLES
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    $runhash=$row['dbrunhash'];
$result.=<<<RESULT
<td>$row[dbrunhash]</td>
<td>$row[runs_runcode]</td>
<td>$row[dbauthor]</td>
<td>$row[dbdate]</td>
RESULT;
    blankFunc();

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //INPUT VARIABLES
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    $result.="<td><div style='position:relative'>";
$result.=<<<RESULT
  <a href="JavaScript:void(null)" 
     onclick="toggleElement('params$i')">
    Click to see
  </a>
  <div id="params$i" class="displayable" style="display:none">
RESULT;
    blankFunc();
    foreach($keys as $key){
$result.=<<<RESULT
$key=$row[$key]<br/>
RESULT;
    }
    $result.="</div></div></td>";

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //RESULT VARIABLES
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    foreach($reskeys as $key){
      $result.="<td>$row[$key]</td>";
    }
    $file="$row[dbrunhash].tar.gz";
$result.=<<<RESULT
<td>
  <input type="checkbox" name="obj$i" value="ObjDown:$file"
	 onchange="popOutHidden(this)">
  <input type="hidden" name="obj${i}_Submit" value="off">
</td>
<td>
  <a href="JavaScript:Open('$PROJ[BINDIR]/file.php?Mode=View&Dir=$savedbdir&File=$row[dbrunhash].tar.gz','Results for $runhash','$PROJ[SECWIN]')">Browse</a>
</td>
RESULT;
    $result.="</tr>";
    $i++;
  }

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //FINALIZE TABLE
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  $removeopt="";
  if(checkSuperUser()){
$removeopt=<<<REMOVE
<button id="removebut" class="image" name="Button" value="RemoveResults"
	onmouseover="setsValue('action_remove','RemoveResults');explainThis(this)"
	explanation="Remove results" $removeopt>
  $BUTTONS[Remove]
</button>
<input id="action_remove" type="hidden" name="Action_Submit" value="">
REMOVE;
}

$result.=<<<RESULT
</tbody>
<tfooter>
<tr>
<td colspan="8" style="text-align:right">
  <div style="position:relative">
    <div style="float:right">
      $removeopt
      <button id="downbut" class="image" name="Button" value="Download"
	      onmouseover="setsValue('action_down','Download');explainThis(this)"
	      explanation="Download results">
	$BUTTONS[Down]
      </button>
      <input id="action_down" type="hidden" name="Action_Submit" value="">
    </div>
    <div id="down_wait" 
	 style="border:solid black 0px;float:right;position:relative;top:15px;
		margin-right:20px;display:none">
      $BUTTONS[Wait]
    </div>
    <div id="down_divlink"
	 style="border:solid black 0px;float:right;position:relative;top:15px;
		margin-right:20px;display:block">
    </div>
  </div>
</td>
</tr>
</tfooter>
</table>
</form>
RESULT;
}

end:
echo $result;
?>
