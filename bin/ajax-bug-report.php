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
# TAB PAGE
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
*/
//////////////////////////////////////////////////////////////////////////////////
//LOAD PACKAGE
//////////////////////////////////////////////////////////////////////////////////
$RELATIVE="../";
include("$RELATIVE/lib/sci2web.php");

//////////////////////////////////////////////////////////////////////////////////
//GLOBAL VARIABLES
//////////////////////////////////////////////////////////////////////////////////
$msg="";
$from=$replyto=$PHP["BugUser"];
$email=$PHP["BugRecipient"];
$subject="[Sci2Web:Bug] $PHP[BugSubject] (page: $PHP[BugPage], module: $PHP[BugModule])";

//////////////////////////////////////////////////////////////////////////////////
//PREPARE MESSAGE
//////////////////////////////////////////////////////////////////////////////////
$msg.=<<<MSG
<p>Dear site/application manager/developer,</p>

<p>We have received a bug report from
the <a href="$PROJ[PROJURL]">Sci2Web server site
$PROJ[SCI2WEBSITE]</a>.  Below you will find a full description of the
bug.  Please try to solve the issue and answer to the user who sends
this e-mail with the solution.</p>

<p>
<b>From:</b>$PHP[BugUser]<br/>
<b>Page:</b>$PHP[BugPage]<br/>
<b>Module:</b>$PHP[BugModule]<br/>
<b>Bug subject:</b>$PHP[BugSubject]<br/>
<b>Bug report:</b>
</p>
<p style="padding-left:20px;text-style:italic">
$PHP[BugReport]
</p>
<p></p>
<b>The Sci2Web team</p>
MSG;

//////////////////////////////////////////////////////////////////////////////////
//SEND MESSAGE
//////////////////////////////////////////////////////////////////////////////////
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//MAIN MESSAGE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
echo "Bug report sent to $PHP[BugRecipient]...";
sendMail($email,$subject,$msg,$from,$replyto);

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//COPY TO BUG-REPORT MAIL LIST
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$replyto=$email;
$from="sci2web@gmail.com";
$email="sci2web-bug@googlegroups.com";
sendMail($email,$subject,$msg,$from,$replyto);

sleep(1);
finalizePage();
?>
