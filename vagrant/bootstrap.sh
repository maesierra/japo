#!/usr/bin/env bash

apt-get update
apt-get install -y apache2



#Mysql
MYSQL_ROOT_PASSWORD=293874jksdfssdfjsdhf823
wget http://repo.mysql.com/mysql-apt-config_0.8.12-1_all.deb
dpkg -i mysql-apt-config_0.8.12-1_all.deb
apt-key adv --keyserver keyserver.ubuntu.com --recv-keys B7B3B788A8D3785C
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
apt-get install -y php8.2
# To keep the same setup as dreamhost
ln -s /usr/bin/php8.2 /usr/bin/php-8.2

#PHP extensions
apt-get install -y php8.2-xml php8.2-mysql php8.2-mbstring php8.2-xdebug

#Picky extensions that need to be compiled
apt-get install -y php8.2-dev pkg-config git

if ! php -m | grep apcu ; then
    wget https://pecl.php.net/get/apcu-5.1.22.tgz
    gunzip -c apcu-5.1.22.tgz | tar xf -
    cd apcu-5.1.22/
    phpize
    ./configure
    make
    sudo make install
    echo 'extension="apcu.so"' > apcu.ini
    echo 'apc.enabled=1' >> apcu.ini
    echo 'apc.shm_size=32M' >> apcu.ini
    echo 'apc.ttl=7200' >> apcu.ini
    echo 'apc.enable_cli = 1' >> apcu.ini
    sudo mv apcu.ini /etc/php/8.2/mods-available/apcu.ini
    sudo ln -s /etc/php/8.2/mods-available/apcu.ini /etc/php/8.2/apache2/conf.d/20-apcu.ini
    sudo ln -s /etc/php/8.2/mods-available/apcu.ini /etc/php/8.2/cli/conf.d/20-apcu.ini
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
curl -sL https://deb.nodesource.com/setup_18.x | sudo -E bash -
apt-get install -y nodejs

#zip for composer
apt-get install -y zip unzip php8.2-zip

su vagrant
cd /vagrant
/usr/bin/php composer.phar self-update
/usr/bin/php composer.phar install