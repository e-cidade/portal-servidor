FROM php:8.4-apache

ARG WWWUSER
ARG WWWGROUP

WORKDIR /var/www/html
COPY . /var/www/html

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    libpq-dev libicu-dev libjpeg-dev libfreetype6-dev \
    libmagickwand-dev libmagickcore-dev imagemagick \
    && rm -rf /var/lib/apt/lists/*

# Instala extensões do PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo pdo_pgsql pdo_mysql gd mbstring zip intl opcache bcmath && \
    a2enmod rewrite

# Instala Redis e Imagick (após dependências do sistema)
RUN pecl install redis && \
    pecl install imagick && \
    docker-php-ext-enable redis imagick

# Instala Node.js 22.x
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs && \
    node -v && npm -v

# Instala o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copia configurações do PHP
COPY ./php/local.ini /usr/local/etc/php/conf.d/local.ini

# Garante que o usuário dentro do container tem o mesmo UID/GID do host
RUN groupmod -o -g ${WWWGROUP} www-data \
    && usermod -o -u ${WWWUSER} www-data

# Copia o entrypoint e ajusta permissões
COPY ./entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copia a configuração do Apache
COPY ./apache/laravel.conf /etc/apache2/sites-available/000-default.conf

# Marca o diretório como seguro para o Git
RUN git config --global --add safe.directory /var/www/html

RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache


