FROM php:8.2-apache
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql
RUN a2enmod rewrite
# Forzar DirectoryIndex (suele venir por defecto)
RUN printf "DirectoryIndex index.php index.html\n" > /etc/apache2/conf-enabled/dirindex.conf
EXPOSE 80
