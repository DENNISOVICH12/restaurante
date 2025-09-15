FROM php:8.2-fpm

# Variables para el usuario dentro del contenedor
ARG WWWGROUP=www-data
ARG WWWUSER=www-data

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    PATH="/var/www/html/vendor/bin:$PATH"

# Dependencias del sistema necesarias para Laravel + PostgreSQL
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    vim \
    && docker-php-ext-configure zip \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Directorio de trabajo por defecto para Laravel
WORKDIR /var/www/html

# Ajustar permisos para el usuario web
RUN chown -R ${WWWUSER}:${WWWGROUP} /var/www/html

CMD ["php-fpm"]
