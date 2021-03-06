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
$PAGETITLE="File";
include_once("$RELATIVE/lib/sci2web.php");
if(!isset($PHP["TabId"])) $PHP["TabId"]=1;
$PHP["TabId"]--;

//////////////////////////////////////////////////////////////////////////////////
//GLOBAL VARIABLES
//////////////////////////////////////////////////////////////////////////////////
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//INPUT VARIABLES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$header="";
$content="";
$footer="";
$errors="";
$onload="";
$taberror="";
$qerror=true;

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//SWITCHES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//DIRECTORIES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//==================================================
//FILES PATH
//==================================================
$path="$PHP[ROOTPATH]/$PHP[Dir]";
$imgdir="$PHP[Dir]";
$imgpath="$PHP[ROOTPATH]/$imgdir";

//==================================================
//FILE & IMAGE NAME
//==================================================
if(!preg_match("/,/",$PHP["File"]) and
   preg_match("/\.ps2w/",$PHP["File"])){
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //READ PS2W FILES
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  $fpath="$path/$PHP[File]";
  readConfig("$fpath");
  //DATA FILES
  $fdatafiles=$CONFIG["DataFiles"];
  $fdatafiles=preg_replace("/[\"\']/","",$fdatafiles);
  //GET THE IMAGE NAME
  $fimgfile=$CONFIG["ImageFile"];
  $fimgfile=preg_replace("/[\"\']/","",$fimgfile);
  echo "IMAGE: $fimgfile";br();
  list($fname,$fext)=preg_split("/\.\w+$/",$fimgfile);
  //$fname=$fimgfile;
  //COPY PS2W AS SCI2WEB PLOTTING SCRIPT
  systemCmd("cp -rf $fpath $imgpath/.$fname.ps2w");
  //REPLACE " BY '
  shell_exec("sed -i.save -e \"s/\\\"/\'/gi\" $imgpath/.$fname.ps2w");
  //PLOT CONFIGURATION FILE
  $fconf="$imgpath/.$fname.ps2w";
}else{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //GET LIST OF FILES
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  $fname="";
  $datafiles="";
  $ncols=array();
  $i=0;
  foreach(preg_split("/,/",$PHP["File"]) as $file){
    //COUNT NUMBER OF COLUMNS
    $fpath="$path/$file";
    $ncols[]=systemCmd("grep -v '^#' $fpath | head -n 1 | awk '{print NF}'");
    if(!file_exists($fpath)){
      $content.="<div class='tabbertab sectab'><h2>Error</h2><p>File '$fpath' does not exist.</p>";
      goto end;
    }
    //list($tfname,$fext)=preg_split("/(.+)\.\w+/",$file);
    $tfname=$file;
    $fname.="${tfname}_";
    $datafiles.="'$file',";
    $i++;
  }
  if($i>1) $qmultfile=1;
  else $qmultfile=0;
  $fname=preg_replace("/_$/","",$fname);
  $datafiles=preg_replace("/,$/","",$datafiles);
  //PLOT CONFIGURATION FILE
  $fconf="$imgpath/.$fname.ps2w";
}
$fimg="$fname.png";
if(!$qmultfile){
$viewlink=<<<VIEW
<a href="JavaScript:Open('$PROJ[BINDIR]/file.php?Dir=$PHP[Dir]&File=$file&Mode=View','Data File','$PROJ[SECWIN]');">View</a>
VIEW;
}else $viewlink="";

//==================================================
//FILE & IMAGE DIR AND PATH
//==================================================
$fimgdir="$imgdir/$fimg";
$fimgpath="$imgpath/$fimg";

if(file_exists($fimgpath)){
  $preimg=$fimgdir;
}else{
  $preimg="$PROJ[IMGDIR]/image-not-available.jpg";
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//READ CONFIGURATION FILE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//echo "CONF:$fconf";br();
if(!file_exists($fconf)){
  $pconf=genPlotConf($datafiles,$fimgpath);
  //echo $pconf;br();
  $fl=fileOpen("$fconf","w");
  fwrite($fl,$pconf);
  fclose($fl);
}
$n=readConfig($fconf);
ps2wToPlain($CONFIG);

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//FILE PROPERTIES
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//CHECK PLOTTING DIRECTORY
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
if(!is_dir($imgpath)){
  $out=systemCmd("mkdir $imgpath");
  if($PHP["?"]){
$content.=<<<CONTENT
<div class="tabbertab sectab">
<h2>Error</h2>
<p>An error has occurred creating s2w directory</p>
CONTENT;
    blankFunc();
    goto end;
  }
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//CONFIGURING ACTIONS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

//////////////////////////////////////////////////////////////////////////////////
//ACTIONS
//////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////
//LOAD INFORMATION
//////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////
//GENERATE CONTENT
//////////////////////////////////////////////////////////////////////////////////

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//TAB HEAD
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$content.=<<<CONTENT
<div class="tabbertab sectab">
<h2>Plot</h2>
CONTENT;

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//HEADER
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//PLOT TAB
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$ajaxcmd=<<<AJAX
loadContent
  (
   '$PROJ[BINDIR]/ajax-plot.php?Dir=$PHP[Dir]&File=$PHP[File]&Image=$fimg&ImgDir=$imgdir&ConfFile=.$fname.ps2w',
   'plotarea',
   function(element,rtext){
     element.innerHTML=rtext;
     $('#DIVBLANKET').css('display','none');
     $('#DIVOVER').css('display','none');
   },
   function(element,rtext){
     $('#DIVBLANKET').css('display','block');
     $('#DIVOVER').css('display','block');
   },
   function(element,rtext){
   },
   -1,
   true
   )
AJAX;
$onload=genOnLoad($ajaxcmd,'load');
//$onload="";

$border="dashed gray 0px";
$hdif=$wdif=1;
$intspc=15;
$wplot=60;
$wform=100-$wplot-$wdif;
$hplot=82;
$hinfo=100-$hplot-$hdif;
$imgmore="<img src=$PROJ[IMGDIR]/icons/actions/More.gif height=15px/>";
$imgless="<img src=$PROJ[IMGDIR]/icons/actions/Less.gif height=15px/>";
$moreless="{'More':'$imgmore','Less':'$imgless'}";
$colspan=2;

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//LINKS
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$confile_link=fileWebOpen($imgdir,".$fname.ps2w",'View');
$outfile_link=fileWebOpen($PHP[TMPDIR],"phpout-ajax-plot-$PHP[SESSID]",'View');
$file_link=fileWebOpen($PHP["Dir"],$PHP["File"],'View');

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//SELECTION FIELDS
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//
$selXScale=genSelect(array("'linear'","'log'"),"XScale",$CONFIG["XScale"],"onchange='popOutHidden(this)'").
	      "<input type='hidden' name='XScale_Submit' value=\"$CONFIG[XScale]\">";
$selYScale=genSelect(array("'linear'","'log'"),"YScale",$CONFIG["YScale"],"onchange='popOutHidden(this)'").
	      "<input type='hidden' name='YScale_Submit' value=\"$CONFIG[YScale]\">";
$selLegLoc=genSelect(
	      array("'best'","'upper left'"),
	      "LegendLocation",$CONFIG["LegendLocation"],"onchange='popOutHidden(this)'").
	      "<input type='hidden' name='LegendLocation_Submit' value=\"$CONFIG[LegendLocation]\">";
$selSetGrid=genSelect(array("'Yes'","'No'"),"SetGrid",$CONFIG["SetGrid"],"onchange='popOutHidden(this)'").
	      "<input type='hidden' name='SetGrid_Submit' value=\"$CONFIG[SetGrid]\">";

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//GENERATE FORM
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//*
$ajaxcmd=<<<AJAX
submitForm
  ('formplot',
   '$PROJ[BINDIR]/ajax-plot.php?Dir=$PHP[Dir]&File=$PHP[File]&Image=$fimg&ImgDir=$imgdir&ConfFile=.$fname.ps2w',
   'plotarea',
   function(element,rtext){
     element.innerHTML=rtext;
     $('#DIVBLANKET').css('display','none');
     $('#DIVOVER').css('display','none');
   },
   function(element,rtext){
     $('#DIVBLANKET').css('display','block');
     $('#DIVOVER').css('display','block');
   },
   function(element,rtext){
   }
   )
AJAX;
//*/
$BaseDir=basename($PHP["Dir"]);
$content.=<<<CONTENT
<!-- ---------------------------------------------------------------------- -->
<!-- FORM								    -->
<!-- ---------------------------------------------------------------------- -->
<form id="formplot" method="get" 
      action="JavaScript:void(null)" 
      enctype="multipart/form-data" 
      onSubmit="$ajaxcmd">
<div style="position:relative;left:0px;float:left;width:$wform%;height:100%;overflow:auto;border:$border">
  <table id="plotform" class="form">
    <!-- -------------------------------------------------- -->
    <!-- GROUP 						    -->
    <!-- -------------------------------------------------- -->
    <tr>
      <td class="group" colspan="$colspan">Basic information</td>
    </tr>
    <!-- -------------------------------------------------- -->
    <!-- INFORMATION					    -->
    <!-- -------------------------------------------------- -->
    <tr>
      <td class="field">
	Directory
      </td>
      <td class="value">
	<input type="hidden" name="Dir_Submit" value="$PHP[Dir]">
	<b onmouseover="explainThis(this)" explanation="$PHP[Dir]">
	  $BaseDir
	</b>
      </td>
    </tr>
    <tr>
      <td class="field">
	Image
      </td>
      <td class="value">
        <input type="hidden" name="ImageFile_Submit" value="$fimg">
	<!--<input type="text" name="ImageFile" 
	       onchange="popOutHidden(this)"
	       onmouseover="explainThis(this)" explanation="Image file"
	       value="$fimg">-->
	<b>$fimg</b>
      </td>
    </tr>
    <tr>
      <td class="field">
	Files
      </td>
      <td class="value">
	<input type="hidden" name="File_Submit" value="$PHP[File]">
        <input type="hidden" name="ConfFile_Submit" value=".$fname.ps2w">
	<input type="hidden" name="DataFiles_Submit" value="$CONFIG[DataFiles]">
	<input type="text" name="DataFiles" value="$CONFIG[DataFiles]" 
	       onchange="popOutHidden(this)" 
	       onfocus="$('#explanationf').toggle('fast',null);"
	       onblur="$('#explanationf').toggle('fast',null);"> $viewlink
	<br/>
	<div id="explanationf" class="explanationtxt" style="display:none">
	  Give the name of the files separated by ','
	</div>
      </td>
    </tr>
    <!-- -------------------------------------------------- -->
    <!-- GROUP 						    -->
    <!-- -------------------------------------------------- -->
    <tr>
      <td class="group" colspan="$colspan">Data properties</td>
    </tr>
    <!-- -------------------------------------------------- -->
    <!-- FIELD 						    -->
    <!-- -------------------------------------------------- -->
    <tr>
      <td class="field">X Column</td>
      <td class="value">
	<input type="hidden" name="XCols_Submit" value="$CONFIG[XCols]">
	<input type="text" name="XCols" value="$CONFIG[XCols]" 
	       onchange="popOutHidden(this)" 
	       onfocus="$('#explanationx').toggle('fast',null);"
	       onblur="$('#explanationx').toggle('fast',null);">
	<br/>
	<div id="explanationx" class="explanationtxt" style="display:none">
	  Indicate the column(s) with the value of the abcisas.<br/>
	  Ex.: '1' (single file), '1,2' (multiple file)
	</div>
      </td>
    </tr>
    <!-- -------------------------------------------------- -->
    <!-- FIELD 						    -->
    <!-- -------------------------------------------------- -->
    <tr>
      <td class="field">Y Columns</td>
      <td class="value">
	<input type="hidden" name="YCols_Submit" value="$CONFIG[YCols]">
	<input type="text" name="YCols" value="$CONFIG[YCols]" 
	       onchange="popOutHidden(this)"
	       onfocus="$('#explanationy').toggle('fast',null);"
	       onblur="$('#explanationy').toggle('fast',null);"
	<br/>
	<div id="explanationy" class="explanationtxt" style="display:none">
	  Column(s) with the value of the ordinates. Use brackets to
	  group columns for multiple files<br/> Ex.: '[1]' (single
	  file, single column), '[1,2]' (multiple columns, single
	  file), '[1,2],[3]' (multiple files).
	</div>
      </td>
    </tr>
    <!-- -------------------------------------------------- -->
    <!-- FIELD 						    -->
    <!-- -------------------------------------------------- -->
    <tr>
      <td class="field">Styles</td>
      <td class="value">
	<input type="hidden" name="LinesInformation_Submit" 
	       value="$CONFIG[LinesInformation]">
	<input type="text" name="LinesInformation" 
	       value="$CONFIG[LinesInformation]" 
	       onchange="popOutHidden(this)"
	       onfocus="$('#explanationl').toggle('fast',null);"
	       onblur="$('#explanationl').toggle('fast',null);"
	       >
	<br/>
	<div id="explanationl" class="explanationtxt" style="display:none">
	  Style specification for plot.  Format:
	  ('label','color','linestyle',<br/>linewidth,'markerstyle',markersize).
	  Where:<br/> linestyle: '-' (continuous), '--' (dashed), ':'
	  (dotted), '-.' (dashed-dotted).<br/>  markerstyle: '*'
	  (star), '+' (plus), '.' (dot), 'o' (circle), 's'
	  (squares).<br/>Leave blank line or marker style to disable.
	  <br>Ex.:[('Velocity','blue','--',2,'',1)] (single column),
	  [('Vx','red','-',2,'+',3),('Vy','gren','-',2,'+',3)],
	  [('Vtot','blue','',1,'*',3)] (multiple columns, multiple
	  files)
	</div>
      </td>
    </tr>
    <!-- -------------------------------------------------- -->
    <!-- GROUP 						    -->
    <!-- -------------------------------------------------- -->
    <tr>
      <td class="group" colspan="$colspan">Properties</td>
    </tr>
    <!-- -------------------------------------------------- -->
    <!-- FIELD 						    -->
    <!-- -------------------------------------------------- -->
    <tr>
      <td class="field">X range</td>
      <td class="value">
	<input type="hidden" name="XRange_Submit" value="$CONFIG[XRange]">
	<input type="text" name="XRange" value="$CONFIG[XRange]" 
	       onchange="popOutHidden(this)"
	       onfocus="$('#explanationxr').toggle('fast',null);"
	       onblur="$('#explanationxr').toggle('fast',null);">
	<br/>
	<div id="explanationxr" class="explanationtxt" style="display:none">
	  Range for abcisas.<br/>Ex.:'Auto' (auto range), (2,3) (custom range)
	</div>
      </td>
    </tr>
    <!-- -------------------------------------------------- -->
    <!-- FIELD 						    -->
    <!-- -------------------------------------------------- -->
    <tr>
      <td class="field">Y range</td>
      <td class="value">
	<input type="hidden" name="YRange_Submit" value="$CONFIG[YRange]">
	<input type="text" name="YRange" value="$CONFIG[YRange]" 
	       onchange="popOutHidden(this)"
	       onfocus="$('#explanationxr').toggle('fast',null);"
	       onblur="$('#explanationxr').toggle('fast',null);">
	<br/>
	<div id="explanationxr" class="explanationtxt" style="display:none">
	  Range for ordinates.<br/>Ex.:'Auto' (auto range), (2,3) (custom range)
	</div>
      </td>
    </tr>
    <!-- -------------------------------------------------- -->
    <!-- FIELD 						    -->
    <!-- -------------------------------------------------- -->
    <tr>
      <td class="field">
	<input type="checkbox" name="ExtraCode_Query" onclick="enableElement(this)">
	Extra code
      </td>
      <td class="value">
	<input type="hidden" name="ExtraCode_Submit" value="$CONFIG[ExtraCode]">
	<textarea name="ExtraCode" style="width:100%" disabled 
		  onchange="popOutHidden(this)">$CONFIG[ExtraCode]</textarea>
      </td>
    </tr>
    <!-- -------------------------------------------------- -->
    <!-- ACTIONS 						    -->
    <!-- -------------------------------------------------- -->
    <tr>
      <td class="action" colspan="$colspan">
	<a action="More" href="JavaScript:void(null)" 
	   onclick="toggleHidden(this,'hiddenblock',$moreless)">
	  $imgmore
	</a>
      </td>
    </tr>
  </table>
  <div class="hiddenblock">
    <table id="plotform" class="form">
      <!-- -------------------------------------------------- -->
      <!-- FIELD 						    -->
      <!-- -------------------------------------------------- -->
      <tr>
	<td class="field">X axis scale</td>
	<td class="value">
	  $selXScale
	</td>
      </tr>
      <!-- -------------------------------------------------- -->
      <!-- FIELD 						    -->
      <!-- -------------------------------------------------- -->
      <tr>
	<td class="field">Y axis scale</td>
	<td class="value">
	  $selYScale
	</td>
      </tr>
      <!-- -------------------------------------------------- -->
      <!-- FIELD 						    -->
      <!-- -------------------------------------------------- -->
      <tr>
	<td class="field">Legend location</td>
	<td class="value">
	  $selLegLoc
	</td>
      </tr>
      <!-- -------------------------------------------------- -->
      <!-- FIELD 						    -->
      <!-- -------------------------------------------------- -->
      <tr>
	<td class="field">Grid</td>
	<td class="value">
	  $selSetGrid
	</td>
      </tr>
      <!-- -------------------------------------------------- -->
      <!-- GRID STYLE 						    -->
      <!-- -------------------------------------------------- -->
      <tr>
	<td class="field">Grid style</td>
	<td class="value">
	  <input type="hidden" name="GridStyle_Submit" value="$CONFIG[GridStyle]">
	  <input type="text" name="GridStyle" value="$CONFIG[GridStyle]"
		 onchange="popOutHidden(this)">
	</td>
      </tr>
      <!-- -------------------------------------------------- -->
      <!-- FIELD 						    -->
      <!-- -------------------------------------------------- -->
      <tr>
	<td class="field">
	  <input type="checkbox" name="ExtraDecoration_Query" onclick="enableElement(this)">
	  Extra decoration
	</td>
	<td class="value">
	  <input type="hidden" name="ExtraDecoration_Submit" 
		 value="$CONFIG[ExtraDecoration]">
	  <textarea name="ExtraDecoration" style="width:100%" disabled
		    onchange="popOutHidden(this)">$CONFIG[ExtraDecoration]</textarea>
	</td>
      </tr>
      <!-- -------------------------------------------------- -->
      <!-- -------------------------------------------------- -->
    </table>
  </div>
  <br/><br/><br/>
  <br/><br/><br/>
  <br/><br/><br/>
</div>
<!-- ---------------------------------------------------------------------- -->
<!-- IMAGE								    -->
<!-- ---------------------------------------------------------------------- -->
<div style="position:relative;left:${intspc}px;float:left;width:$wplot%;height:100%;overflow:auto;border:$border">
  $PROJ[DIVBLANKET]
  $PROJ[DIVOVER]
  $onload

  <div style="float:right;top:0px;right:0px;text-align:center;padding:5px">
    <input type="hidden" name="Title_Submit" value="$CONFIG[Title]">
    Title:<input type="text" name="Title" value="$CONFIG[Title]" onchange="popOutHidden(this)">
  </div>

  <div style="float:left;top:0px;left:0px text-align:center;padding:5px">
    <input type="hidden" name="YLabel_Submit" value="$CONFIG[YLabel]">
    Y Label:<input type="text" name="YLabel" value="$CONFIG[YLabel]" onchange="popOutHidden(this)">
  </div>

  <div style="float:right;position:absolute;bottom:5px;right:5px;text-align:center">
    <input type="hidden" name="XLabel_Submit" value="$CONFIG[XLabel]">
    X Label:<input type="text" name="XLabel" value="$CONFIG[XLabel]" onchange="popOutHidden(this)">
  </div>

  <div id="plotarea" style="float:left;position:relative;top:0px;left:0px;height:$hplot%;width:100%;text-align:center;">
  <img src="$preimg" height="100%"/>
  </div>

  <div style="float:left;position:absolute;bottom:5px;left:5px;z-index:6000;font-size:14px">
    <input type="hidden" name="PlotAction_Submit" value="Update">
    <input type="submit" name="PlotAction" value="Update">
    <a href="$preimg">
      Download
    </a> |
    <a href="JavaScript:$confile_link">
      PS2W File
    </a> |
    <a href="JavaScript:$outfile_link">
      Output/Error
    </a> 
  </div>
  
  <div id="information" class="userbox">
  Download
  </div>
  </form>
</div>
CONTENT;

//==================================================
//COMMAND PANEL
//==================================================

//==================================================
//IMAGE PANEL
//==================================================

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//TAB CLOSE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$content.=<<<CONTENT
</div>
CONTENT;

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//FOOTER
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
list($bugbut,$bugform)=genBugForm2("PlotGeneral","General problems with plot");
$footer.= <<<CONF
</div>
<div style="position:absolute;
	    top:0px;right:18px">
  <div class="actionbutton">
    $bugbut$bugform
  </div>
  <div class="actionbutton">
    <button class="image" onclick="window.location.reload()">
      $BUTTONS[Update]
    </button>
  </div>
  <div class="actionbutton">
    <button class="image" onclick="window.close()">
      $BUTTONS[Cancel]
    </button>
  </div>
</div>
CONF;

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//ERROR
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
end:
if($qerror){
}

//////////////////////////////////////////////////////////////////////////////////
//CONTENT DISPLAY
//////////////////////////////////////////////////////////////////////////////////
$DEBUG=genDebug("bottom");
$head="";
$head.=genHead("","");

echo<<<CONTENT
<html>
<head>
$head
</head>

<body>
$PROJ[ELBLANKET]
$PROJ[ELOVER]  
$header
<div class="tabber sectabber" id="$PHP[TabId]" style="height:83%">
$DEBUG
$content
$taberror
</div>
$footer

</body>
</html>

CONTENT;
?>
