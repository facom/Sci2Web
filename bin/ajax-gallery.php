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
$result="";
$dir=$PHP["Dir"];
$path="$PHP[ROOTPATH]/$dir";

//////////////////////////////////////////////////////////////////////////////////
//GET LIST OF IMAGES
//////////////////////////////////////////////////////////////////////////////////

if(!isset($PHP["Criterium"])){
  $criterium="*.png *.jpg *.gif *.tiff";
}else{
  $criterium=$PHP["Criterium"];
}
//echo "CRIT: $criterium";br();
$listimgs=systemCmd("cd $path;ls -m $criterium");
if(isBlank($listimgs)){
  $images=array("null");
}else{
  $images=preg_split("/\s*,\s*/",$listimgs);
}
//printArray($images);
//////////////////////////////////////////////////////////////////////////////////
//ACTIONS
//////////////////////////////////////////////////////////////////////////////////
if($PHP["Get"]=="Image"){
  $image="null";
  if(isset($PHP["ImgNum"])){
    $image=$images[$PHP["ImgNum"]];
  }else{
    $image=$images[0];
  }
  if(!file_exists("$path/$image")){
    $imgsrc="$PROJ[IMGDIR]/image-not-available.jpg";
  }else{
    $imgsrc="$dir/$image";
  }
  //CHECK IF IT COMES FROM A .ps2w FILE
  $modify="";
  $ps2wfile=systemCmd("cd $path;grep '$image' *.ps2w | cut -f 1 -d ':'");
  if(isBlank($ps2wfile)) 
    $ps2wfile=systemCmd("cd $path;grep '$image' .*.ps2w | head -n 1 | cut -f 1 -d ':'");
  if(!isBlank($ps2wfile) and file_exists("$path/$ps2wfile")){
$modify=<<<PLOT
  (<a href="JavaScript:Open('$PROJ[BINDIR]/plot.php?Dir=$dir&File=$ps2wfile','Plot $image','$PROJ[PLOTWIN]')">Modify</a>)
PLOT;
  }

$result.=<<<RESULT
<div style="position:absolute;top:10px;right:10px">
  <a href="JavaScript:void(null)"
     onmouseover="explainThis(this)"
     explanation="Reload"
     onclick="loadcmd=$('#loadimgcmd').attr('value');
	      setTimeout(loadcmd,0);">
    $BUTTONS[Update]
  </a>
</div>
<div style="
	    font-style:italic;
	    "
     >
  <a href="JavaScript:Open('$imgsrc','Image','$PROJ[SECWIN]')">
    $image
  </a>
  $modify
</div>
<div style="padding:5px">
<img src="$imgsrc" height="90%">
</div>
RESULT;
}
if($PHP["Get"]=="Thumbnails"){
  $i=0;
  $width=15;
$result.=<<<RESULT
<div style="position:absolute;top:10px;right:10px">
  <a href="JavaScript:void(null)"
     onmouseover="explainThis(this)"
     explanation="Reload"
     onclick="loadcmd=$('#loadthumbscmd').attr('value');
	      setTimeout(loadcmd,0);">
    $BUTTONS[Update]
  </a>
</div>
RESULT;
  foreach($images as $image){
    if(!file_exists("$path/$image")) continue;
    $type=systemCmd("file $path/$image");
    if(!preg_match("/image/",$type)) continue;
    //GENERATE THUMBNAIL
    $imghash=systemCmd("md5sum $path/$image | cut -f 1 -d ' '");
    $oldimghash=systemCmd("cd $path;cat .$image.hash");
    if(!file_exists("$path/.$image.thumb") or $imghash!=$oldimghash){
      systemCmd("cd $path;convert -resize 20% $image .$image.thumb");
      systemCmd("cd $path;echo $imghash > .$image.hash");
    }
    $imgsrc="$dir/.$image.thumb";
    $pos=$width*$i+2*$i;
$result.=<<<RESULT
    <div style="
		position:absolute;
		top:5px;
		left:${pos}%;
		border:solid black 1px;
		width:${width}%;
		">
      <a href="JavaScript:Open('$imgsrc','Image','$PROJ[SECWIN]')">
	$image
      </a>
      <a href="JavaScript:void(null)"
	 onclick="$('#loadimgnum').attr('value','$i');
		  loadcmd=$('#loadimgcmd').attr('value');
		  setTimeout(loadcmd,0);">
	<img src="$imgsrc" width="90%">
      </a>
    </div>
RESULT;
    $i++;
  }
}

//////////////////////////////////////////////////////////////////////////////////
//FINALIZE
//////////////////////////////////////////////////////////////////////////////////
end:
echo $result;
?>
