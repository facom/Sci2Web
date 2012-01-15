#!/bin/bash
SCI2WEB_USER="sci2web"
APACHE_USER="www-data"
APACHE_GROUP="www-data"

echo "Setting directories and file permissions..."
chown -R $APACHE_USER.$APACHE_GROUP runs tmp log
chown -R $APACHE_USER.$APACHE_GROUP pages/*
chown $APACHE_USER.$APACHE_GROUP lib/sci2web.{conf,db}
chown $SCI2WEB_USER.$APACHE_GROUP .ssh
chmod o-rwx .ssh
find apps -type d -name "sci2web" -exec chown -R $APACHE_USER.$APACHE_GROUP {} \;
find apps -name "*.html" -exec chown -R $APACHE_USER.$APACHE_GROUP {} \;
chmod o-rwx lib/sci2web.{conf,db}
chmod -R ug+rw runs tmp log lib/sci2web.{conf,db}
chmod a+x bin/*
echo "Done."
