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

//////////////////////////////////////////////////////////////////////////////////
//GLOBAL VARIABLES
//////////////////////////////////////////////////////////////////////////////////
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//INITIALIZATION
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$msg="";
$img="<img src='$PROJ[IMGDIR]/image-not-available.jpg' height='100%'/>";

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//FILE INFORMATION
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$dir="$PHP[Dir]";
$path="$PHP[ROOTPATH]/$PHP[Dir]";
$imgdir="$PHP[ImgDir]";
$imgpath="$PHP[ROOTPATH]/$PHP[ImgDir]";

$fpath="$path/$PHP[File]";
$fimgdir="$imgdir/$PHP[Image]";
$fimgpath="$imgpath/$PHP[Image]";

$fconfdir="$imgdir/$PHP[ConfFile]";
$fconfpath="$imgpath/$PHP[ConfFile]";

//////////////////////////////////////////////////////////////////////////////////
//ACTIONS
//////////////////////////////////////////////////////////////////////////////////
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//SAVE CONFIGURATION INFORMATION
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//printArray($PHP,"PHP");
if(isset($PHP["PlotAction"])){
  if($PHP["PlotAction"]=="Update"){
    savePlotConf($fconfpath);	
  }
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//EXECUTE PYTHON PLOTTING SCRIPT
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
systemCmd("cd $path;MPLCONFIGDIR='$PROJ[TMPPATH]' python $PROJ[BINPATH]/sci2web-plot $fconfpath");
if($PHP["?"]){
  $notification="Error";
  $style="background-color:pink";
}else{
  $notification="Success";
  $style="background-color:lightgreen";
}
//////////////////////////////////////////////////////////////////////////////////
//DISPLAY OUTPUT
//////////////////////////////////////////////////////////////////////////////////
end:
$img=$fimgdir;
if(!file_exists($fimgpath)){
 $img="$PROJ[IMGDIR]/image-not-available.jpg";
}

echo<<<PLOT
<div id="imgnot" class="notification" style="display:none;$style"></div>
<img src="$img" height="100%" onload="notDiv('imgnot','$notification')"/>
PLOT;

finalizePage();
?>
