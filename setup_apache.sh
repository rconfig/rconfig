#! /bin/sh

# This script is used to setup apache for the project
if [ -f /etc/redhat-release ]; then  HTTPDDIR=/etc/httpd/conf.d/; fi;
if [ -f /etc/lsb-release ]; then  HTTPDDIR=/etc/apache2/sites-enabled; fi;
# Render rconfig-vhost.conf.dist into the untracked rconfig-vhost.local.conf, so git pull never meets a locally edited vhost.
# An existing rconfig-vhost.local.conf is kept. ServerName is this host's FQDN; ServerAlias lists its IPv4 addresses so the app still opens by IP.
VHOST_FILE=$PWD/rconfig-vhost.local.conf
if [ ! -f "$VHOST_FILE" ]; then
    SERVERNAME=$(hostname -f 2>/dev/null); [ -n "$SERVERNAME" ] || SERVERNAME=$(hostname)
    SERVER_IPS=$(ip -o -4 addr show scope global 2>/dev/null | awk '{ split($4, a, "/"); print a[1] }')
    [ -n "$SERVER_IPS" ] || SERVER_IPS=$(hostname -I 2>/dev/null | tr ' ' '\n')
    SERVER_IPS=$(printf '%s\n' "$SERVER_IPS" | grep -E '^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$' | grep -v '^127\.' | head -n 5 | tr '\n' ' ' | sed 's/ *$//')
    [ -n "$SERVER_IPS" ] || SERVER_IPS=$SERVERNAME
    sed -e "s+PWD+$PWD+g" -e "s+ServerName YourServerName+ServerName $SERVERNAME+" -e "s+ServerAlias YourServerName+ServerAlias $SERVER_IPS+" "$PWD/rconfig-vhost.conf.dist" > "$VHOST_FILE"
fi
if [ -f $HTTPDDIR/rconfig-vhost.conf ]; then unlink $HTTPDDIR/rconfig-vhost.conf; fi
sudo ln -s "$VHOST_FILE" $HTTPDDIR/rconfig-vhost.conf
if [ -f $HTTPDDIR/000-default.conf ]; then unlink $HTTPDDIR/000-default.conf; fi;
if [ -f /etc/redhat-release ]; then chown -R apache:apache $PWD; fi;
if [ -f /etc/redhat-release ]; then systemctl restart httpd; fi;
if [ -f /etc/lsb-release ]; then sudo chown -R www-data:www-data /var/www/html/rconfig; fi;
if [ -f /etc/lsb-release ]; then sudo chown -R $USER:www-data /var/www/html/rconfig; fi;
if [ -f /etc/lsb-release ]; then service apache2 restart; fi;
if [ -f /etc/lsb-release ]; then sudo a2enmod rewrite; fi;
if [ -f /etc/redhat-release ]; then httpd -S; fi;
if [ -f /etc/lsb-release ]; then apachectl -S; fi;
