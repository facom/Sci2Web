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
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//INPUT VARIABLES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$Action=$PHP["Action"];
$Dir=$PHP["Dir"];
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
$dpath="$PHP[ROOTPATH]/$Dir";
$flink="$Dir/$File";
$fpath="$PHP[ROOTPATH]/$flink";

//////////////////////////////////////////////////////////////////////////////////
//ACTION
//////////////////////////////////////////////////////////////////////////////////
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//DOWNLOAD FILES
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
if($Action=="Download"){
  $objects="";
  foreach($_GET as $object){
    if(preg_match("/ObjDown:(.+)/",$object,$objs)){
      $objects.="$objs[1] ";
    }
  }
  if(strlen($objects)>0){
    $tarhash=md5($objects);  
    $tarfile="tarball-$tarhash-$PHP[SESSID].tar.gz";
    $tardir="$PROJ[TMPDIR]/$tarfile";
    $tarpath="$PROJ[TMPPATH]/$tarfile";
    systemCmd("cd $dpath;tar zcf $tarpath $objects");
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
//DOWNLOAD FILES
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
if($Action=="Download"){
  $objects="";
  foreach($_GET as $object){
    if(preg_match("/ObjDown:(.+)/",$object,$objs)){
      $objects.="$objs[1] ";
    }
  }
  if(strlen($objects)>0){
    $tarhash=md5($objects);  
    $tarfile="tarball-$tarhash-$PHP[SESSID].tar.gz";
    $tardir="$PROJ[TMPDIR]/$tarfile";
    $tarpath="$PROJ[TMPPATH]/$tarfile";
    systemCmd("cd $dpath;tar zcf $tarpath $objects");
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
//DOWNLOAD FILES
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
if($Action=="RemoveResults"){
  $results=array();
  foreach($_GET as $result){
    if(preg_match("/ObjDown:(.+)/",$result,$res)){
      $reshash=preg_replace("/\.tar\.gz/","",$res[1]);
      $results[]="$reshash";
    }
  }
  $nresults=count($results);
  if($nresults>0){
    $dbname="$_SESSION[App]_$_SESSION[Version]";
    foreach($results as $runhash){
      //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
      //REMOVE DATABASE ENTRY
      //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
      mysqlCmd("delete from $dbname where dbrunhash='$runhash'");
      //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
      //REMOVE FILE
      //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
      systemCmd("rm -rf $dpath/$runhash.tar.gz");
    }
    $result.="<i>$nresults results removed, please update results.</i>";
  }else{
    $result.="<i>No files selected</i>";
  }
  goto end;
}
if($Action=="Download"){
  $objects="";
  foreach($_GET as $object){
    if(preg_match("/ObjDown:(.+)/",$object,$objs)){
      $objects.="$objs[1] ";
    }
  }
  if(strlen($objects)>0){
    $tarhash=md5($objects);  
    $tarfile="tarball-$tarhash-$PHP[SESSID].tar.gz";
    $tardir="$PROJ[TMPDIR]/$tarfile";
    $tarpath="$PROJ[TMPPATH]/$tarfile";
    systemCmd("cd $dpath;tar zcf $tarpath $objects");
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
  //echo "PATH:$path";br();
  if(!is_dir($path)){
    $result="<tr><td colspan=10>No directory '$Dir'</td></tr>";
    goto end;
  }
  $criterium="*";
  if(isset($PHP["Criterium"])) $criterium=$PHP["Criterium"];

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //LIST OF FILES
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  $files=listFiles($path,$criterium);
  $result.="";
  $i=0;
  if(!file_exists("$path/.root")){
    array_unshift($files,"..");
  }
  $nfiles=count($files);

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
<input type="checkbox" name="obj$i" value="ObjDown:$file"
       onchange="popOutHidden(this)"><!--$i-->
<input type="hidden" name="obj${i}_Submit" value="off">
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
$ajax_subdir=<<<AJAX
loadContent
  (
   '$PROJ[BINDIR]/ajax-trans-file.php?Action=GetList&Dir=$Dir/$file&Start=0',
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
   },
   -1,
   true
   )
AJAX;
      blankFunc();
      $flink="JavaScript:$ajax_subdir";
      break;
    case "TEXT":case "SCRIPT":
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
<a href="JavaScript:$flink_view">View</a>
ACTIONS;
      break;
    case "TEXT":case "SCRIPT":
      //##############################
      //TEXT FILE
      //##############################
$actions=<<<ACTIONS
<a href="JavaScript:$flink_view">View</a> | 
<a href="JavaScript:$flink_edit">Edit</a> | 
<a href="JavaScript:$flink_plot">Plot</a>
ACTIONS;
      break;
    case "IMAGE":case "TGZ":
      //##############################
      //TEXT FILE
      //##############################
$actions=<<<ACTIONS
<a href="JavaScript:$flink_view">View</a>
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
<td>
  $iconimg<a href="$flink" $explanation>$fileshort<!--($ftype)--></a>
</td>
<!-- ---------------------------------------------------------------------- -->
<!-- FILE METADATA 							    -->
<!-- ---------------------------------------------------------------------- -->
<td>
$metadata
</td>
<!-- ---------------------------------------------------------------------- -->
<!-- ACTIONS         							    -->
<!-- ---------------------------------------------------------------------- -->
<td>
$actions
</td>
</tr>
TABLE;
 
  }//END FOR FILES
  
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
//echo "<textarea>$result</textarea>";
echo $result;
?>
