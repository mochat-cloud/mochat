#!/usr/bin/env bash

if [ "${APP_ENV}" = "dev" ]; then
  composer install;
  composer cs-fix;
  php bin/hyperf.php server:watch;
  echo "dev-done";
else
  php /opt/www/bin/hyperf.php start;
  echo "nonDev-done";
fi
