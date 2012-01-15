#!/bin/bash
APACHE_USER="www-data"
APACHE_GROUP="www-data"

echo "Setting directories and file permissions..."
chown -R $APACHE_USER.$APACHE_GROUP runs tmp log
chown -R $APACHE_USER.$APACHE_GROUP pages/*
chown -R $APACHE_USER.$APACHE_GROUP js/ckfinder/userfiles
chown $APACHE_USER.$APACHE_GROUP lib/sci2web.{conf,db}
find apps -type d -name "sci2web" -exec chown -R $APACHE_USER.$APACHE_GROUP {} \;
find apps -name "*.html" -exec chown -R $APACHE_USER.$APACHE_GROUP {} \;
chmod og-rwx lib/sci2web.{conf,db}
chmod a+x bin/*
echo "Done."
