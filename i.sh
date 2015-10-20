#!/usr/bin/env bash

TTT=$(mktemp -d) && cd $TTT
curl -sS 'https://getcomposer.org/installer' | php
php composer.phar require ejz/dom:~1.0
cd vendor/ejz/dom/
curl -sS 'https://getcomposer.org/installer' | php
php composer.phar install
chmod a+x install.sh
./install.sh
cd - && rm -rf $TTT
