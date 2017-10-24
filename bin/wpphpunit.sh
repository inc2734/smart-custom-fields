#!/usr/bin/env bash

set -e;

themedir=$(pwd)

cd ${plugindir}

if [ -e ${plugindir}/bin/install-wp-tests.sh ]; then
  echo 'DROP DATABASE IF EXISTS wordpress_test;' | mysql -u root

  if [ -e /tmp/wordpress ]; then
    rm -fr /tmp/wordpress
  fi

  if [ -e /tmp/wordpress-tests-lib ]; then
    rm -fr /tmp/wordpress-tests-lib
  fi

  bash "${plugindir}/bin/install-wp-tests.sh" wordpress_test root '' localhost latest;
  vendor/bin/phpunit --configuration= ${plugindir}/phpunit.xml
else
  echo "${plugindir}/bin/install-wp-tests.sh not found."
fi;
