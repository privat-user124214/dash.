FROM php:8.2-apache

# Apache Rewrite aktivieren
RUN a2enmod rewrite

# Konfiguration für .htaccess erlauben
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Systempakete installieren, PostgreSQL und mysqli Extensions aktivieren
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql pgsql mysqli

# Projektdateien kopieren
COPY . /var/www/html/

# Rechte setzen
RUN chown -R www-data:www-data /var/www/html
