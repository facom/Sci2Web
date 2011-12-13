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
    case "remove" {
	push(@cmdopt,("appname|a=s","vername|v=s"));
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
	if($options{db} or $options{all}){
	    rprint "Resetting databases...";
	    $ans=promptAns("Do you want to proceed?(y/n)",$ans) if($ans!~/a/i);
	    if($ans=~/[ya]/i){
		print "Provide the MySQL root password:\n\t";
		`mysql -u root -p < $ROOTDIR/doc/install/sci2web.sql`;
		die("Failed authentication") if($?);
		$qclean=1;
	    }
	}
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
		$apps=sysCmd("ls $ROOTDIR/apps | grep -v template");
		foreach $app (split /\s+/,$apps){
		    $vers=sysCmd("ls -d $ROOTDIR/apps/$app/*/sci2web");
		    foreach $verdir (split /\s+/,$vers){
			$ver=sysCmd("basename \$(dirname $verdir)");
			$tbname="${app}_${ver}";
			print "Removing entries in table $tbname...\n";
			sysCmd("echo 'truncate table $tbname;' > /tmp/db.$$");
			`mysql -u $DBUSER --password=$DBPASS $DBNAME < /tmp/db.$$`;
			die("Failed authentication") if($?);
		    }
		}
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
	Error "A changeslog summary hould be provided"
	    if($changeslog!~/\w/);

	rprint "Initializing directory as application '$appname' and version '$vername'","=";
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#COPYING SCI2WEB CONFIGURATION DIRECTORY
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	if(!-d "sci2web"){
	    rprint "Copying sci2web templates...";
	    sysCmd("cp -rf $INSTALLDIR/sci2web .");
	    sysCmd("ln -s $BINDIR sci2web/bin");
	}else{
	    rprint "Directory already initialized...";
	}

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CREATE THE BASIC CONFIGURATION FILE
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Initializing the version configuration file...";
	open(fl,">sci2web/version.conf");
	print fl bar("#",50)."\n";
	print fl "Application = $appname\n";
	print fl "AppCompleteName = Complete name of the application\n";
	print fl "AppBrief = Brief description of the application\n";
	print fl bar("#",50)."\n";
	print fl "Version = $vername\n";
	print fl "EmailsContributors = $emails\n";
	print fl "ChangesLog = $changeslog\n";
	print fl "InfoPages = true\n";
	print fl "QueueMode = QueueList\n";
	print fl "ResultsDatabase = true\n";
	print fl bar("#",50)."\n";
	print fl "TabHeight = 80%\n";
	print fl "RunTab = true\n";
	print fl "FilesTab = true\n";
	print fl "ControlButtons = true\n";
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
	$list=`find $basedir -name ".\*.temp" 2> /dev/null`;chop $list;
	@files=split /\s+/,$list;
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
	    $vartab="All" if($vartab!~/\w/);
	    $vargroup=$VARS{$j,"vargroup"};
	    $vargroup="General" if($vargroup!~/\w/);
	    vprint "$vartab,$vargroup\n";
	    $vtgroups="${vartab}_gs";
	    $vtivars="${vartab}_${vargroup}_ivar";
	    push(@{$vtgroups},$vargroup);
	    push(@vts,$vartab);
	    push(@{$vtivars},$j);
	}
	@vartabs=unique(@vts);
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
	    print fa "#$b2\n#TAB $vartab\n#$b2\n";
	    print fv "#TAB:$vartab\n";
	    $vtgroups="${vartab}_groups";
	    foreach $vargroup (@{$vtgroups}){
		print "\t\tGroup: $vargroup\n";
		print fa "#$b3\n#GROUP $vargroup\n#$b3\n";
		print fv "#GROUP:$vargroup\n";
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
		    print fa "#Variable $varname\n$var = $defval\n";
		    print fv "\n";
		    print "\t\t\tVariable: $var ($varname,$datatype)\n";
		}
	    }
	}
	close(fa);
	close(fv);

	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#CREATE THE SQL DATABASE TABLE SCRIPT
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Creating SQL script file...";
	$sql="drop table if exists ${appname}_${vername};\n";
	$query=$DB->prepare($sql);
	$nres=$query->execute();
$sql.=<<SQL;
create table ${appname}_${vername} (
dbrunhash char(32) not null,
dbauthor varchar(255),
dbdate datetime not null,
SQL
	    foreach $vartab (@vartabs){
		$vtgroups="${vartab}_groups";
		foreach $vargroup (@{$vtgroups}){
		    $vtivars="${vartab}_${vargroup}_ivar";
		    foreach $j (@{$vtivars}){
			$varname=$VARS{$j,"var"};
			$vartype=$VARS{$j,"datatype"};
			$vartype=~s/boolean/tinyint\(1\)/;
			$vartype=~s/file/varchar\(255\)/;
			$sql.="$varname $vartype,\n";
		    }
		}
	    }
$sql.=<<SQL;
primary key (dbrunhash),
#LINKS
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
		    if($lines[$j]=~s/\[\[$var[^\]]*\]\]/$val/gi){
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

    case "install" {
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#READ VERSION CONFIGURATION
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	%CONFIG=readConfig("sci2web/version.conf");
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
	    $files=sysCmd("cd $tempdir;find . -type f -o -type l");
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
	    #========================================
	    #SAVE CONFIGURATION
            #========================================
	    sysCmd("head -n 4 sci2web/version.conf > $appdir/app.conf");
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
	#CHECK IF VERSION ALREADY EXIST
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	$verdir="$APPSDIR/$appname/$appver";
	if(!-d $verdir){
	    rprint "Version does not exist. Creating a new one.";
	    sysCmd("mkdir -p $verdir");
	    #========================================
	    #SAVE VERSION DATABASE
            #========================================
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
	    #COPY FILES INTO VERSION DIR
            #========================================
	    rprint "Copying files into version dir...";
	    sysCmd("tar cf - * .[a-zA-Z]* | tar xf - -C $verdir");
	}else{
	    Error "Version '$appver' already exists...";
	}
	
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	#FINALIZE
	#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	rprint "Application/Version installed","=";
    }

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
	    rprint "Removing database of results...";
	    mysqlDo("drop table if exists $verdbname");
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
	}
	rprint "Application components removed successfully";
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

=item I<init> 

    Initialize a directory containing the version of a given application.

=back 

=head1 COMMON OPTIONS

=over 8
    
=item B<--man|-m>: this man page

=item B<--help|-h>: simple help

=item B<--verbose|-v>: verbose mode

=back

=head1 ACTION OPTIONS

=over 8

=item I<init>

=item B<--appname | -a name>: Name of the application.

=item B<--vername | -v name>: Name of the version.

=back 

=cut
