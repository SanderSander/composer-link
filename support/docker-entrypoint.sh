#!/usr/bin/env bash

git config --global --add safe.directory /app

export PHPUNIT_CONTAINERIZED=1
./vendor/bin/phpunit --testsuite=Integration --group=ubuntu-latest
