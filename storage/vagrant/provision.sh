#!/bin/bash
PORT=$1
ROOTPASS=$2
DBNAME=$3
DBUSER=$4
DBPASS=$5
DBPORT=$6
PMAUSER="phpmyadmin"
PMAPASS=$(perl -e 'print map{("a".."z","A".."Z",0..9)[int(rand(62))]}(1..32)');
DEBIANPASS=$(perl -e 'print map{("a".."z","A".."Z",0..9)[int(rand(62))]}(1..16)');
BLOWFISH=$(perl -e 'print map{("a".."z","A".."Z",0..9)[int(rand(62))]}(1..32)');

# mariadb unattended install
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password password $ROOTPASS"
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $ROOTPASS"

# install php
sudo apt-get update
sudo apt-get upgrade
sudo apt-get install -y php composer php-pear php-fpm php-dev php-zip php-curl php-xmlrpc php-gd php-mysql php-mbstring php-xml libapache2-mod-php mysql-server mysql-client apache2

# configure mariadb
if [ ! -f "/etc/mysql/mariadb.conf.d/dev.cnf" ]; then
  printf "[mysqld]\nplugin-load-add = auth_socket.so" | sudo tee -a /etc/mysql/mariadb.conf.d/dev.cnf
  sudo sed -i "s/^bind-address.*/bind-address = 0.0.0.0\nport = ${DBPORT}/" /etc/mysql/mysql.conf.d/mysqld.cnf
  # sudo sed -i "s/^# port.*/port = ${DBPORT}/" /etc/mysql/my.cnf
  sudo systemctl restart mysql.service
fi

sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/internal/skip-preseed boolean true"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/dbconfig-install boolean false"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/app-password-confirm password ${PMAPASS}"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/admin-pass password ${ROOTPASS}"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/app-pass password ${PMAPASS}"

sudo apt-get install -y phpmyadmin

#if [ ! -d "/usr/share/phpmyadmin" ]; then
#  # install phpmyadmin
#  cd /usr/share
#  sudo wget https://files.phpmyadmin.net/phpMyAdmin/4.7.4/phpMyAdmin-4.7.4-all-languages.zip
#  sudo apt-get install unzip
#  sudo unzip phpMyAdmin-4.7.4-all-languages.zip
#  sudo rm phpMyAdmin-4.7.4-all-languages.zip
#  sudo mv phpMyAdmin-4.7.4-all-languages phpmyadmin
#  cd phpmyadmin
#  sudo cp config.sample.inc.php config.inc.php

  # update phpmyadmin config
#  sudo sed -i "s|blowfish_secret'] = ''|blowfish_secret'] = '$BLOWFISH'|g" /usr/share/phpmyadmin/config.inc.php
#  sudo sed -i "s|AllowNoPassword'] = false;|AllowNoPassword'] = true;|g" /usr/share/phpmyadmin/config.inc.php
#fi

# create phpmyadmin user and tables
#if [ ! -d /var/lib/mysql/phpymadmin ]; then
#  sudo mysql -uroot -p$ROOTPASS <<SQL
#	  CREATE DATABASE IF NOT EXISTS phpmyadmin
#      DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;
#    USE phpmyadmin;
#    source /usr/share/phpmyadmin/sql/create_tables.sql;
#  	CREATE USER IF NOT EXISTS ${PMAUSER}@localhost IDENTIFIED BY "${PMAPASS}";
#  	GRANT SELECT ON phpmyadmin.* TO ${PMAUSER}@'localhost';
#  	FLUSH PRIVILEGES;
#SQL
#fi

# update php max size
sudo sed -i "s|upload_max_filesize = 2M|upload_max_filesize = 200M|g" /etc/php/7.4/apache2/php.ini
sudo sed -i "s|post_max_size = 8M|post_max_size = 200M|g" /etc/php/7.4/apache2/php.ini

# update apache config
sudo sed -i "s|DocumentRoot /var/www/html|DocumentRoot /vagrant/public\n\tAlias /phpmyadmin /usr/share/phpmyadmin\n\t<Directory /vagrant/public>\n\t\tOptions Indexes FollowSymLinks Includes ExecCGI\n\t\tAllowOverride All\n\t\tRequire all granted\n\t</Directory>|g" /etc/apache2/sites-enabled/000-default.conf
sudo sed -i "s|:80|:${PORT}|g" /etc/apache2/sites-enabled/000-default.conf
sudo sed -i "s|Listen 80|Listen ${PORT}|g" /etc/apache2/ports.conf

sudo a2enmod rewrite 
sudo service apache2 restart

# create database if doesn't exist
sudo mysql -uroot -p$ROOTPASS <<SQL
  CREATE DATABASE IF NOT EXISTS ${DBNAME};
  USE ${DBNAME};
  CREATE USER IF NOT EXISTS ${DBUSER}@'%' IDENTIFIED BY "${DBPASS}";
  GRANT ALL PRIVILEGES ON ${DBNAME}.* TO ${DBUSER}@'%';
  FLUSH PRIVILEGES;
SQL

# update composer and install
cd /vagrant
composer install
./vendor/bin/phinx migrate