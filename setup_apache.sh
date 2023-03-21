#! /bin/sh

# This script is used to setup apache for the project
if [ -f /etc/redhat-release ]; then  HTTPDDIR=/etc/httpd/conf.d/; fi;
if [ -f /etc/lsb-release ]; then  HTTPDDIR=/etc/apache2/sites-enabled; fi;
sed -i -e s+PWD+$PWD+g $PWD/rconfig-vhost.conf
if [ -f $HTTPDDIR/rconfig-vhost.conf ]; then unlink $HTTPDDIR/rconfig-vhost.conf; fi
sudo ln -s $PWD/rconfig-vhost.conf $HTTPDDIR/rconfig-vhost.conf
if [ -f $HTTPDDIR/000-default.conf ]; then unlink $HTTPDDIR/000-default.conf; fi;
if [ -f /etc/redhat-release ]; then chown -R apache:apache $PWD; fi;
if [ -f /etc/redhat-release ]; then systemctl restart httpd; fi;
if [ -f /etc/lsb-release ]; then sudo chown -R www-data:www-data /var/www/html/rconfig; fi;
if [ -f /etc/lsb-release ]; then sudo chown -R $USER:www-data /var/www/html/rconfig; fi;
if [ -f /etc/lsb-release ]; then service apache2 restart; fi;
if [ -f /etc/lsb-release ]; then sudo a2enmod rewrite; fi;
if [ -f /etc/redhat-release ]; then httpd -S; fi;
if [ -f /etc/lsb-release ]; then apachectl -S; fi;
