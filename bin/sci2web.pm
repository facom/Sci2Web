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
#################################################################################
#EXTERNAL PACKAGES
#################################################################################
use Digest::MD5 qw(md5_hex);
use DBI;
use DBD::mysql;
#OPERATORS
sub vprint{vprintFunc(@_);}
sub Exit{exitFunc(@_);}

#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
#DATABASE
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$DBNAME="sci2web";
$DBSERVER="localhost";
$DBUSER="sci2web";
$DBPASS="WebPoweredNDSA";
%CONFIG={};

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
$VERBOSE=1;
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
$APPSDIR="$ROOTDIR/apps";
$RUNSDIR="$ROOTDIR/runs";

#################################################################################
#USEFUL ROUTINES
#################################################################################
sub unique
{
    my @list=@_;
    @list=sort(@list);
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
sub marquee
{
    my $msg=shift;
    my $char=shift;
    my $len=length($msg);
    my $bar=bar($char,$len);
    my $str="$bar\n$msg\n$bar\n";
    return $str;
}
sub vprintFunc
{
    print @_ if($VERBOSE);
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

sub readConfig($filename)
{
    my $filename=shift;
    my @lines,$line,$field,@fields;
    if(!-e $filename){
	print "File '$filename' does not exist\n";
	return;
    }
    open(fl,"<$filename");
    @lines=<fl>;chomp @lines;
    close(fl);
    foreach $line (@lines)
    {
	next if($line=~/^#/ or $line!~/[^\s]/);
	@fields=split /\s*=\s*/,$line;
	$field=$fields[0];
	next if($field!~/\w/);
        $CONFIG{"$field"}=join "=",@fields[1..$#fields];
    }
}

1;
