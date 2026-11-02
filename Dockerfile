# Etapa 1: instala las dependencias de Composer (Slim, phpdotenv, etc.)
FROM composer:2 AS vendor
WORKDIR /app
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs

# Etapa 2: PHP 8.2 + Apache sirviendo la carpeta public/
FROM php:8.2-apache

# Driver PDO para MySQL y mod_rewrite
RUN docker-php-ext-install pdo_mysql && a2enmod rewrite

# Apache apunta a /public y manda todas las rutas a index.php (Slim)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && printf '<Directory /var/www/html/public>\n  AllowOverride All\n  Require all granted\n  FallbackResource /index.php\n</Directory>\n' > /etc/apache2/conf-available/app.conf \
 && a2enconf app

# Que PHP llene $_ENV con las variables de entorno de Dokploy
RUN echo "variables_order = EGPCS" > /usr/local/etc/php/conf.d/env.ini

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html

# Carpeta de logs con permisos para Apache
RUN mkdir -p logs && chown -R www-data:www-data logs

EXPOSE 80
