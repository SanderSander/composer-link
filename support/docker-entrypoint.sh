#!/usr/bin/env bash

git config --global --add safe.directory /composer-link

export PHPUNIT_INTEGRATION=1
./vendor/bin/phpunit --testsuite=Integration --group=ubuntu-latest "$@"
