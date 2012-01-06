#!/usr/bin/perl
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
#          _ ___               _     
#         (_)__ \             | |    
# ___  ___ _   ) |_      _____| |__  
#/ __|/ __| | / /\ \ /\ / / _ \ '_ \ 
#\__ \ (__| |/ /_ \ V  V /  __/ |_) |
#|___/\___|_|____| \_/\_/ \___|_.__/ 
#
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
# UTILITY SCRIPT
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$BASEDIR=`dirname $0`;chop $BASEDIR;
$ROOTDIR=`dirname $BASEDIR`;chop $ROOTDIR;
$ABSDIR=`cd $ROOTDIR;pwd`;chop $ABSDIR;
$ROOTDIR=$ABSDIR;
require "$BASEDIR/sci2web.pm";

################################################################################
#OPERATORS
################################################################################
use Switch;
sub vprint{vprintFunc(@_);}
sub rprint{rprintFunc(@_);}
sub Error{error(@_);}
sub Exit{exitFunc(@_);}

################################################################################
#ACTION
################################################################################
$Action=$ARGV[0];
Error "You should provide an action" if($Action=~/-/ or $Action!~/\w/);

################################################################################
#COMMON OPTIONS
################################################################################
@cmdopt=("help|?","verbose|V","man|m");
switch($Action){
    case "clean" {
	push(@cmdopt,("tmp|t","runs|r","db|d","log|l","all|a","results|R"));
    }
    case "init" {
	push(@cmdopt,("appname|a=s","vername|v=s"));
	push(@cmdopt,("emails|e=s","changeslog|c=s"));
    }
    case "contvars" {
	push(@cmdopt,("appdir|d=s"));
    }
    case "genfiles" {
	push(@cmdopt,("runconf|r=s","rundir|d=s"));
    }
    case "install" {
	push(@cmdopt,("appdir|d=s"));
    }
    case "release" {
	push(@cmdopt,("appname|a=s","vername|v=s","sci2web|s"));
    }
    case "newversion" {
	push(@cmdopt,("appname|a=s","tempver|t=s","newver|n=s","emails|e=s"));
    }
    case "remove" {
	push(@cmdopt,("appname|a=s","vername|v=s"));
    }
    case "genrun" {
	push(@cmdopt,("appdir|a=s","template|t=s","template-file|f",
		      "rundir|r=s","local|l"));
    }
    case "saveresult" {
	push(@cmdopt,("rundir|r=s"));
    }
    case "changestatus" {
	push(@cmdopt,("rundir|r=s","status|s=s"));
    }
    case "down" {
	push(@cmdopt,("results|R","sources|s","rundir|r=s"));
    }
    case "showdb" {
	push(@cmdopt,("describe|d","entries|e","table|t=s","show|s"));
    }
    else {
    	Error "Action '$Action' not recognized";
    }
}
GetOptions(\%options,@cmdopt) or pod2usage(2);
pod2usage(1) if $options{help};
pod2usage(-verbose => 2) if $options{man};
$VERBOSE=1 if($options{verbose});

################################################################################
#ACTIONS
################################################################################
switch($Action){
    #======================================================================
    #CLEAN ALL THE SCI2WEB SITE
    #======================================================================
    case "clean" {
	rprint "Cleaning Sci2Web server site","=";
	$qclean=0;
	$ans="n";
	if($options{tmp} or $options{all}){
	    rprint "Cleaning tmp directory...";
	    $ans=promptAns("Do you want to proceed?(y/n)",$ans) if($ans!~/a/i);
	    if($ans=~/[ya]/i){
		sysCmd("rm -rf $ROOTDIR/tmp/[^.]*");
		$qclean=1;
	    }
	}
	if($options{log} or $options{all}){
	    rprint "Cleaning log directory...";
	    $ans=promptAns("Do you want to proceed?(y/n)",$ans) if($ans!~/a/i);
	    if($ans=~/[ya]/i){
		sysCmd("rm -rf $ROOTDIR/log/[^.]*");
		$qclean=1;
	    }
	}
	if($options{runs} or $options{all}){
	    rprint "Cleaning runs directory...";
	    $ans=promptAns("Do you want to proceed?(y/n)",$ans) if($ans!~/a/i);
	    if($ans=~/[ya]/i){
		sysCmd("rm -rf $ROOTDIR/runs/[^db.]*");
		#&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
		#CLEANING RUNS TABLE
		#&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
		print "Removing runs entries from database...\n";
		sysCmd("echo 'truncate table runs;' > /tmp/db.$$");
		`mysql -u $DBUSER --password=$DBPASS $DBNAME < /tmp/db.$$`;
		die("Failed authentication") if($?);
		$qclean=1;
	    }
	}
	if($options{results} or $options{all}){
	    rprint "Cleaning results directory...";
	    $ans=promptAns("Do you want to proceed?(y/n)",$ans) if($ans!~/a/i);
	    if($ans=~/[ya]/i){
		sysCmd("rm -rf $ROOTDIR/runs/db/[^.]*");
		#&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
		#CLEANING APPLICATION TABLES
		#&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
		$apps=sysCmd("ls $ROOTDIR/apps | grep -v template | grep -v licenses | grep -v bench");
		foreach $app (split /\s+/,$apps){
		    $vers=sysCmd("ls -d $ROOTDIR/apps/$app/*/sci2web");
		    foreach $verdir (split /\s+/,$vers){
			$ver=sysCmd("basename \$(dirname $verdir)");
			$tbname="${app}_${ver}";
			print "Removing entries in table $tbname...\n";
			sysCmd("echo 'truncate table `$tbname`;' > /tmp/db.$$");
			`mysql -u $DBUSER --password=$DBPASS $DBNAME < /tmp/db.$$`;
			die("Failed authentication") if($?);
		    }
		}
		$qclean=1;
	    }
	}
	if($options{db} or $options{all}){
	    rprint "Resetting databases...";
	    rprint "NOTE: If you have created applications or versions you should remove it from the apps dir";
	    $ans=promptAns("Do you want to proceed?(y/n)",$ans) if($ans!~/a/i);
	    if($ans=~/[ya]/i){
		print "Provide the MySQL root password:\n\t";
		sysCmd("cat $ROOTDIR/doc/install/sci2web.sql $ROOTDIR/apps/MercuPy/2B-dev/sci2web/controlvars.sql $ROOTDIR/apps/MercuPy/3B-dev/sci2web/controlvars.sql > /tmp/db.$$");
		`mysql -u root -p < /tmp/db.$$`;
		die("Failed authentication") if($?);
		$qclean=1;
	    }
	}
	if($qclean){
	    rprint "Site cleaned","=";
	}
	else{
	    rprint "No clean action performed.";
	}
    }
    #======================================================================
    #INITIALIZE APPLICATION DIRECTORY
    #======================================================================
    case "init" {
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CHECK APPLICATION NAME
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$appname=$options{appname};
	Error "An application name should be provided." if($appname!~/\w/);
	$vername=$options{vername};
	Error "A version name should be provided." if($vername!~/\w/);

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CHECK ADDITIONAL INFORMATION
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$emails=$options{emails};
	Error "The contributors emails (separated by ';') should be provided"
	    if($emails!~/\w/);
	$changeslog=$options{changeslog};
	Error "A changeslog summary should be provided"
	    if($changeslog!~/\w/);

	rprint "Initializing directory as application '$appname' and version '$vername'","=";
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#COPYING SCI2WEB CONFIGURATION DIRECTORY
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	if(!-d "sci2web"){
	    rprint "Copying sci2web templates...";
	    sysCmd("cp -rf $APPSDIR/template/sci2web .");
	    sysCmd("echo '<p>$changeslog</p>' >> sci2web/changeslog.html");
	    sysCmd("ln -s $BINDIR sci2web/bin");
	}else{
	    rprint "Directory already initialized...";
	}

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CREATE THE BASIC CONFIGURATION FILE
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Initializing the version configuration file...";
	open(fl,">sci2web/version.conf");

$config=<<CONFIG;
############################################################
#VERSION CONFIGURATION FILE
############################################################
#------------------------------------------------------------
#GENERAL INFORMATION ABOUT APPLICATION
#------------------------------------------------------------
Application = $appname
AppCompleteName = Complete name of the application
AppBrief = Brief description of the application

#------------------------------------------------------------
#VERSION PROPERTIES
#------------------------------------------------------------
#Name of this version.  Do not modify in installed applications.
Version = $vername
#Emails of the contributors. User with permissions to edit app.
EmailsContributors = $emails
#Brief description of the changes log
ChangesLog = $changeslog
#Could this application be released
Release = true
#Under which license (see apps/licenses directory or add a license there)
License = GPLv3
#Information pages displayed in tabs of the version page
#Valid pages:description,documentation,downloads,runs,database.
VerTabs = description:Description;documentation:Documentation;downloads:Downloads;runs:Runs;database:Database
#Queue mode: QueueList,RunsHistory
QueueMode = QueueList
#Do you want to store your results in a new database?
ResultsDatabase = true
#Actions not valid for this version of your application
#Valid actions:Clean,Compile,Run,Pause,Stop,Kill,Resume
InvalidActions=

#------------------------------------------------------------
#CONFIGURATION WINDOW PROPERTIES
#------------------------------------------------------------
#Do you want to include a tab with a list of run files
FilesTab = false
#Do you want to include control panel in the conf. window?
ControlButtons = true

#------------------------------------------------------------
#DEFAULT RUN PROPERTIES
#------------------------------------------------------------
#Default run name
DefaultRunName = New Run

CONFIG
	print fl $config;
	close(fl);

	rprint "Directory initialized.","=";
    }
    #======================================================================
    #GENERATE CONTROL VARIABLES CONFIGURATION FILES
    #======================================================================
    case "contvars"
    {
	%VARS=();

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CHECK BASE DIRECTORY 
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$basedir=$options{appdir};
	Error "Application directory '$basedir' does not exist." if(!-d $basedir);
	Error "Application in directory '$basedir' has not been initialized." 
	    if(!-d "$basedir/sci2web");

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#GETTING INFORMATION FROM VERSION CONF.FILE
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	%CONFIG=readConfig("$basedir/sci2web/version.conf");
	$appname=$CONFIG{"Application"};
	$vername=$CONFIG{"Version"};

	rprint "Gathering control variables in directory '$basedir' for application '$appname' version '$vername'","=";

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#SEARCH FOR TEMPLATE FILES
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$list=`find $basedir -name ".\*.temp" | grep -v ".variables.info" | grep -v ".results.info" 2> /dev/null`;chop $list;
	@files=split /\s+/,$list;
	unshift(@files,("sci2web/.variables.info.temp",
			"sci2web/.results.info.temp"));
	rprint "@files";
	if($#files<0){
	    print "There is no file to change in directory '$basedir'.\n";
	    exit(0);
	}
	
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#EXTRACT VARIABLES FROM TEMPLATES
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$j=0;
	foreach $file (@files)
	{
	    print "FILE: $file\n";
	    $fname=`basename $file`;
	    $fname=~/^\.(.+)\.temp/;
	    $fname=$1;
	    open(fl,"<$file");
	    @lines=<fl>;chomp @lines;
	    foreach $line (@lines){
		vprint "LINE: $line\n";
		next if($line=~/^#/);
		@parts=split /\[/,$line;
		foreach $part (@parts){
		    if($part=~/^([^\]]+)\]\]/gi){
			next if($1=~/^[\[\(\d]/);
			@comps=split /::/,$1;
			$i=0;
			#DETERMINE IF THIS IS A NEW VARIABLE
			$key=searchHashValues($comps[0],%VARS);
			vprint "FOUND: $key\n";
			if($key=~/^(\d+)/){
			    $nvar=$1;
			}
			else{
			    $nvar=$j;
			    print "Variable $nvar: $comps[0]\n";
			    $j++;
			}
			vprint "Variable $nvar:\n";
			foreach $compvar (@VARSCOMP){
			    if($VARS{$nvar,"$compvar"}!~/\w/){
				$comps[$i]="All" if($compvar eq "vartab" and $comps[$i]!~/\w/);
				$comps[$i]="General" if($compvar eq "vargroup" and $comps[$i]!~/\w/);
				$VARS{$nvar,"$compvar"}=$comps[$i];
			    }
			    $i++;
			}
			$VARS{$nvar,"files"}.="$fname,";
			foreach $compvar (@VARSCOMP){
			    vprint "\t$compvar: ".$VARS{$nvar,"$compvar"}."\n";
			}
		    }
		}
	    }
	    close(fl);
	}
	$numvars=$j;
	print "Numvars: $numvars\n";

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CLASSIFICATION BY TABS
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	@vts=();
	for($j=0;$j<$numvars;$j++){
	    vprint "Var $j ".$VARS{$j,"var"}.":";
	    $vartab=$VARS{$j,"vartab"};
	    #$vartab="All" if($vartab!~/\w/);
	    $vargroup=$VARS{$j,"vargroup"};
	    #$vargroup="General" if($vargroup!~/\w/);
	    vprint "$vartab,$vargroup\n";
	    $vtgroups="${vartab}_gs";
	    $vtivars="${vartab}_${vargroup}_ivar";
	    push(@{$vtgroups},$vargroup);
	    push(@vts,$vartab);
	    push(@{$vtivars},$j);
	}
	vprint "VARTABS NOT UNIQUE: @vts\n";
	@vartabs=unique(@vts);
	vprint "VARTABS UNIQUE: @vartabs\n";
	foreach $vartab (@vartabs){
	    vprint "Variable tab: $vartab\n";
	    $vtgs="${vartab}_gs";
	    $vtgroups="${vartab}_groups";
	    @{$vtgroups}=unique(@{$vtgs});
	    vprint "\tGroups:".join(",",@{$vtgroups})."\n";
	}

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CHECK UNCHANGING THE VARIABLES
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	open(fl,">/tmp/vars$$");
	foreach $vartab (@vartabs){
	    $vtgroups="${vartab}_groups";
	    foreach $vargroup (@{$vtgroups}){
		$vtivars="${vartab}_${vargroup}_ivar";
		foreach $j (@{$vtivars}){
		    print fl $VARS{$j,"var"}."\n";
		}
	    }
	}
	close(fl);
	$newhashvars=sysCmd("md5sum /tmp/vars$$ | awk '{print \$1}'");
	`rm -rf /tmp/vars$$`;
	if(-e "$basedir/sci2web/.hashvars"){
	    $hashvars=sysCmd("cat $basedir/sci2web/.hashvars");
	}else{
	    $hashvars=$newhashvars;
	    sysCmd("echo $newhashvars > $basedir/sci2web/.hashvars");
	}
	if(!($newhashvars eq $hashvars)){
	    print "A new variable has been detected in the project.
This will affect all the instances created with this project.  
Consider to create a new version of the application.
";
	    exitFunc(1);
	}

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CREATE THE CONTROL VARIABLES CONF.FILE
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	open(fa,">$basedir/sci2web/templates/Default.conf");
	open(fv,">$basedir/sci2web/controlvars.info");
	$b1=bar("#",70);
	$b2=bar("%",60);
	$b3=bar("/",50);
	print "Variables detected:\n";
	print fa "#T:Default template\n";
	print fa "#$b1\n#DEFAULT CONFIGURATION FILE\n#$b1\n";
	foreach $vartab (@vartabs){
	    print "\tTab: $vartab\n";
	    print fa "#$b2\n#TAB $vartab\n#$b2\n" if($vartab ne "Results");
	    print fv "#$b1\n#TAB:$vartab\n#$b1\n";
	    $vtgroups="${vartab}_groups";
	    foreach $vargroup (@{$vtgroups}){
		print "\t\tGroup: $vargroup\n";
		print fa "#$b3\n#GROUP $vargroup\n#$b3\n" if($vartab ne "Results");
		print fv "#$b2\n#GROUP:$vargroup\n#$b2\n";
		$vtivars="${vartab}_${vargroup}_ivar";
		foreach $j (@{$vtivars}){
		    foreach $compvar (@VARSCOMP){
			$$compvar=$VARS{$j,"$compvar"};
			if(!($compvar eq "vartab") and
			   !($compvar eq "vargroup")){
			    print fv $$compvar."::";
			}
		    }
		    print fv $VARS{$j,"files"};
		    $defval=~s/\"//gi;
		    vprint "Defval: $defval\n";
		    if($defval=~/==([^:]+)/){
			$defval=$1;
			vprint "\tNew defval: $defval\n";
		    }
		    print fa "#Variable $varname\n$var = $defval\n" if($vartab ne "Results");
		    print fv "\n";
		    print "\t\t\tVariable: $var ($varname,$datatype)\n";
		}
	    }
	}
	close(fa);
	close(fv);

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#COPY DEFAULT CONF FILE TO RUNS DIRS
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	sysCmd("find $RUNSDIR -name $appname -exec cp -rf $basedir/sci2web/templates/Default.conf {}/$vername/templates \\;");
	
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CREATE THE SQL DATABASE TABLE SCRIPT
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Creating SQL script file...";
	$sql="drop table if exists `${appname}_${vername}`;\n";
$sql.=<<SQL;
create table `${appname}_${vername}` (
dbrunhash char(32) not null,
dbauthor varchar(255),
dbdate datetime not null,
SQL
        foreach $vartab (@vartabs){
	    rprint "\tTab $vartab:";
	    $vtgroups="${vartab}_groups";
	    foreach $vargroup (@{$vtgroups}){
		rprint "\t\tGroup $vargroup:";
		$vtivars="${vartab}_${vargroup}_ivar";
		foreach $j (@{$vtivars}){
		    $varname=$VARS{$j,"var"};
		    $vartype=$VARS{$j,"datatype"};
		    $vartype=~s/varchar$/varchar\(255\)/;
		    $vartype=~s/boolean/tinyint\(1\)/;
		    $vartype=~s/file/varchar\(255\)/;
		    rprint "\t\t\tInserting variable $varname of type $vartype";
		    $sql.="$varname $vartype,\n";
		}
	    }
        }
$sql.=<<SQL;
primary key (dbrunhash),
runs_runcode char(8)
);
SQL
        open(fl,">$basedir/sci2web/controlvars.sql");
	print fl "$sql";
	close(fl);

	rprint "Control variables information has been succesfully gathered.","=";
    }

    #======================================================================
    #GENERATE RUN FILES FROM TEMPLATES
    #======================================================================
    case "genfiles" {
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CHECK RUN CONFIGURATION FILE
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$runconf=$options{runconf};
	Error "Run configuration file '$runconf' does not exist."
	    if(!-e $runconf);

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CHECK RUN DIRECTORY
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$rundir=$options{rundir};
	Error "Application directory '$rundir' does not exist." if(!-d $rundir);
	Error "Application in directory '$rundir' has not been initialized." 
	    if(!-d "$rundir/sci2web");
	
	rprint "Generating run files from templates using configuration in '$runconf' at run directory '$rundir'","=";

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#READ CONFIGURATION FILES
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	%CONFIG=readConfig("$runconf");
	@vars=keys(%CONFIG);
	@vals=values(%CONFIG);
	$numvars=$#vars+1;

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#SEARCHING TEMPLATE FILES
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$list=`find $rundir -name ".\*.temp" 2> /dev/null`;chop $list;
	@files=split /\s+/,$list;
	Error "There is no template files in directory '$rundir'."
	    if($#files<0);

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#REPLACING VARIABLES
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	@lines=();
	foreach $file (@files){
	    print "FILE: $file\n";
	    @lines=readLines($file);
	    for($i=0;$i<$numvars;$i++){
		$var=$vars[$i];
		$val=$vals[$i];
		for($j=0;$j<=$#lines;$j++){
		    vprint "\tLine: ".$lines[$j]."\n";
		    vprint "\t\tTesting line against -$var-\n";
		    if($lines[$j]=~s/\[\[$var\]\]/$val/gi){
			vprint "\t\t\tNEW LINE: $lines[$j]\n";
		    }
		}
	    }
	    $file=~/(.+)\.temp/;
	    $newfile="$1";
	    $newfile=~s/\/\./\//gi;
	    print "\tNEWFILE:$newfile\n";
	    open(fl,">$newfile");
	    print fl join "\n",@lines;
	    print fl "\n";
	    close(fl);
	}
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CREATING LOCAL CONFIGURATION FILE
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	sysCmd("cp -rf $runconf $rundir/run.conf");

	rprint "Run files generated.","=";
    }
    #======================================================================
    #RELEASE A NEW APPLICATION
    #======================================================================
    case "release" {
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CHECK OPTIONS
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$appname=noBlank($options{appname},"Application name");
	$appdir="$APPSDIR/$appname";
	Error "Application '$appname' not valid"
	    if(!-d "$appdir");
	$vername=noBlank($options{vername},"Version name");
	$verdir="$appdir/$vername";
	Error "Template version '$vername' is not installed"
	    if(!-d "$verdir");

	$appver="${appname}_${vername}";
	rprint "Realeasing $appver","=";
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#COPYING VERSION DIR
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Copying version directory...";
	$tgtdir="$TMPDIR/$appver";
	sysCmd("rm -rf $tgtdir") if(-d $tgtdir);
	sysCmd("cp -rf $verdir $tgtdir");
	if(!$options{sci2web}){
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    #GENERATING FILES FROM TEMPLATES
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    $out=sysCmd("cd $tgtdir;perl $BINDIR/sci2web.pl genfiles --runconf sci2web/templates/Default.conf --rundir .");
	    rprint $out;
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    #REMOVING EXTRA FILES
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    rprint "Removing extra files...";
	    sysCmd("cd $tgtdir;cp -rf sci2web/version.conf .");
	    sysCmd("cd $tgtdir;rm -rf sci2web .*.temp run.conf");
	}
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CREATING TARBALL OF APPLICATION
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Creating the tarball...";
	$tarball="$appver.tar.gz";
	if($options{sci2web}){
	    $tarball="${appver}_sci2web.tar.gz";
	}
	sysCmd("basepwd=\$(pwd);cd $tgtdir/..;tar zcvf $verdir/sci2web/$tarball --exclude=.gitignore --exclude=sci2web/bin $appver"); 

	rprint "Tarball '$verdir/sci2web/$tarball' created","=";
    }
    #======================================================================
    #CREAT A NEW VERSION FROM AN EXISTING ONE
    #======================================================================
    case "newversion" {
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CHECK OPTIONS
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$appname=noBlank($options{appname},"Application name");
	$appdir="$APPSDIR/$appname";
	Error "Application '$appname' not valid"
	    if(!-d "$appdir");
	$tempver=noBlank($options{tempver},"Template version");
	$tempdir="$appdir/$tempver";
	Error "Template version '$tempver' is not installed"
	    if(!-d "$tempdir");
	$newver=noBlank($options{newver},"New version");
	$newdir="$appdir/$newver";
	Error "New version '$newver' already exists"
	    if(-d "$newdir");
	if($options{emails}){
	    $emails=$options{emails};
	    Error "No valid contributors emails has been provided..."
		if($emails!~/@/);
	}

	rprint "Installing a new version from template","=";
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#TEMPORAL COPY OF TEMPLATE VERSION
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Temporal copy of template version...";
	$tgtdir="$TMPDIR/Versions_$appname";
	sysCmd("mkdir -p $tgtdir") if(!-d $tgtdir);
	sysCmd("cp -rf $APPSDIR/$appname/$tempver $tgtdir");
	rprint "Removing any release...";
	sysCmd("rm -rf $tgtdir/$vername/$tempver/sci2web/*.tar.gz");
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#RECONFIGURING NEW VERSION
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Reconfiguring new version...";
	rprint "Changing version name from '$tempver' to '$newver'...";
	sysCmd("sed -i.save -e 's/\\s*=\\s*$tempver\$/ = $newver/gi' $tgtdir/$tempver/sci2web/version.conf");
	if($options{emails}){
	    rprint "Changing version contributor...";
	    sysCmd("sed -i.save -e 's/EmailsContributors\\s*=\\s*.*/EmailsContributors = $emails/gi' $tgtdir/$tempver/sci2web/version.conf");
	}
	rprint "Regenerating variables...";
	sysCmd("cd $tgtdir/$tempver;perl $BINDIR/sci2web.pl contvars --appdir .");
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#INSTALLING NEW VERSION
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Installing new version...";
	$out=sysCmd("basepwd=\$(pwd);cd $tgtdir/$tempver;perl \$basepwd/$BASEDIR/sci2web.pl install --appdir .");
	rprint $out;

	rprint "New version installed","=";
    }
    #======================================================================
    #INSTALL APPLICATION
    #======================================================================
    case "install" {
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CHECK OPTIONS
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$appdir=noBlank($options{appdir},"Application directory");
	Error "Application directory '$appdir' not valid"
	    if(!-d "$appdir/sci2web");

	Error "Please call the script using the absolute path"
	    if(!-e "$ROOTDIR/lib/sci2web.conf");

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#READ VERSION CONFIGURATION
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	%CONFIG=readConfig("$appdir/sci2web/version.conf");
	$appname=$CONFIG{Application};
	$appver=$CONFIG{Version};

	rprint "Installing application '$appname', version '$appver'","=";
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CHECK IF APPLICATION ALREADY EXIST
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$appdir="$APPSDIR/$appname";
	if(!-d $appdir){
	    rprint "Application does not exist.  Creating a new one...";
	    sysCmd("mkdir $appdir");
	    #========================================
	    #COPY TEMPLATE FILES
            #========================================
	    $tempdir="$APPSDIR/template";
	    $files=sysCmd("cd $tempdir;find . -type f -o -type l | grep -v sci2web | grep -v bench");
	    foreach $file (split /\s+/,$files){
		$tgtfile=$file;
		$tgtfile=~s/application-/$appname-/gi;
		$dir=sysCmd("dirname $tgtfile");
		$fname=sysCmd("basename $tgtfile");

		print "\tCopying file $fname to $dir...\n";

		$tgtdir="$appdir/$dir";
		if(!-d $tgtdir){
		    print "\t\tCreating new directory $tgtdir...\n";
		    sysCmd("mkdir $tgtdir");
		}else{
		    print "\t\tSkipping directory $tgtdir...\n";
		}

		$tgtfile="$tgtdir/$fname";
		if(!-e $tgtfile){
		    print "\t\tCopying file $fname...\n";
		    sysCmd("cp -rf $tempdir/$file $tgtfile");
		}else{
		    print "\t\tSkipping file $tgtfile...\n";
		}
	    }
	    #============================================
	    #SETTING USER PERMISSIONS FOR EDITABLE FILES
            #============================================
	    sysCmd("find $appdir -name '*.html' -exec chown $APACHEUSER.$APACHEGROUP {} \\;");
	    #========================================
	    #SAVE CONFIGURATION
            #========================================
	    sysCmd("grep -v '^#' sci2web/version.conf | head -n 3 > $appdir/app.conf");
	    #========================================
	    #SAVE DATABASE
            #========================================
	    $appauthor=$CONFIG{EmailsContributors};
	    $sql="
replace into apps
set 
app_code='$appname',
creation_date=date(now()),
users_emails_author='$appauthor;'
";
	    rprint "Inserting/Replacing application into database...";
	    $query=$DB->prepare($sql);
	    $query->execute() or Error "Error creating application in database";
	}else{
	    rprint "Application '$appname' already exists...";
	}
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CHECK IF LICENSE FILE EXISTS
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$licfile="$APPSDIR/licenses/LICENSE.$CONFIG{License}";
	Error "License file '$licfile' does not exist.  Check your configuration file."
	    if(!-e $licfile);
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CHECK IF VERSION ALREADY EXIST
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$verdir="$APPSDIR/$appname/$appver";
	if(!-d $verdir){
	    rprint "Version does not exist. Creating a new one.";
	    sysCmd("mkdir -p $verdir");
	    #========================================
	    #SAVE VERSION DATABASE
            #========================================
	    $verdbname="${appname}_${appver}";
	    $vercont=$CONFIG{EmailsContributors};
	    $vercode="${appver}";
	    $verchlog=$CONFIG{ChangesLog};
	    $appbrief=$CONFIG{AppBrief};
	    $sql="
replace into versions
set 
release_date=date(now()),
version_code='$vercode',
users_emails_contributor='$vercont;',
apps_code='$appname'
";
	    rprint "Inserting/Replacing version into database...";
	    $query=$DB->prepare($sql);
	    $query->execute() or Error "Error creating application in database";
	    #========================================
	    #UPDATING APPLICATION DATABASE
            #========================================
	    @results=mysqlCmd("select versions_codes from apps where app_code='$appname'");
	    $vercodes=$results[0];
	    if($vercodes=~/$appver;/){
		rprint "Version already registered in application table...";
	    }else{
		$sql="update apps set versions_codes='$vercodes$appver;' where app_code='$appname'";
		$query=$DB->prepare($sql);
		$query->execute();
	    }
	    #========================================
	    #CREATING RESULT DATABASE
            #========================================
# $sql=<<SQL;
# drop table if exists `$verdbname`;
# create table `$verdbname` (
# dbrunhash char(32) not null,
# dbauthor varchar(255),
# dbdate datetime not null,
# SQL
# 	    open(fl,"<sci2web/controlvars.info");
# 	    @lines=<fl>;chomp @lines;
# 	    close(fl);
# 	    for $line (@lines){
# 		next if($line=~/^#/ or $line!~/\w/);
# 		@parts=split /::/,$line;
# 		$vartype=$parts[2];
# 		$vartype=~s/varchar$/varchar\(255\)/;
# 		$vartype=~s/boolean/tinyint\(1\)/;
# 		$vartype=~s/file/varchar\(255\)/;
# 		$sql.="$parts[0] $vartype,\n";
# 	    }
# $sql.=<<SQL;
# primary key (dbrunhash),
# runs_runcode char(8));
# SQL
# 	    open(fl,">$TMPDIR/sql.$$");
# 	    print fl $sql;
# 	    close(fl);
# 	    `mysql -u $DBUSER --password=$DBPASS $DBNAME < $TMPDIR/sql.$$`;
	    `mysql -u $DBUSER --password=$DBPASS $DBNAME < sci2web/controlvars.sql`;
	    #========================================
	    #COPY FILES INTO VERSION DIR
            #========================================
	    rprint "Copying files into version dir...";
	    sysCmd("tar cf - * .[a-zA-Z]* | tar xf - -C $verdir");
	    sysCmd("cp -rf $licfile $verdir/LICENSE");
	    #========================================
	    #RELEASE TARBALLS
            #========================================
	    if($CONFIG{Release}=~/true/){
		rprint "Releasing tarballs for version...";
		$out=sysCmd("perl $BINDIR/sci2web.pl release --appname $appname --vername $appver");
		sysCmd("perl $BINDIR/sci2web.pl release --appname $appname --vername $appver --sci2web");
	    }

	}else{
	    Error "Version '$appver' already exists...";
	}
	
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#FINALIZE
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Application/Version installed","=";
    }

    #======================================================================
    #REMOVE APPLICATION
    #======================================================================
    case "remove" {
	rprint "Removing application components","=";
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CHECK INFORMATION
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$appname=$options{appname};
	Error "An application name should be provided." if($appname!~/\w/);
	$appdir="$APPSDIR/$appname";
	Error "Application '$appname' does not exist" if(!-d "$appdir");
	@versions=();
	if(!$options{vername}){
	    rprint "Removing application '$appname'...";
	    @result=mysqlCmd("select versions_codes from apps where app_code='$appname'");
	    $vercodes=$result[0];
	    foreach $vercode (split /;/,$vercodes){
		next if($vercode!~/[\w\d]/);
		push(@versions,"$vercode");
	    }
	}else{
	    $vername=$options{vername};
	    Error "A version name should be provided." 
		if($vername!~/\w/);
	    @versions=($vername);
	}
	foreach $vername (@versions){
	    $verdbname="${appname}_${vername}";
	    rprint "Removing version '$appname'/'$vername'";
	    $verdir="$APPSDIR/$appname/$vername";
	    Error "Application version '$vername' does not exist" 
		if(!-d "$verdir");
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    #MOVING VERSION TO TRASH
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    rprint "Moving files to trash...";
	    sysCmd("rm -rf $TRASHDIR/$verdbname") if(-d "$TRASHDIR/$verdbname");
	    sysCmd("mv $verdir $TRASHDIR/$verdbname");
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    #REMOVING ENTRY FROM VERSION DATABASE
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    rprint "Removing version from database...";
	    $sql="delete from versions where version_code='$vername' and apps_code='$appname'";
	    $query=$DB->prepare($sql);
	    $query->execute();
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    #REMOVING VERSION FROM APP DATABASE
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    rprint "Removing version code from app database entry...";
	    @result=mysqlCmd("select versions_codes from apps where app_code='$appname'");
	    $vercodes=$result[0];
	    foreach $vercode (split /;/,$vercodes){
		next if($vercode!~/[\w\d]/);
		next if($vername eq $vercode);
		$newvercodes.="$vercode;";
	    }
	    if($newvercodes!~/[\w\d]/){
		rprint "WARNING: $appname does not have any version";
	    }
	    $sql="update apps set versions_codes='$newvercodes' where app_code='$appname'";
	    $query=$DB->prepare($sql);
	    $query->execute();
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    #REMOVE DATABASE OF RESULTS
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    rprint "Removing database of results '$verdbname'...";
	    mysqlDo("drop table if exists `$verdbname`");
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    #REMOVE RUNS AND STORED RESULTS
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    mysqlDo("delete from runs where apps_code='$appname' and versions_code='$vername'");
	    sysCmd("rm -rf $RUNSDIR/*/$appname/$vername");
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    #FINALIZE
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    rprint "Version '$appname'/'$vername' removed succesfully";
	}
	if(!$options{vername}){
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    #MOVING VERSION TO TRASH
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    rprint "Moving remaining application files to trash...";
	    sysCmd("rm -rf $TRASHDIR/$appname") if(-d "$TRASHDIR/$appname");
	    sysCmd("mv $appdir $TRASHDIR");
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    #REMOVE APPLICATION DATABASE ENTRY
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    rprint "Removing application from database...";
	    mysqlDo("delete from apps where app_code='$appname'");
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    #REMOVE RUNS AND STORED RESULTS
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    sysCmd("rm -rf $RUNSDIR/*/$appname");
	}
	rprint "Application components removed successfully","=";
    }
    #======================================================================
    #REMOVE APPLICATION
    #======================================================================
    case "genrun" {
	rprint "Generating a new run instance","=";

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CHECK INPUT
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$root=$ROOTDIR if($options{local});
	rprint "Root directory $root...";

	$appdir=noBlank($options{appdir},"Appdir");
	Error "Application directory not valid" 
	    if(!-d "$root/$appdir/sci2web");
	
	# if($options{template}){
	#     $template=$options{template};
	#     Error "Template '$template' not found" 
	# 	if(!-e "$root/$appdir/sci2web/templates/$template.conf");
	# }else{
	#     $template=noBlank($options{template-file},"Template file");
	#     Error "Template file '$template' not found" 
	# 	if(!-e $template);
	# }

	$rundir=noBlank($options{rundir},"Run directory");
	Error "Directory '$rundir' already exist" 
	    if(-d "$root/$rundir");

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CREATE RUN DIRECTORY
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Creating Run Directory...";
	sysCmd("mkdir -p $root/$rundir");
	sysCmd("echo -e '*\n^sci2web' > $root/$rundir/.s2wfiles");

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#COPY APPLICATION FILES
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Copying files...";
	$links=sysCmd("cd $root/$appdir;ls -md \$(cat sci2web/sharedfiles.info)");
	rprint "LINKS:\n$links\n";
	@list_links=split /\s*,\s*/,$links;
	$exclude.=" --exclude='$_' " foreach ("sci2web",".gitignore");
	$exclude.=" --exclude='$_' " foreach (@list_links);
	$out=sysCmd("((cd $root/$appdir;tar cvf - $exclude * .[a-zA-Z]*)| tar xf - -C $root/$rundir) &> /tmp/src.$$");
	$files=sysCmd("cat /tmp/src.$$");

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
        #CREATE SYMBOLIC LINKS
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Linking files...";
	for $link (@list_links){
	    sysCmd("ln -s $root/$appdir/$link $root/$rundir/$link");
	}

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#COPY SCI2WEB FILES
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Creating and populating sci2web directory...";
	$out=sysCmd("mkdir -p $rundir/sci2web");
	$out=sysCmd("cd $root/$appdir/sci2web;cp -rf *.info *.conf .*.temp $root/$rundir/sci2web");
	$out=sysCmd("ln -s $ROOTDIR/bin $rundir/sci2web/bin");

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#SAVING LIST OF FILES
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Saving list of files...";
	open(fl,">$root/$rundir/sci2web/srcfiles.info");
	for $file (split /\n/,$files){
	    next if($file=~/^\..+\.temp$/ or $file!~/\w/);
	    print fl "$file\n";
	}
	close(fl);
	
	rprint "Run instance created","=";
    }
    #======================================================================
    #CHANGE STATUS OF THE APPLICATION
    #======================================================================
    case "changestatus" {
	rprint "Changing run status","=";

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CHECK INPUT
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$rundir=noBlank($options{rundir},"Rundir");
	Error "Run directory not valid" 
	    if(!-d "$rundir/sci2web");
	$status=noBlank($options{status},"Status");

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#READ RUN PROPERTIES
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Reading configuration files...";
	%conf_run=readConfig("$rundir/run.info");
	$runcode=$conf_run{"run_code"};
	$tbname=$conf_run{"apps_code"}."_".$conf_run{"versions_code"};

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CHECK IF APPLICATION EXISTS
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	sysCmd("echo 'describe `$tbname`;' > /tmp/db.$$");
	`mysql -u $DBUSER --password=$DBPASS $DBNAME < /tmp/db.$$ &> /dev/null`;
	if(!$?){
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    #CHANGE STATUS AT THE DATABASE LEVEL
	    #%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	    rprint "Changing status at the database...";
	    $sql="update runs set run_status='$status' where run_code='$runcode'";
	    mysqlDo($sql);
	    rprint "Status changed","=";
	}else{
	    rprint "Application does not exist at database";
	}
    }
    #======================================================================
    #SAVE RESULTS OF APPLICATION
    #======================================================================
    case "saveresult" {
	rprint "Save result","=";
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CHECK INPUT
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$rundir=noBlank($options{rundir},"Rundir");
	Error "Run directory not valid" 
	    if(!-d "$rundir/sci2web");

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#READ RUN PROPERTIES
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Reading configuration files...";
	%conf_app=readConfig("$rundir/sci2web/version.conf");
	$appname=$conf_app{"Application"};
	$vername=$conf_app{"Version"};
	%conf_conf=readConfig("$rundir/run.conf");
	%conf_run=readConfig("$rundir/run.info");
	%conf_results=readConfig("$rundir/sci2web/results.info");
	$tbname=$conf_run{"run_app"}."_".$conf_run{"run_version"};
	$runcode=$conf_run{"run_code"};
	$author=$conf_run{"run_author"};
	$runhash=sysCmd("md5sum $rundir/run.info | cut -f 1 -d ' '");
	$rundate=sysCmd("date +'%Y-%m-%d %H:%M:%S'");

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#SAVE RESULTS IN DATABASE
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$sql=<<SQL;
replace into `$tbname` set
dbrunhash='$runhash',
runs_runcode='$runcode',
dbauthor='$author',
dbdate='$rundate',
SQL

        for $field (keys(%conf_conf)){
	    next if($conf_conf{"$field"}!~/\w/);
	    $sql.="$field=\"".$conf_conf{"$field"}."\",\n";
        }
        for $field (keys(%conf_results)){
	    next if($conf_results{"$field"}!~/\w/);
	    $sql.="$field=\"".$conf_results{"$field"}."\",\n";
        }
	$sql=~s/,$//;
	rprint "Storing result in database...";
	mysqlDo($sql);

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#SAVING RESULTS FILES
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Storing results files...";
	$dbdir="$RUNSDIR/db/$appname/$vername";
	if(-d $dbdir){
	    sysCmd("mkdir -p $dbdir/$runhash");
	    sysCmd("echo -e '*' > $dbdir/$runhash/.s2wfiles");
	    sysCmd("cd $rundir;cp -rf *.conf *.info *.oxt $dbdir/$runhash");
	    sysCmd("cd $rundir;cp -rf \$(cat sci2web/outfiles.info) $dbdir/$runhash");
	    sysCmd("cd $dbdir;tar zcf $runhash.tar.gz $runhash");
	    sysCmd("rm -rf $dbdir/$runhash");
	}else{
	    rprint "Database directory $dbdir not found.";
	}

	rprint "Result $runhash saved","=";
    }
    #======================================================================
    #DOWNLOAD COMPONENT OF RUN
    #======================================================================
    case "down" {
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CHECK OPTIONS
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$rundir=noBlank($options{rundir},"Run Directory");
	Error "Run directory not valid" 
	    if(!-d "$rundir/sci2web");
	%conf_app=readConfig("$rundir/sci2web/version.conf");
	%conf_run=readConfig("$rundir/run.info");
	$runcode=$conf_run{"run_code"};

	if($options{results}){
	    rprint "Downloading results","=";
	    $tgt="$conf_app{Application}_$conf_app{Version}-results-run_$runcode";
	    sysCmd("rm -rf $TMPDIR/$tgt") if(-d "$TMPDIR/$tgt");
	    rprint "Creating download directory '$TMPDIR/$tgt'...";
	    sysCmd("mkdir -p $TMPDIR/$tgt");
	    rprint "Creating permissions file...";
	    sysCmd("echo -e '*' > $TMPDIR/$tgt/.s2wfiles");
	    rprint "Copying results files into download directory...";
	    sysCmd("cd $rundir;cp -rf *.conf *.info *.oxt $TMPDIR/$tgt");
	    sysCmd("cd $rundir;cp -rf \$(cat sci2web/outfiles.info) $TMPDIR/$tgt");
	}
	if($options{sources}){
	    rprint "Downloading sources","=";
	    rprint "Downloading results","=";
	    $tgt="$conf_app{Application}_$conf_app{Version}-sources-run_$runcode";
	    sysCmd("rm -rf $TMPDIR/$tgt") if(-d "$TMPDIR/$tgt");
	    rprint "Creating download directory '$TMPDIR/$tgt'...";
	    sysCmd("mkdir -p $TMPDIR/$tgt");
	    rprint "Creating permissions file...";
	    sysCmd("echo -e '*' > $TMPDIR/$tgt/.s2wfiles");
	    rprint "Copying sources files into download directory...";
	    sysCmd("cd $rundir;tar chf - * .[a-zA-Z]* | tar xf - -C $TMPDIR/$tgt");
	    rprint "Removing extra files...";
	    sysCmd("cd $TMPDIR/$tgt;make cleanall");
	    sysCmd("cd $TMPDIR/$tgt;rm -rf sci2web *.oxt *.sig .*.temp");
	}
	rprint "Packing results...";
	sysCmd("cd $TMPDIR;tar zcf $tgt.tar.gz $tgt");
	sysCmd("rm -rf $TMPDIR/$tgt");
	rprint "Download complete","=";
    }
    #======================================================================
    #DOWNLOAD COMPONENT OF RUN
    #======================================================================
    case "showdb" {
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CHECK OPTIONS
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Database information","=";
	if($options{describe}){
	    $table=noBlank($options{table},"Table");
	    rprint "Describing table '$table'","-";
	    $resultscsv=mysqlCSV("describe `$table`");
	}elsif($options{entries}){
	    $table=noBlank($options{table},"Table");
	    rprint "Entries in table '$table'","-";
	    $resultscsv=mysqlCSV("select * from `$table`");
	}elsif($options{show}){
	    rprint "Show tables","-";
	    $resultscsv=mysqlCSV("show tables");
	}
	formatSqlOut($resultscsv);
	rprint "Done","=";
    }
}

################################################################################
#FINALIZE
################################################################################
Exit 0;

################################################################################
#PLAIN OLD DOCUMENTATION (POD) USAGE 
################################################################################
=head1 NAME

sci2web.pl 

=head1 DESCRIPTION

Master perl utility script of Sci2Web.

=head1 SYNOPSIS

sci2web.pl  <action> <options>

Valid actions are: 

=over 8

=item I<clean> 

    Clean the directory of the Sci2Web server site.

=item I<init> 

    Initialize a directory containing the version of a given application.

=item I<contvars> 

    Extract the control variable information from the template files.

=item I<genfiles> 

    Generate run files from templates using a given configuration file.

=item I<install> 

    Install and application in the Sci2Web server site.

=item I<remove> 

    Remove a version or a complete application from the Sci2Web server site.

=back 

=head1 COMMON OPTIONS

=over 8
    
=item B<--man|-m>: this man page

=item B<--help|-h>: simple help

=item B<--verbose|-v>: verbose mode

=back

=head1 ACTION OPTIONS

=over 8

=item I<claen>

=item B<--tmp | -t>: Remove the temporal directory content

=item B<--runs | -r>: Remove the content of the runs directory

=item B<--db | -d>: Reset the Sci2Web database

=item B<--log | -l>: Remove the content of the log directory 

=item B<--results | -R>: Remove the content of the results directory and database

=item I<init>

=item B<--appname | -a name>: Name of the application.

=item B<--vername | -v name>: Name of the version.

=back 

=cut
