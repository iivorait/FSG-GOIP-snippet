FROM php:8.0.9-apache-buster
RUN docker-php-ext-install sockets
RUN sed -i "s/Listen 80/Listen 8080/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
COPY . /var/www/html/
