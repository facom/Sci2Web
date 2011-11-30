<!--Presentation-->
<?
$RELATIVE="../..";
include("$RELATIVE/lib/sci2web.php");
?>

<?
echo<<<PAGE

<p> Welcome to the application platform of $SCI2WEB at the
<b><a href=$PHP[SERVER]>$PROJ[SCI2WEBSITE]</a></b>.  </p>

<!--<img src="$PROJ[IMGDIR]/sci2web-mainlogo.jpg" align="right"
height="100px"/>-->

<p> $SCI2WEB is intended to allow scientist put their
<b>naturally developed scientific applications (NDSA)</b> available to
other colleagues, students and collaborators in order to increase the
impact and visibility of their works.  </p>

<p> <b>Naturally developed scientific application (NDSA)</b> are small
to medium size software developed as a part of a scientific project to
solve specific problems.  Many of them becomes rapidly fundamental in
the research work of entire teams and even in complete fields.
However and although the development of new and easy to use high level
languages are changing the situation, many NDSA have simple natural
interfaces and no graphic interaction with users.  People that use
them have to learn hardly how the software work, how they can use it
and how the information retrieved by them can be processed.</p>

<p> $SCI2WEB is a general purpose web platform designed to provide
simple but powerful web interfaces to NDSAs that improves the
usability of the software, increase the visibility of what normally
are hidden products of scientific research and if required allow
easily other researchers to use the software.  </p>

<p> In this site you will be able to:</p>

<ul>

<li>Know what are the <a href="?TabId=2">applications</a> installed
in this particular server.</li>

<li>Sign up and start to use the applications available at this
server.</li>

<li>Read <a href="?TabId=1">complete documentation</a> and
technical papers about $SCI2WEB and know how NDSAs are migrated to the
platform.</li>

</ul>

PAGE;
?>
