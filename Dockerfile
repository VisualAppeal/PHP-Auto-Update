FROM php:8.0-apache

MAINTAINER VisualAppeal <tim@visualappeal.de>

RUN apt-get update && apt-get install -y libzip-dev libcurl4-openssl-dev
RUN docker-php-ext-install -j$(nproc) zip curl

ADD ./vendor /var/www/html/vendor
ADD ./example /var/www/html/example
ADD ./src /var/www/html/src

RUN chown -R www-data:www-data /var/www/html/example
