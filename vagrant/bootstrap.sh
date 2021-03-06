#!/usr/bin/env bash

apt-get update
apt-get install -y apache2

MYSQL_ROOT_PASSWORD=293874jksdfssdfjsdhf823

#Mysql
debconf-set-selections <<< 'mysql-server-5.6 mysql-server/root_password password '$MYSQL_ROOT_PASSWORD
debconf-set-selections <<< 'mysql-server-5.6 mysql-server/root_password_again password '$MYSQL_ROOT_PASSWORD
apt-get install -y curl mysql-server-5.6 mysql-client-5.6
db_created=$(mysql -uroot -p$MYSQL_ROOT_PASSWORD -N --raw --batch -e "select count(*) from schemata where schema_name = 'japo';" information_schema)
echo "Checking database $db_created"
if [ "$db_created" = "0" ] ; then
    echo "Init db...."
    mysql -uroot -p$MYSQL_ROOT_PASSWORD < /vagrant/vagrant/files/db.init
fi
apt-get install -y language-pack-en-base

export LC_ALL=en_US.UTF-8 &&
export LANG=en_US.UTF-8 &&
apt-get install -y software-properties-common &&
add-apt-repository -y ppa:ondrej/php &&
apt-get update

#PHP install
apt-get install -y php7.1
#PHP extensions
apt-get install -y php7.1-xml php7.1-mysql php7.1-mbstring

#Picky extensions that need to be compiled
apt-get install -y php7.1-dev pkg-config git

if ! php -m | grep xdebug ; then
    wget https://pecl.php.net/get/xdebug-2.6.1.tgz
    gunzip -c xdebug-2.6.1.tgz | tar xf -
    cd xdebug-2.6.1
    phpize
    ./configure
    make
    make install
    echo 'zend_extension="xdebug.so"' > xdebug.ini
    echo 'xdebug.remote_enable=true' >>xdebug.ini
    echo 'xdebug.remote_connect_back=true' >>xdebug.ini
    echo 'xdebug.idekey=maesierra.net_at_vagrant' >>xdebug.ini
    mv xdebug.ini /etc/php/7.1/mods-available/xdebug.ini
    ln -s /etc/php/7.1/mods-available/xdebug.ini /etc/php/7.1/apache2/conf.d/20-xdebug.ini
    ln -s /etc/php/7.1/mods-available/xdebug.ini /etc/php/7.1/cli/conf.d/20-xdebug.ini
    cd ..
fi

if ! php -m | grep apcu ; then
    wget https://pecl.php.net/get/apcu-5.1.5.tgz
    gunzip -c apcu-5.1.5.tgz | tar xf -
    cd apcu-5.1.5/
    phpize
    ./configure
    make
    sudo make install
    echo 'extension="apcu.so"' > apcu.ini
    echo 'apc.enabled=1' >> apcu.ini
    echo 'apc.shm_size=32M' >> apcu.ini
    echo 'apc.ttl=7200' >> apcu.ini
    echo 'apc.enable_cli = 1' >> apcu.ini
    sudo mv apcu.ini /etc/php/7.1/mods-available/apcu.ini
    sudo ln -s /etc/php/7.1/mods-available/apcu.ini /etc/php/7.1/apache2/conf.d/20-apcu.ini
    sudo ln -s /etc/php/7.1/mods-available/apcu.ini /etc/php/7.1/cli/conf.d/20-apcu.ini
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

service apache2 reload

#npm
curl -sL https://deb.nodesource.com/setup_8.x | sudo -E bash -
apt-get install -y nodejs

#zip for composer
apt-get install -y zip unzip php7.1-zip

cd /vagrant
/usr/bin/php composer.phar self-update
/usr/bin/php composer.phar install

#Clean up because boostrap is run as sudo
rm /tmp/phinx.json
chown vagrant /vagrant/react/japo/ -R
chown vagrant /vagrant/react/japo/node_modules/ -R
chown vagrant /vagrant/react/japo/build/ -R