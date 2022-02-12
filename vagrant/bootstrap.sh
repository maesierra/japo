#!/usr/bin/env bash

apt-get update
apt-get install -y apache2



#Mysql
MYSQL_ROOT_PASSWORD=293874jksdfssdfjsdhf823
wget http://repo.mysql.com/mysql-apt-config_0.8.10-1_all.deb
dpkg -i mysql-apt-config_0.8.10-1_all.deb
apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys 467B942D3A79BD29
apt update && apt install -y  mysql-server mysql-client
db_created=$(mysql -uroot -p$MYSQL_ROOT_PASSWORD -N --raw --batch -e "select count(*) from schemata where schema_name = 'japo';" information_schema)
echo "Checking database $db_created"
if [ "$db_created" = "0" ] ; then
    echo "Init db...."
    mysql -uroot -p$MYSQL_ROOT_PASSWORD < /vagrant/vagrant/files/db.init
fi
apt-get install -y language-pack-en-base

export LC_ALL=en_US.UTF-8 && export LANG=en_US.UTF-8 && apt-get install -y software-properties-common && add-apt-repository -y ppa:ondrej/php && apt-get update

#PHP install
apt-get install -y php8.0
#PHP extensions
apt-get install -y php8.0-xml php8.0-mysql php8.0-mbstring

#Picky extensions that need to be compiled
apt-get install -y php8.0-dev pkg-config git

if ! php -m | grep xdebug ; then
    wget https://pecl.php.net/get/xdebug-3.0.1.tgz
    gunzip -c xdebug-3.0.1.tgz | tar xf -
    cd xdebug-3.0.1
    phpize
    ./configure
    make
    make install
    echo 'zend_extension="xdebug.so"' > xdebug.ini
    echo 'xdebug.remote_enable=true' >>xdebug.ini
    echo 'xdebug.remote_connect_back=true' >>xdebug.ini
    echo 'xdebug.idekey=maesierra.net_at_vagrant' >>xdebug.ini
    mv xdebug.ini /etc/php/8.0/mods-available/xdebug.ini
    ln -s /etc/php/8.0/mods-available/xdebug.ini /etc/php/8.0/apache2/conf.d/20-xdebug.ini
    ln -s /etc/php/8.0/mods-available/xdebug.ini /etc/php/8.0/cli/conf.d/20-xdebug.ini
    cd ..
fi

if ! php -m | grep apcu ; then
    wget https://pecl.php.net/get/apcu-5.1.19.tgz
    gunzip -c apcu-5.1.19.tgz | tar xf -
    cd apcu-5.1.19/
    phpize
    ./configure
    make
    sudo make install
    echo 'extension="apcu.so"' > apcu.ini
    echo 'apc.enabled=1' >> apcu.ini
    echo 'apc.shm_size=32M' >> apcu.ini
    echo 'apc.ttl=7200' >> apcu.ini
    echo 'apc.enable_cli = 1' >> apcu.ini
    sudo mv apcu.ini /etc/php/8.0/mods-available/apcu.ini
    sudo ln -s /etc/php/8.0/mods-available/apcu.ini /etc/php/8.0/apache2/conf.d/20-apcu.ini
    sudo ln -s /etc/php/8.0/mods-available/apcu.ini /etc/php/8.0/cli/conf.d/20-apcu.ini
    cd ..
fi

a2enmod rewrite
a2enmod ssl

if [  ! -f /etc/apache2/sites-enabled/002-default-ssl.conf ]; then
    echo "Setting up apache SSL config to /etc/apache2/sites-enabled/002-default-ssl.conf"
    cp /etc/apache2/sites-available/default-ssl.conf /etc/apache2/sites-enabled/002-default-ssl.conf
    sed -i'.bak' 's/DocumentRoot \/var\/www\/html/DocumentRoot \/vagrant\/webroot/' /etc/apache2/sites-enabled/002-default-ssl.conf
    sed -i'' 's/<\/VirtualHost>/        <Directory "\/">\n                  Require all granted\n                  AllowOverride All\n               <\/Directory>\n        <\/VirtualHost>/' /etc/apache2/sites-enabled/002-default-ssl.conf
fi

if [ ! -d "/var/log/japo" ]; then
  mkdir "/var/log/japo"
  chown www-data "/var/log/japo"
  chgrp www-data "/var/log/japo"
fi

systemctl restart apache2

#npm
curl -sL https://deb.nodesource.com/setup_8.x | sudo -E bash -
apt-get install -y nodejs

#zip for composer
apt-get install -y zip unzip php8.0-zip

su vagrant
cd /vagrant
/usr/bin/php composer.phar self-update
/usr/bin/php composer.phar install