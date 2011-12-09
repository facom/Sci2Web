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
    case "init" {
	push(@cmdopt,("appname|a=s","vername|v=s"));
    }
    case "contvars" {
	push(@cmdopt,("appdir|d=s"));
    }
    case "genfiles" {
	push(@cmdopt,("runconf|r=s","rundir|d=s"));
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
	print fl "Application = $appname\n";
	print fl "Version = $vername\n";
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
	    $vargroup=$VARS{$j,"vargroup"};
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
	print fa "#T:Default template\n";
	print fa "#$b1\n#DEFAULT CONFIGURATION FILE\n#$b1\n";
	foreach $vartab (@vartabs){
	    print fa "#$b2\n#TAB $vartab\n#$b2\n";
	    print fv "#TAB:$vartab\n";
	    $vtgroups="${vartab}_groups";
	    foreach $vargroup (@{$vtgroups}){
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
}

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
