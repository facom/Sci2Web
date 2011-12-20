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
//FILE PATH
//==================================================
$path="$PHP[ROOTPATH]/$PHP[Dir]";
$imgdir="$PHP[Dir]";
$imgpath="$PHP[ROOTPATH]/$imgdir";
//==================================================
//FILE & IMAGE NAME
//==================================================
list($fname,$fext)=preg_split("/\./",$PHP["File"]);
$fimg="$fname.png";
//==================================================
//FILE & IMAGE DIR AND PATH
//==================================================
$fpath="$path/$PHP[File]";
$fimgdir="$imgdir/$fimg";
$fimgpath="$imgpath/$fimg";

if(file_exists($fimgpath)){
  $preimg=$fimgdir;
}else{
  $preimg="$PROJ[IMGDIR]/image-not-available.jpg";
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//CHECK FILE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
if(!file_exists($fpath)){
$content.=<<<CONTENT
<div class="tabbertab sectab">
<h2>Error</h2>
<p>File '$fpath' does not exist.</p>
CONTENT;
  goto end;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//READ CONFIGURATION FILE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$fconf="$imgpath/.$fname.ps2w";
if(!file_exists($fconf)){
  $pconf=genPlotConf($fpath,$fimgpath);
  $fl=fileOpen("$fconf","w");
  fwrite($fl,$pconf);
  fclose($fl);
}
$n=readConfig($fconf);
ps2wToPlain($CONFIG);

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//FILE PROPERTIES
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//COUNT NUMBER OF COLUMNS
$ncols=systemCmd("grep -v '^#' $fpath | head -n 1 | awk '{print NF}'");

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
	File
      </td>
      <td class="value">
	<input type="hidden" name="Dir_Submit" value="$PHP[Dir]">
	<input type="hidden" name="File_Submit" value="$PHP[File]">
	<input type="hidden" name="DataFiles_Submit" value="$fpath">
        <input type="hidden" name="ImageFile_Submit" value="$fimgpath">
        <input type="hidden" name="ConfFile_Submit" value=".$fname.ps2w">
	<a href="JavaScript:$file_link">$PHP[File]</a>
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

	<input type="text" name="XCols" value="$CONFIG[XCols]" 
	       onchange="popOutHidden(this)" 
	       onmouseover="explainThis(this)" 
	       explanation="This is the column with the abcsisas" >
	<input type="hidden" name="XCols_Submit" value="$CONFIG[XCols]">
      </td>
    </tr>
    <!-- -------------------------------------------------- -->
    <!-- FIELD 						    -->
    <!-- -------------------------------------------------- -->
    <tr>
      <td class="field">Y Columns</td>
      <td class="value">
	<input type="text" name="YCols" value="$CONFIG[YCols]" 
	       onchange="popOutHidden(this)"
	       onmouseover="explainThis(this)" 
	       explanation="This is the column with the ordinates">
	<input type="hidden" name="YCols_Submit" value="$CONFIG[YCols]">
      </td>
    </tr>
    <!-- -------------------------------------------------- -->
    <!-- FIELD 						    -->
    <!-- -------------------------------------------------- -->
    <tr>
      <td class="field">Styles</td>
      <td class="value">
	<input type="text" name="LinesInformation" value="$CONFIG[LinesInformation]" onchange="popOutHidden(this)">
	<input type="hidden" name="LinesInformation_Submit" value="$CONFIG[LinesInformation]">
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
	<input type="text" name="XRange" value="$CONFIG[XRange]" onchange="popOutHidden(this)">
	<input type="hidden" name="XRange_Submit" value="$CONFIG[XRange]">
      </td>
    </tr>
    <!-- -------------------------------------------------- -->
    <!-- FIELD 						    -->
    <!-- -------------------------------------------------- -->
    <tr>
      <td class="field">Y range</td>
      <td class="value">
	<input type="text" name="YRange" value="$CONFIG[YRange]" onchange="popOutHidden(this)">
	<input type="hidden" name="YRange_Submit" value="$CONFIG[YRange]">
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
	<textarea name="ExtraCode" style="width:100%" disabled 
		  onchange="popOutHidden(this)">$CONFIG[ExtraCode]</textarea>
	<input type="hidden" name="ExtraCode_Submit" value="$CONFIG[ExtraCode]">
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
	  <input type="text" name="GridStyle" value="$CONFIG[GridStyle]">
	  <input type="hidden" name="GridStyle_Submit" value="$CONFIG[GridStyle]" onchange="popOutHidden(this)">
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
	  <textarea name="ExtraDecoration" style="width:100%" disabled
		    onchange="popOutHidden(this)">$CONFIG[ExtraDecoration]</textarea>
	  <input type="hidden" name="ExtraDecoration_Submit" value="$CONFIG[ExtraDecoration]">
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
    Title:<input type="text" name="Title" value="$CONFIG[Title]" onchange="popOutHidden(this)">
    <input type="hidden" name="Title_Submit" value="$CONFIG[Title]">
  </div>

  <div style="float:left;top:0px;left:0px text-align:center;padding:5px">
    Y Label:<input type="text" name="YLabel" value="$CONFIG[YLabel]" onchange="popOutHidden(this)">
    <input type="hidden" name="YLabel_Submit" value="$CONFIG[YLabel]">
  </div>

  <div style="float:right;position:absolute;bottom:5px;right:5px;text-align:center">
    X Label:<input type="text" name="XLabel" value="$CONFIG[XLabel]" onchange="popOutHidden(this)">
    <input type="hidden" name="XLabel_Submit" value="$CONFIG[XLabel]">
  </div>

  <div id="plotarea" style="float:left;position:relative;top:0px;left:0px;height:$hplot%;width:100%;text-align:center;">
  <img src="$preimg" height="100%"/>
  </div>

  <div style="float:left;position:absolute;bottom:5px;left:5px;z-index:6000;font-size:14px">
    <input type="submit" name="PlotAction" value="Update">
    <input type="hidden" name="PlotAction_Submit" value="Update">
    <a href="$preimg">
      Download
    </a> |
    <a href="JavaScript:$confile_link">
      PS2W File
    </a> |
    <!--
    <a href="JavaScript:void(0)" onclick="toggleElement('information')">
      Information
    </a> |
    -->
    <a href="$PHP[TMPDIR]/phpout-ajax-plot-$PHP[SESSID]">
      Output
    </a> |
    <a href="$PHP[TMPDIR]/phperr-ajax-plot-$PHP[SESSID]">
      Error
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
<div class="formbuttons" id="buttons">
</div>
<div class="close">
  <button class="image" onclick="window.close()">
    $BUTTONS[Cancel]
  </button>
  <div class="close" style="right:38px;top:7px">$bugbut$bugform</div>
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
