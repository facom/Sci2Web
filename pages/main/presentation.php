<!--Presentation-->
<?
session_start();
//////////////////////////////////////////////////////////////////////////////////
//FILE WITH CONTENT
//////////////////////////////////////////////////////////////////////////////////
$FILE="presentation.html";
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
$bugbut="";
$bugform="";
$PATH="$PHP[PROJPATH]/pages/main/content";
$FHASH=md5($FILE);
$LINK="";
$RID=$PHP["RANDID"];
$RESULT="";
$bugbut="";

//////////////////////////////////////////////////////////////////////////////////
//CHECK PERMISSIONS
//////////////////////////////////////////////////////////////////////////////////
if(isset($_SESSION["User"]) and 
   strstr("$PROJ[ROOTEMAIL]","$_SESSION[User]")
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
     //echo "Saving:$PHP[CONTENT_$FHASH]";br();
     if(isset($PHP["CONTENT_$FHASH"])){
       if(!isBlank($PHP["CONTENT_$FHASH"])){
	 $fl=fileOpen("$PATH/$FILE","w");
	 fwrite($fl,$PHP["CONTENT_$FHASH"]);
	 fclose($fl);
       }
     }
   }
}

//////////////////////////////////////////////////////////////////////////////////
//CONTENT
//////////////////////////////////////////////////////////////////////////////////
$CONTENT=shell_exec("cat $PATH/$FILE");
$TabNum=$PHP["TabNum"];
echo<<<CONTENT
<!--EDIT LINK-->
<form action="?" method="get" enctype="multipart/form-data">
$LINK
<!--NORMAL CONTENT-->
<div id="content_$RID" style="display:block">
CONTENT;

$fl=fopen("$PROJ[TMPPATH]/content.$PHP[RANDID]","w");
fwrite($fl,"<?\necho<<<CONTENT\n");
fwrite($fl,$CONTENT."\n");
fwrite($fl,"CONTENT;\n?>");
fclose($fl);
include("$PHP[TMPPATH]/content.$PHP[RANDID]");

echo<<<CONTENT
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

//////////////////////////////////////////////////////////////////////////////////
//FINALIZE
//////////////////////////////////////////////////////////////////////////////////
finalizePage();
?>
