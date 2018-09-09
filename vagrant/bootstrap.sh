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
apt-get install -y php5.6
#PHP extensions
apt-get install -y php5.6-xml php5.6-mysql php5.6-mbstring

#Picky extensions that I have to compile
apt-get install -y php5.6-dev pkg-config git
mkdir php-modules -p

if ! php -m | grep xdebug ; then
    wget https://pecl.php.net/get/xdebug-2.5.5.tgz
    gunzip -c xdebug-2.5.5.tgz | tar xf -
    cd xdebug-2.5.5
    phpize
    ./configure
    make
    make install
    echo 'zend_extension="xdebug.so"' > xdebug.ini
    echo 'xdebug.remote_enable=true' >>xdebug.ini
    echo 'xdebug.remote_connect_back=true' >>xdebug.ini
    echo 'xdebug.idekey=maesierra.net_at_vagrant' >>xdebug.ini
    mv xdebug.ini /etc/php/5.6/mods-available/xdebug.ini
    ln -s /etc/php/5.6/mods-available/xdebug.ini /etc/php/5.6/apache2/conf.d/20-xdebug.ini
    ln -s /etc/php/5.6/mods-available/xdebug.ini /etc/php/5.6/cli/conf.d/20-xdebug.ini
    cd ..
fi

a2enmod rewrite
a2enmod ssl

if ! [ -L /etc/apache2/sites-enabled/002-default-ssl.conf ]; then
    ln -s /etc/apache2/sites-available/default-ssl.conf /etc/apache2/sites-enabled/002-default-ssl.conf
    sed -i'.bak' 's/<\/VirtualHost>/        Alias \/japo \/vagrant\/react\/japo\/build\n               <Directory "\/vagrant\/react\/japo\/build">\n                  Require all granted\n               <\/Directory>\n        <\/VirtualHost>/' /etc/apache2/sites-enabled/002-default-ssl.conf
    sed -i'' 's/<\/VirtualHost>/        Alias \/api\/japo \/vagrant\/api\n               <Directory "\/vagrant\/api">\n                  Require all granted\n               <\/Directory>\n        <\/VirtualHost>/' /etc/apache2/sites-enabled/002-default-ssl.conf
fi

if [ ! -d "/var/log/japo" ]; then
  mkdir "/var/log/japo"
  chown www-data "/var/log/japo"
  chgrp www-data "/var/log/japo"
fi

service apache2 reload
