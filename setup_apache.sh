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
# Run the rConfig Apache Security Check. It fixes what it safely can and changes nothing when
# /etc/rconfig/skip-apache-hardening exists. Downloaded first so a cut-off download never runs; never fails the install.
CHECK_URL=https://dl.rconfig.com/downloads/rconfig-apache-check.sh
CHECK_FILE=$(mktemp)
if curl -fsSL "$CHECK_URL" -o "$CHECK_FILE"; then
    sudo bash "$CHECK_FILE" --yes --respect-optout
    CHECK_STATUS=$?
else
    CHECK_STATUS="download failed"
fi
rm -f "$CHECK_FILE"
if [ "$CHECK_STATUS" != 0 ]; then
    echo "WARNING: the rConfig Apache Security Check did not finish cleanly ($CHECK_STATUS). The install carries on." >&2
    echo "WARNING: Review it with: curl -fsSL $CHECK_URL | sudo bash -s -- --check" >&2
    echo "WARNING: Apply its fixes with: curl -fsSL $CHECK_URL | sudo bash" >&2
fi
