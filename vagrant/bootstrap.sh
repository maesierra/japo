#!/usr/bin/env bash

apt-get update
apt-get install -y apache2
mkdir /home/vagrant/www/html -p
if ! [ -L /var/www ]; then
  rm -rf /var/www
  ln -fs /home/vagrant/www /var/www
fi

debconf-set-selections <<< 'mysql-server-5.6 mysql-server/root_password password 293874jksdfssdfjsdhf823'
debconf-set-selections <<< 'mysql-server-5.6 mysql-server/root_password_again password 293874jksdfssdfjsdhf823'
apt-get install -y curl mysql-server-5.6 mysql-client-5.6

apt-get install -y language-pack-en-base

export LC_ALL=en_US.UTF-8 &&
export LANG=en_US.UTF-8 &&
apt-get install -y software-properties-common &&
add-apt-repository -y ppa:ondrej/php &&
apt-get update

#PHP install
apt-get install -y php5.6

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
service apache2 reload

#folders
mkdir /home/vagrant/www/html/japo/ -p
mkdir /home/vagrant/www/html/japo/tmp/ -p
mkdir /home/vagrant/www/html/japo/data/ -p
mkdir /home/vagrant/www/html/japo/logs/ -p