#!/bin/bash
APACHE_USER="www-data"
APACHE_GROUP="www-data"

echo "Setting directories and file permissions..."
chown -R $APACHE_USER.$APACHE_GROUP runs tmp log
chown -R $APACHE_USER.$APACHE_GROUP apps/*/*.html
chown -R $APACHE_USER.$APACHE_GROUP pages/*
chown -R $APACHE_USER.$APACHE_GROUP js/ckfinder/userfiles
chown $APACHE_USER.$APACHE_GROUP lib/sci2web.{conf,db}
chmod og-rw lib/sci2web.{conf,db}
echo "Done."
