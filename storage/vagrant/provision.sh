#!/bin/bash
PORT=$1
ROOTPASS=$2
PMAPASS=$3
DBNAME=$4
DBUSER=$5
DBPASS=$6
DEBIANPASS=$(perl -e 'print map{("a".."z","A".."Z",0..9)[int(rand(62))]}(1..16)');

# mariadb unattended install
sudo debconf-set-selections <<< "mariadb-server mysql-server/root_password password $ROOTPASS"
sudo debconf-set-selections <<< "mariadb-server mysql-server/root_password_again password $ROOTPASS"

# phpmyadmin unattended install
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/dbconfig-install boolean true"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/admin-user string root"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/admin-pass password $ROOTPASS"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/app-pass password $PMAPASS"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/app-password-confirm password $PMAPASS"

# install php
sudo apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0xF1656F24C74CD1D8
sudo add-apt-repository 'deb [arch=amd64] http://mirror.zol.co.zw/mariadb/repo/10.3/ubuntu bionic main'
sudo apt-get update
sudo apt-get upgrade
sudo apt-get install -y php php-pear php-fpm php-dev php-zip php-curl php-xmlrpc php-gd php-mysql php-mbstring php-xml libapache2-mod-php mariadb-server mariadb-client

# install composer
wget https://raw.githubusercontent.com/composer/getcomposer.org/1b137f8bf6db3e79a38a5bc45324414a6b1f9df2/web/installer -O - -q | php -- --quiet
sudo mv composer.phar /usr/local/bin/composer
echo "PATH=\"/home/vagrant/.config/composer/vendor/bin:\$PATH\"" >> /home/vagrant/.profile

source /home/vagrant/.profile

# install phinx
composer global require robmorgan/phinx

# configure mariadb
printf "[mysqld]\nplugin-load-add = auth_socket.so" | sudo tee -a /etc/mysql/mariadb.conf.d/dev.cnf
sudo systemctl restart mariadb.service

# update apache config
sudo sed -i "s|Listen 80|Listen $PORT|g" /etc/apache2/ports.conf
sudo sed -i "s|*:80|*:$PORT|g" /etc/apache2/sites-enabled/000-default.conf
sudo sed -i "s|DocumentRoot /var/www/html|DocumentRoot /vagrant/public\n\t<Directory /vagrant/public>\n\t\tOptions Indexes FollowSymLinks Includes ExecCGI\n\t\tAllowOverride All\n\t\tRequire all granted\n\t</Directory>|g" /etc/apache2/sites-enabled/000-default.conf
sudo a2enmod rewrite
sudo service apache2 restart

# create database if doesn't exist
sudo mysql -uroot -p$ROOTPASS <<SQL
  CREATE DATABASE IF NOT EXISTS ${DBNAME};
  USE ${DBNAME};
  CREATE USER IF NOT EXISTS ${DBUSER}@localhost IDENTIFIED BY "${DBPASS}";
  GRANT ALL PRIVILEGES ON ${DBNAME}.* TO ${DBUSER}@localhost;
  FLUSH PRIVILEGES;
SQL

cd /vagrant
composer install
phinx migrate