<?
session_start();
//////////////////////////////////////////////////////////////////////////////////
//FILE WITH CONTENT
//////////////////////////////////////////////////////////////////////////////////
$FILE="$_SESSION[App]-down.html";
//////////////////////////////////////////////////////////////////////////////////
?>

<?
//////////////////////////////////////////////////////////////////////////////////
//LIBRARY
//////////////////////////////////////////////////////////////////////////////////
$RELATIVE="../..";
include("$RELATIVE/lib/sci2web.php");

//////////////////////////////////////////////////////////////////////////////////
//VARIABLES
//////////////////////////////////////////////////////////////////////////////////
$APPNAME="$_SESSION[App]";
$APPDIR="$PROJ[APPSDIR]/$APPNAME";
$APPPATH="$PROJ[APPSPATH]/$APPNAME";
$FHASH=md5($FILE);
$LINK="";
$RID=$PHP["RANDID"];
$RESULT="";

//////////////////////////////////////////////////////////////////////////////////
//CHECK PERMISSIONS
//////////////////////////////////////////////////////////////////////////////////
if(isset($_SESSION["User"]) and 
   (strstr($_SESSION["Contributors"],"$_SESSION[User]") or
    strstr("$PROJ[ROOTEMAIL]","$_SESSION[User]")
    )
   ){
   list($bugbut,$bugform)=genBugForm2("PageEdition","Editing page content");
$LINK=<<<LINK
<div id="editlink_$RID" class="editlink">
  $bugbut
  <a href="JavaScript:void(null)"
  onclick="toggleToEdition('content_$RID','edition_$RID','editlink_$RID','$COLORS[text]','$PROJ[PROJDIR]/lib/ckfinder')">
  Edit
  </a>
</div>
LINK;
   blankFunc();
   //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
   //SAVE
   //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
   if(isset($PHP["SaveContent"])){
     if(!isBlank($PHP["CONTENT_$FHASH"])){
       $fl=fileOpen("$APPPATH/$FILE","w");
       fwrite($fl,$PHP["CONTENT_$FHASH"]);
       fclose($fl);
     }
   }
 }

//////////////////////////////////////////////////////////////////////////////////
//CONTENT
//////////////////////////////////////////////////////////////////////////////////
$CONTENT=shell_exec("cat $APPPATH/$FILE");
$TabNum=$PHP["TabNum"];
echo<<<CONTENT
<!--EDIT LINK-->
<form action="?" method="get" enctype="multipart/form-data">
$LINK
<!--NORMAL CONTENT-->
<div id="content_$RID" style="display:block">
$CONTENT
</div>
<!--EDIT CONTENT-->
<input id="TabId" type="hidden" name="TabId" value="$TabNum">
<textarea id="edition_$RID" name="CONTENT_$FHASH" style="display:none;height:100%">
$CONTENT
</textarea>
</form>
$bugform
$RESULT
CONTENT;
?>
