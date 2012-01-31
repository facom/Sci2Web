#!/bin/bash
#INSTALL APPLICATIONS INTO A NEWLY CONFIGURED SERVER-SITE
qerror=0
echo > /tmp/db.$$
if [ ! -e .installed ];then
    echo;echo 
    for version in $(ls -d */sci2web)
    do
	version=$(dirname $version)
	echo 
	echo "Copying big files..."
	cp -rf cspice $version/util
	qerror=$(($?+$qerror))
	echo
	echo "Installing $version..."
	make -C $version utilbuild
	qerror=$(($?+$qerror))
	echo 
	echo "Adding entries to database..."
	cat $version/sci2web/controlvars.sql >> /tmp/db.$$
	qerror=$(($?+$qerror))
	echo
	echo "Releasing versions..."
	../../bin/sci2web.pl release --appname MercuPy --vername $version
	qerror=$(($?+$qerror))
	../../bin/sci2web.pl release --appname MercuPy --vername $version --sci2web
	qerror=$(($?+$qerror))
    done
    echo "Creating databases. Enter the password root for your mysql server:"
    mysql -u root -p sci2web < /tmp/db.$$
    qerror=$(($?+$qerror))
    
    if [ $qerror -lt 1 ];then
	echo "Succesfully installed."
	touch .installed
    else
	echo "$qerror errors occur.  Please check."
    fi
else
    echo "MercuPy has been already installed in your server site.  If you want to force the installation remove the '.installed' file"
fi
rm -rf /tmp/*.$$
