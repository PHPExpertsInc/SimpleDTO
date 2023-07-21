#!/bin/bash

time for PHPV in 7.2 7.3 7.4 8.0 8.1 8.2; do
    PHP_VERSION=$PHPV composer update

    if [ $PHPV == '8.2' ]; then
        PHP_VERSION=$PHPV-debug phpunit
    else
        PHP_VERSION=$PHPV phpunit
    fi

    echo "Tested: PHP v$PHPV"
    sleep 2
done

