#!/bin/bash
#INSTALL APPLICATIONS INTO A NEWLY CONFIGURED SERVER-SITE
qerror=0
if [ ! -e .installed ];then
    echo;echo 
    echo "Installing 2B-dev..."
    make -C 2B-dev utilbuild
    qerror=$(($?+$qerror))
    echo
    echo "Enter the password root for your mysql server:"
    mysql -u root -p sci2web < 2B-dev/sci2web/controlvars.sql
    qerror=$(($?+$qerror))
    echo;echo 
    echo "Installing 3B-dev..."
    make -C 3B-dev utilbuild
    qerror=$(($?+$qerror))
    echo 
    echo "Enter the password root for your mysql server:"
    mysql -u root -p sci2web < 3B-dev/sci2web/controlvars.sql
    qerror=$(($?+$qerror))
    echo;echo
    if [ $qerror -lt 1 ];then
	echo "Succesfully installed."
	touch .installed
    else
	echo "$qerror errors occur.  Please check."
    fi
else
    echo "MercuPy has been already installed in your server site"
fi
