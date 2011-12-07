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
    }
    #======================================================================
    #INSTALL APPLICATION VERSION
    #======================================================================
    case "install"
    {
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
