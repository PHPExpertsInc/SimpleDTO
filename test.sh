#!/bin/bash

time for PHPV in 7.4 8.0 8.1 8.2 8.3; do
    PHP_VERSION=$PHPV composer update
    PHPUNIT_V=''
    if [ $PHPV == '7.2' ]; then
        PHPUNIT_V='8'
    elif [ $PHPV == '7.3' ] || [ $PHPV == '7.4' ] || [ $PHPV == '8.0' ]; then
        PHPUNIT_V='9'
    else
        PHPUNIT_V='10'
    fi


    if [ $PHPV == '8.2' ]; then
        PHP_VERSION=$PHPV-debug phpunit -c phpunit.v${PHPUNIT_V}.xml
    else
        PHP_VERSION=$PHPV phpunit -c phpunit.v${PHPUNIT_V}.xml
    fi

    echo "Tested: PHP v$PHPV"
    sleep 2
done

