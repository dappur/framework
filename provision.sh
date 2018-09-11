#!/bin/bash

# install php
sudo apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0xF1656F24C74CD1D8
sudo add-apt-repository 'deb [arch=amd64] http://mirror.zol.co.zw/mariadb/repo/10.3/ubuntu bionic main'
sudo apt-get update
sudo apt-get upgrade
sudo apt-get install -y php php-pear php-fpm php-dev php-zip php-curl php-xmlrpc php-gd php-mysql php-mbstring php-xml libapache2-mod-php

# install composer
wget https://raw.githubusercontent.com/composer/getcomposer.org/1b137f8bf6db3e79a38a5bc45324414a6b1f9df2/web/installer -O - -q | php -- --quiet
sudo mv composer.phar /usr/local/bin/composer
echo "PATH=\"/home/vagrant/.config/composer/vendor/bin:\$PATH\"" >> /home/vagrant/.profile

source /home/vagrant/.profile

# install phinx
composer global require robmorgan/phinx

# install mariadb
sudo debconf-set-selections <<< "mariadb-server mysql-server/root_password password \"''\""
sudo debconf-set-selections <<< "mariadb-server mysql-server/root_password_again password \"''\""
sudo apt-get install -y mariadb-server mariadb-client
printf "[mysqld]\nplugin-load-add = auth_socket.so" | sudo tee -a /etc/mysql/mariadb.conf.d/dev.cnf
sudo systemctl restart mariadb.service
sudo mysql <<SQL
  CREATE DATABASE dev;
  USE dev;
  CREATE USER vagrant@localhost IDENTIFIED BY "";
  GRANT ALL PRIVILEGES ON dev.* TO vagrant@localhost;
  FLUSH PRIVILEGES;
SQL

cd /vagrant
cp settings.json.dist settings.json
composer install
phinx migrate

php -S 0.0.0.0:8888 -t public &
