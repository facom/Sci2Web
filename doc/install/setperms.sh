#!/bin/bash
APACHE_USER="www-data"
APACHE_GROUP="www-data"

echo "Setting directories and file permissions..."
chown -R $APACHE_USER.$APACHE_GROUP runs tmp log
chown -R $APACHE_USER.$APACHE_GROUP apps/*/*.html
chown -R $APACHE_USER.$APACHE_GROUP pages/*/content/*.html
chown -R $APACHE_USER.$APACHE_GROUP js/ckfinder/userfiles
echo "Done."
