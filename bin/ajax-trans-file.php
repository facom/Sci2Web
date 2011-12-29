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
//LIBRARY
//////////////////////////////////////////////////////////////////////////////////
$RELATIVE="..";
include_once("$RELATIVE/lib/sci2web.php");
//echo "Start";
//////////////////////////////////////////////////////////////////////////////////
//GLOBAL VARIABLES
//////////////////////////////////////////////////////////////////////////////////
$explanation="";
$extraaction="";
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//INPUT VARIABLES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$Action=$PHP["Action"];
if(isset($PHP["Dir"])) $Dir=$PHP["Dir"];
if(isset($PHP["File"])) $File=$PHP["File"];
else $File="";
if(!isset($PHP["LinkTarget"])) $PHP["LinkTarget"]="Blank";
$Target=$PHP["LinkTarget"];
$result="";

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//ROUTINES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
function genListCmd($dir,$id,$start){
  global $PROJ;
  $imgload=genLoadImg("animated/loader-circle.gif");
$cmd=<<<AJAX
loadContent
  (
   '$PROJ[BINDIR]/ajax-trans-file.php?Action=GetList&Dir=$dir&Start=$start',
   'listfiles',
   function(element,rtext){
     element.innerHTML=rtext;
     $('#DIVBLANKET$id').css('display','none');
     $('#DIVOVER$id').css('display','none');
   },
   function(element,rtext){
     $(element).html('$imgload');
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
 return $cmd;
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//DIRECTORIES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
if(isset($Dir)){
  $dpath="$PHP[ROOTPATH]/$Dir";
  $flink="$Dir/$File";
  $fpath="$PHP[ROOTPATH]/$flink";
}

//////////////////////////////////////////////////////////////////////////////////
//ACTION
//////////////////////////////////////////////////////////////////////////////////
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//DOWNLOAD FILES
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
if($Action=="Check"){
  $tocheck=$PHP["ToCheck"];
  systemCmd("($tocheck) &> $PROJ[TMPPATH]/check.$PHP[RANDID]");
  $result=shell_exec("cat $PROJ[TMPPATH]/check.$PHP[RANDID]");
  goto end;
}
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//DOWNLOAD FILES
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
if($Action=="DownloadFiles"){
  $dirpath="$PHP[ROOTPATH]/$PHP[DownloadDir]";
  $objs=array();
  $nobjs=$PHP["NumFiles"];
  for($i=0;$i<$nobjs;$i++){
    if($PHP["obj$i"]=="on"){
      $objs[]=$PHP["objfile$i"];
    }
  }
  $nobjs=count($objs);
  /*
  printArray($objs,"OBJ");
  print "OBJS:$nobjs";br();
  print "DPATH:$dirpath";br();
  //goto end;
  //*/
  if($nobjs>0){
    $tarhash=md5(join(",",$objs));  
    $tarfile="tarball-$tarhash-$PHP[SESSID].tar.gz";
    $tardir="$PROJ[TMPDIR]/$tarfile";
    $tarpath="$PROJ[TMPPATH]/$tarfile";
    $objslist=join(" ",$objs);
    systemCmd("cd $dirpath;tar zcf $tarpath $objslist .s2wfiles");
    $tarlink=fileWebOpen($PROJ["TMPDIR"],$tarfile,'View');
$result=<<<RESULT
Packed $nobjs objects. 
<a id="down_link" href="JavaScript:$tarlink">Click to get tarball</a>
RESULT;
  }else{
    $result.="<i>No files selected</i>";
  }
  goto end;
}
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//DOWNLOAD RESULTS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
if($Action=="DownloadResults"){
  $results=array();
  $nresults=$PHP["NumResults"];
  for($i=0;$i<$nresults;$i++){
    if($PHP["result$i"]=="on"){
      $results[]=$PHP["reshash$i"].".tar.gz";
    }
  }
  $nresults=count($results);
  if($nresults>0){
    $tarhash=md5(join(",",$results));  
    $tarfile="tarball-$tarhash-$PHP[SESSID].tar.gz";
    $tardir="$PROJ[TMPDIR]/$tarfile";
    $tarpath="$PROJ[TMPPATH]/$tarfile";
    $resultslist=join(" ",$results);
    systemCmd("cd $dpath;echo '*.tar.gz' > .s2wfiles");
    systemCmd("cd $dpath;tar zcf $tarpath $resultslist .s2wfiles");
    $tarlink=fileWebOpen($PROJ["TMPDIR"],$tarfile,'View');
$result=<<<RESULT
<a id="down_link" href="JavaScript:$tarlink">Click to get tarball</a>
RESULT;
  }else{
    $result.="<i>No files selected</i>";
  }
  goto end;
}
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//REMOVE RESULTS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
if($Action=="RemoveResults"){
  $results=array();
  $nresults=$PHP["NumResults"];
  for($i=0;$i<$nresults;$i++){
    if($PHP["result$i"]=="on"){
      $results[]=$PHP["reshash$i"];
    }
  }
  $nresults=count($results);
  if($nresults>0){
    $dbname="$_SESSION[App]_$_SESSION[Version]";
    foreach($results as $runhash){
      //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
      //REMOVE DATABASE ENTRY
      //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
      mysqlCmd("delete from `$dbname` where dbrunhash='$runhash'");
      //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
      //REMOVE FILE
      //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
      systemCmd("rm -rf $dpath/$runhash.tar.gz");
    }
    $result.="<i>$nresults results removed, please resubmit your search.</i>";
  }else{
    $result.="<i>No results selected</i>";
  }
  goto end;
}
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GET FILE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$result="";
if($Action=="Get"){
  if(file_exists($fpath)){
    $ftype=filedType("$fpath");
    switch($ftype){
    case "TEXT":
      $filecontent=shell_exec("cat $fpath");
      if(isBlank($filecontent)){
	$result="<p><i>Request file '$File' is empty</p></i>";
      }else{
	$result.=trim($filecontent);
      }
      break;
    case "IMAGE":
      $result="<img src='$flink' height='95%'/>";
      break;
    case "TGZ":
      $result="";
      break;
    default:
      $result="Unable to display";
      break;
    }
  }else{
    $result="<p><i>Request file '$File' does not exist</p></i>";
  }
}
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//SAVE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
if($Action=="Save"){
  $file_content=rtrim($PHP["FileContent"]);
  //WRITE FILE
  $fl=fileOpen($fpath,"w");
  fwrite($fl,$file_content);
  fclose($fl);
}//END OF SAVE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//GET LIST OF FILE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
if($Action=="GetList"){
  $path="$PHP[ROOTPATH]/$Dir";
  if(isset($PHP["SubDir"])) $path.="/$PHP[SubDir]";
  if(!is_dir($path)){
    $result="<tr><td colspan=10>No directory '$Dir'</td></tr>";
    goto end;
  }
  $criterium="";
  if(isset($PHP["Criterium"])) $criterium="$PHP[Criterium]";

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //CHECK FOR PERMISSIONS 
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  $pfile=".s2wfiles";
  systemCmd("echo '(IFS=/;for dir in \$PWD;do if [ -e \"\$PWD/$pfile\" ];then echo \"\$PWD\"/$pfile;exit 0;fi;cd ..;done;exit 1)' &> $PROJ[TMPPATH]/cmd.$PHP[RANDID]");
  $pfile=systemCmd("cd $path;bash $PROJ[TMPPATH]/cmd.$PHP[RANDID];rm -rf $PROJ[TMPPATH]/cmd.$PHP[RANDID]");
  if(isBlank($pfile)){
    echo "<tr><td colspan=10>Directory has not reading permissions.</td></tr>";
    return;
  }

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //LIST OF FILES
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //GET FILES LISTED
  $critlist=shell_exec("cat $pfile");
  $exclude="";
  foreach(preg_split("/\n/",$critlist) as $crit){
    if(preg_match("/^\^/",$crit)){
      $crit=preg_replace("/\^/","",$crit);
      $exclude.="$crit;";
    }else{
      $criterium.=" $crit ";
    }
  }
  $search="*";
  if(isset($PHP["Search"])) $search="$PHP[Search]";
  if(isBlank($criterium)) $criterium="*";
  /*
  echo "CRITERIUM:$criterium";br();
  echo "SEARCH:$search";br();
  echo "EXCLUDE:$exclude";br();
  //*/
  $files=listFiles($path,$criterium,"",$exclude,$search);
  $result.="";
  $i=0;
  if(file_exists("$path/../.s2wfiles")){
    array_unshift($files,"..");
  }
  $nfiles=count($files);

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //SEPARATE BY WINDOWS
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  if($PHP["Start"]=="All"){
    $start=0;
    $end=count($files);
  }else{
    $start=$PHP["Start"];
    $end=min($start+$PROJ["MAXFILES"],$nfiles);
  }

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //PAGER LINKS
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  $maxfile=$PROJ["MAXFILES"];
  $nprev=max($start-$maxfile,0);
  $nmod=fmod($nfiles,$maxfile);
  if($nmod>0){
    $lend=$nmod;
  }else{
    $lend=$maxfile;
  }
  $nnext=min($end,$nfiles-$lend);

  $prev=genListCmd($PHP["Dir"],"file",$nprev);
  $all=genListCmd($PHP["Dir"],"file","All");
  $next=genListCmd($PHP["Dir"],"file",$nnext);
  
$result.=<<<CONTROLS
<tr style="background-color:$COLORS[text];text-align:center">
  <td colspan="4" style="font-size:12px">
  <a href="JavaScript:$prev">< Prev <!--($nprev)--></a> |
  <a href="JavaScript:$all">All</a> |
  <a href="JavaScript:$next">Next <!--($end)--> ></a><br/>
CONTROLS;

  $ini=0;
  $ng=floor($nfiles/$maxfile);
  for($ig=0;$ig<=$ng;$ig++){
    $igi=$ig*$maxfile;
    $ilink=genListCmd($PHP["Dir"],"file",$igi);
$result.=<<<CONTROLS
<a href="JavaScript:$ilink">$igi</a> 
CONTROLS;
  }

$result.=<<<CONTROLS
  / <i>Showing $start to $end from $nfiles</i>
</td>
</tr>
CONTROLS;
  $seli=0;
  foreach($files as $file){
    if(($i<$start or $i>=$end) and $file!=".."){
      $i++;
      continue;
    }
    $filepath="$path/$file";
    //==================================================
    //IMPLICIT EXCLUSION
    //==================================================
    if(isBlank($file)) continue;
    if(!checkPermissions($filepath)) continue;
    if(!file_exists($filepath)) continue;
    if(is_link($filepath)) continue;
    //==================================================
    //EXTERNAL EXCLUSION RULES
    //==================================================
    //**PENDING**
    //==================================================
    //FILE COUNTER
    //==================================================
    $i++;
    //==================================================
    //FILE TYPE AND ICON
    //==================================================
    $icon=chooseFileIcon($filepath);
    $iconimg="<img src='$PROJ[PROJDIR]/images/icons/mimetypes/$icon'/>";
    $ftype=filedType($filepath);
    $size=filesize($filepath)/1024.0;
    //==================================================
    //FILE PROPERTIES
    //==================================================
    $date=date("Y-m-d H:i",filemtime($filepath));
    $size=round($size,1);
    $flink_view=fileWebOpen($Dir,$file,"View",$Target);
    $flink_edit=fileWebOpen($Dir,$file,"Edit",$Target);
    $flink_plot="Open('$PROJ[BINDIR]/plot.php?Dir=$Dir&File=$file','Plot $file','$PROJ[PLOTWIN]')";
    //::::::::::::::::::::::::::::::::::::::::
    //CHECK
    //::::::::::::::::::::::::::::::::::::::::
$checkcol=<<<CHECK
<input type="checkbox" name="obj$seli"
       onchange="popOutHidden(this)" 
       onclick="deselectAll('objall')"
       color_unchecked="$COLORS[clear]"
       color_checked="$COLORS[text]">
<input type="hidden" name="obj${seli}_Submit" value="off">
<input type="hidden" name="objfile${seli}_Submit" value="$file">
CHECK;
    blankFunc();
    if($file=="..") $checkcol="";
    //::::::::::::::::::::::::::::::::::::::::
    //FILENAME
    //::::::::::::::::::::::::::::::::::::::::
    switch($ftype){
    case "DIRECTORY":
      //##############################
      //DIRECTORY
      //##############################
      $dirhash=md5($Dir);
      $id="file_$dirhash";
      $imgload=genLoadImg("animated/loader-circle.gif");
$ajax_subdir=<<<AJAX
loadContent
  (
   '$PROJ[BINDIR]/ajax-trans-file.php?Action=GetList&Dir=$Dir/$file&Start=0&Search=$search',
   'listfiles',
   function(element,rtext){
     element.innerHTML=rtext;
     $('#DIVBLANKET$id').css('display','none');
     $('#DIVOVER$id').css('display','none');
   },
   function(element,rtext){
     $(element).html('$imgload');
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
      $flink="JavaScript:$ajax_subdir";
      $extraaction="onclick=$('input[name=DownloadDir_Submit]').attr('value','$Dir/$file')";
      break;
    case "TEXT":case "SCRIPT":case "IMAGE":
      //##############################
      //TEXT FILE
      //##############################
      $flink="JavaScript:$flink_view";
      break;
    default:
      $flink="$Dir/$file";
      break;
    }
    //::::::::::::::::::::::::::::::::::::::::
    //METADATA
    //::::::::::::::::::::::::::::::::::::::::
    switch($ftype){
    case "DIRECTORY":
      //##############################
      //DIRECTORY
      //##############################
      $metadata="$date";
      break;
    default:
      $metadata="$size kb, $date";
      break;
    }
    //::::::::::::::::::::::::::::::::::::::::
    //ACTIONS
    //::::::::::::::::::::::::::::::::::::::::
    $action="";
    switch($ftype){
    case "DIRECTORY":
      //##############################
      //DIRECTORY
      //##############################
$actions=<<<ACTIONS
$BUTTONS[Results]<a href="JavaScript:$flink_view">View</a><br/>
ACTIONS;
      break;
    case "TEXT":case "SCRIPT":
      //##############################
      //TEXT FILE
      //##############################
$actions=<<<ACTIONS
$BUTTONS[Results] <a href="JavaScript:$flink_view">View</a><br/>
$BUTTONS[Configure] <a href="JavaScript:$flink_edit">Edit</a><br/>
$BUTTONS[Plot] <a href="JavaScript:$flink_plot">Plot</a><br/>
ACTIONS;
      break;
    case "IMAGE":case "TGZ":
      //##############################
      //TEXT FILE
      //##############################
$actions=<<<ACTIONS
$BUTTONS[Results] <a href="JavaScript:$flink_view">View</a>
ACTIONS;
      break;
    default:
$actions=<<<ACTIONS
<i>No actions</i>
ACTIONS;
      break;
    }
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //CONTENT
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    $maxsize=30;
    if(strlen($file)>$maxsize){
      $fileshort=substr($file,0,$maxsize)."...";
      $explanation="onmouseover='explainThis(this)' explanation='$file'";
    }else{
      $fileshort=$file;
      $explanation="";
    }
    
$result.=<<<TABLE
<tr class="body">
<!-- ---------------------------------------------------------------------- -->
<!-- CHECK COLUMN							    -->
<!-- ---------------------------------------------------------------------- -->
<td class="check">
$checkcol
</td>
<!-- ---------------------------------------------------------------------- -->
<!-- FILE NAME     							    -->
<!-- ---------------------------------------------------------------------- -->
<td class="field" colspan="2"
    onmouseover="
		 $('#actionarrow$i').css('display','block');
		 $(this).css('background-color','$COLORS[text]');
		 "
    onmouseout="
		$('#actionarrow$i').css('display','none');
		$(this).css('background-color','$COLORS[clear]');
		">
  <div style="position:relative">
    $iconimg
    <a href="$flink" $explanation $extraaction>$fileshort<!--($ftype)--></a>
    <br/>
    <span style="font-size:10px">$metadata</span>
    <div id="actionarrow$i" 
	 style="display:none;
		position:absolute;
		right:0px;bottom:0px">
      <a href="JavaScript:void(null)" 
	 onclick="
		  toggleElement('runactions$i');
		  ">
	$BUTTONS[Down]
      </a>
    </div>
    <div id="runactions$i"
	 class="contextual"
	 style="position:absolute;
		right:0px;
		display:none;
		text-align:left;
		font-size:12px;"
	 onmouseover="$('#runactions$i').css('display','block');"
	 onmouseout="$('#runactions$i').css('display','none');"
	 >
      $actions
    </div>

  </div>
</td>
<!-- ---------------------------------------------------------------------- -->
<!-- FILE METADATA 							    -->
<!-- ---------------------------------------------------------------------- -->
<!--
<td class="field">
$metadata
</td>
-->
<!-- ---------------------------------------------------------------------- -->
<!-- ACTIONS         							    -->
<!-- ---------------------------------------------------------------------- -->
<!--
<td class="field">
$actions
</td>
-->
</tr>
TABLE;
     $seli++;
  }//END FOR FILES
  $nselfiles=$seli;  
  $result.="<input type='hidden' name='NumFiles_Submit' value='$nselfiles'/>";
  if($end<$nfiles){
$result.=<<<CONTROLS
<tr style="background-color:$COLORS[text];text-align:center">
  <td colspan="4">
  <a href="JavaScript:$next">...</a>
  </td>
</tr>
CONTROLS;
  }

}

//////////////////////////////////////////////////////////////////////////////////
//RESULT
//////////////////////////////////////////////////////////////////////////////////
end:
echo $result;

finalizePage();
?>
