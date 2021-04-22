FROM php:7.4-apache

RUN echo "ServerName https://docker-api-sf.herokuapp.com/" >> /etc/apache2/apache2.conf

RUN apt-get update \
    && apt-get install -y --no-install-recommends locales apt-utils git libicu-dev g++ libpng-dev libxml2-dev libzip-dev libonig-dev libxslt-dev;

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
   mv composer.phar /usr/local/bin/composer

RUN  curl -L --fail https://github.com/docker/compose/releases/download/1.29.1/run.sh -o /usr/local/bin/docker-compose && \
     chmod +x /usr/local/bin/docker-compose

COPY ./ports.conf /etc/apache2/ports.conf
COPY ./vhosts/apache.conf /etc/apache2/site-enabled/000-default.conf

WORKDIR /var/www/