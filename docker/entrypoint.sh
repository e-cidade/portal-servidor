#!/bin/bash
set -e
# Copia o .env se não existir
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Instala dependências PHP se a pasta vendor não existir
if [ ! -d vendor ]; then
    composer install --no-interaction --prefer-dist
fi


if [ -d vendor ]; then
    composer update
fi

# Instala dependências JS e faz build se a pasta node_modules não existir
if [ ! -d node_modules ]; then
    npm install
fi

if [ -d node_modules ]; then
    npm update
fi

if [ ! -d storage/logs ]; then
    mkdir storage/logs
fi

if [ ! -d var/www/html/vendor/mpdf/mpdf/tmp ]; then

    mkdir var/www/html/vendor/mpdf/mpdf/tmp
    chown -R www-data:www-data /var/www/html/vendor/mpdf/mpdf/tmp
    chmod -R 775 /var/www/html/vendor/mpdf/mpdf/tmp

fi


if [ -d var/www/html/vendor/mpdf/mpdf/tmp ]; then

    chown -R www-data:www-data /var/www/html/vendor/mpdf/mpdf/tmp
    chmod -R 775 /var/www/html/vendor/mpdf/mpdf/tmp

fi

npm run build

# Ajusta permissões
chown -R $USER:$USER storage bootstrap/cache public/build || true
chmod -R 777 storage/logs
mkdir -p storage/framework/sessions storage/framework/cache storage/framework/views storage/app/public/anexos
mkdir -p public/build public/img public/anexos
chown -R www-data:www-data storage bootstrap/cache public/build public/img storage/app/public/anexos
chmod -R 775 storage bootstrap/cache public/build public/img public/anexos storage/app/public/anexos

git config --global --add safe.directory /var/www/html
git config core.fileMode false

php artisan key:generate
php artisan storage:link
php artisan migrate --force

# Executa o comando do container (ex.: php-fpm)
exec "$@"