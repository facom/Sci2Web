#!/bin/bash
APACHE_USER="www-data"
APACHE_GROUP="www-data"

chown -R $APACHE_USER.$APACHE_GROUP runs tmp
chown -R $APACHE_USER.$APACHE_GROUP apps/*/*.html
chown -R $APACHE_USER.$APACHE_GROUP pages/*/content/*.html
chown -R $APACHE_USER.$APACHE_GROUP js/ckfinder/userfiles
