#! /bin/sh

# This script is used to setup supervisor for the project
if [ -f /etc/redhat-release ]; then  SUPDIR=/etc/supervisord.d; fi;
if [ -f /etc/lsb-release ]; then  SUPDIR=/etc/supervisor/conf.d; fi;
if [ -f $SUPDIR/horizon_supervisor.ini ]; then unlink $SUPDIR/horizon_supervisor.ini; fi
sed -i -e s+PWD+$PWD+g $PWD/horizon_supervisor.ini
if [ -f /etc/redhat-release ]; then  ln -s $PWD/horizon_supervisor.ini $SUPDIR/horizon_supervisor.ini; fi;
if [ -f /etc/lsb-release ]; then  ln -s $PWD/horizon_supervisor.ini $SUPDIR/horizon_supervisor.conf; fi;
if [ -f /etc/redhat-release ]; then systemctl restart supervisord; fi;
if [ -f /etc/lsb-release ]; then sudo systemctl restart supervisor; fi;
supervisorctl status