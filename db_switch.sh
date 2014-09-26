#!/bin/bash
if [ "$1" == "dev" ]
then
  cp -r phpMyAdmin/config.inc.php_dev phpMyAdmin/config.inc.php
  echo "Set up for dev"
elif [ "$1" == "prod" ]
then
  cp -r phpMyAdmin/config.inc.php_prod phpMyAdmin/config.inc.php
  echo "Set up for prod"
else
  echo "Usage ./db_switch.sh [dev | prod]"
fi
