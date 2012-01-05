#!/usr/bin/perl
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
#          _ ___               _     
#         (_)__ \             | |    
# ___  ___ _   ) |_      _____| |__  
#/ __|/ __| | / /\ \ /\ / / _ \ '_ \ 
#\__ \ (__| |/ /_ \ V  V /  __/ |_) |
#|___/\___|_|____| \_/\_/ \___|_.__/ 
#
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
# PACKAGE
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
#DATABASE INFORMATION
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$DBNAME="sci2web";
$DBSERVER="localhost";
$DBUSER="sci2web";
$DBPASS="WebPoweredNDSA";
$APACHEUSER="www-data";
$APACHEGROUP="www-data";
%CONFIG={};

#################################################################################
#EXTERNAL PACKAGES
#################################################################################
use Digest::MD5 qw(md5_hex);
use DBI;
use DBD::mysql;
use Getopt::Long;
use Pod::Usage;
use Switch;
use Term::ReadKey;
Getopt::Long::Configure("bundling");

#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
#OPERATORS
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
sub rprint{rprintFunc(@_);}
sub vprint{vprintFunc(@_);}
sub Exit{exitFunc(@_);}

#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
#CONNECT TO DATABASE
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$DB=DBI->connect("DBI:mysql:$DBNAME:$DBSERVER","$DBUSER","$DBPASS");

#################################################################################
#GLOBAL VARIABLES
#################################################################################
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
#BEHAVIOR
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$VERBOSE=0;
$EXITCODE=0;
$BACKUP=0;

#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
#VARIABLES
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
#FORMATTING SYNTAX: VarName::DefaultValue::DataType::Variable Name::Tab::Group
@VARSCOMP=("var","defval","datatype","varname","vartab","vargroup","vardesc","varprot");

#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
#DIRECTORIES
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$BINDIR="$ROOTDIR/bin";
$INSTALLDIR="$ROOTDIR/doc/install";
$APPSDIR="$ROOTDIR/apps";
$RUNSDIR="$ROOTDIR/runs";
$TMPDIR="$ROOTDIR/tmp";
$TRASHDIR="$ROOTDIR/tmp";

#################################################################################
#USEFUL ROUTINES
#################################################################################
sub error
{
    my $msg=shift;
    print stderr "Error:\n\t$msg\n";
    pod2usage(2);
    Exit(1)
}

sub unique
{
    my @list=@_;
    #SORT WILL ALTER THE ORDERING OF THE TABS
    #@list=sort(@list);
    my %seen=();
    @uniq=grep{!$seen{$_}++} @list;
    return @uniq;
}

sub bar
{
    my $char=shift;
    my $n=shift;
    my $str="",$i;
    for($i=0;$i<$n;$i++){$str.="$char";}
    return $str;
}
sub vprintFunc
{
    print @_ if($VERBOSE);
    return 0;
}

sub rprintFunc
{
    my $msg=shift;
    my $char=shift;
    my $str;
    if(length($char)>0){
	my $len=length($msg);
	my $bar=bar($char,$len);
	$str="$bar\n$msg\n$bar\n";
    }else{
	$str="$msg\n";
    }
    print $str;
    return 0;
}

sub readLines
{
    my $file=shift;
    open(fl,"<$file");
    my @lines=<fl>;chomp @lines;
    close(fl);
    return @lines;
}
sub printHash
{
    my %hash=@_;
    my @hvals=values(%hash),@hkeys=keys(%hash);
    $i=0;
    foreach $value (@hvals){
	print "$hkeys[$i] : $value\n";
	$i++;
    }
    print "\n";
}
sub searchHashValues
{
    my $string=shift;
    my %hash=@_;
    my @hvals=values(%hash);
    my @hkeys=keys(%hash);
    my $value,$qfound;
    my $i=0;
    foreach $value (@hvals){
	if($value=~/^$string$/){
	    return $hkeys[$i];
	}
	$i++;
    }
    return -1;
}
sub sysCmd
{
    my $cmd=join " ",@_;
    vprint "\tExecuting : $cmd\n";
    my $out=`$cmd`;
    $EXITCODE="$?";
    chop $out;
    vprint "\tOut:$out\n";
    return $out;
}
sub randInt
{
    my $n=shift;
    my $rnd=sprintf "%032.0f",$n*rand();
    return $rnd;
}
sub exitFunc
{
    sysCmd("rm -rf /tmp/*.$$");
    if($EXITCODE){
	exit(1);
    }else{
	exit(0);
    }
}

sub cleanConfig
{
    my $file=shift;
    my $fname=`basename $file`;chop $fname;
    sysCmd("cat lib/sci2web.conf | egrep -v '^#' | egrep -v '^<' | egrep -v '^/' | grep -v '^?' | grep -v '^*' | egrep -v '^\$' > /tmp/$fname.$$");
    
    return "/tmp/$fname.$$";
}

sub readConfig {
    my $envfile=shift;
    my %CONFIG;

    Error "File: $envfile does not exist." if(!-e $envfile);

    open(fl,"$envfile");
    my @lines=<fl>;
    chomp @lines;
    foreach $line (@lines){
	next if($line=~/^\#/ or
		$line=~/^$/);
	($var,$val)=split(/\s*=\s*/,$line);
	#vprint "VAR: $var\nVAL: $val\n";
	$CONFIG{"$var"}=$val;
    }
    close($fl);
    return %CONFIG;
}

sub promptAns
{
    my $question=shift;
    my $defval=shift;
    print "$question [$defval]:";
    my $ans=<STDIN>;
    if($ans!~/\w/){
	$ans=$defval;
    }
    return $ans;
}

sub mysqlCmd
{
    my $sql=shift;
    vprint "\tSQL:\n\t\t$sql\n";

    my $query=$DB->prepare($sql);
    my $nres=$query->execute or die("Database query failed.");
    vprint "\tNRES:\n\t\t$nres\n";

    my @result=(),@data=();
    while(@data=$query->fetchrow_array()){
	push(@result,@data);
    }
    vprint "\tRESULTS:\n\t\t@result\n";
    $query->finish();
    return @result;
}

sub mysqlCSV
{
    my $sql=shift;
    vprint "\tSQL:\n\t\t$sql\n";

    my $query=$DB->prepare($sql);
    my $nres=$query->execute or die("Database query failed.");
    vprint "\tNRES:\n\t\t$nres\n";

    my $data;
    my $resultcsv="";
    my $col,$fields="";
    my $i=0;
    while($data=$query->fetchrow_hashref()){
	foreach $col (keys(%{$data})){
	    if($i==0){
		$fields.="$col,";
	    }
	    $resultcsv.=$$data{$col}.",";
	}
	$resultcsv.="\n";
	$i++;
    }
    $resultcsv="$fields\n$resultcsv";
    $query->finish();
    return $resultcsv;
}

sub mysqlDo
{
    my $sql=shift;
    vprint "\tSQL:\n\t\t$sql\n";
    my $query=$DB->prepare($sql);
    my $nres=$query->execute or die("Database query failed:".$query->errstr);
    vprint "\tNRES:\n\t\t$nres\n";
    return 0;
}

sub noBlank
{
    my $var=shift;
    my $name=shift;
    $name="Variables" if($name!~/\w/);
    Error "$name must be not null" if(length($var)<1);
    return $var;
}

sub formatSqlOut
{
    my $resultcsv=shift;
    my @rows=split /\n/,$resultcsv;
    my $row,@fields;

    $i=1;
    foreach $row (@rows){
	@fields=split /,/,$row;
	$j=1;
	if($i==1){
	    print "Fields:\n";
	}else{
	    print "Entries:\n";
	}
	print "| ";
	foreach $field (@fields){
	    print "$j:$field | ";
	    $j++;
	}
	print "\n";
	print "\n" if($i==1);
	$i++;
    }
}

1;
