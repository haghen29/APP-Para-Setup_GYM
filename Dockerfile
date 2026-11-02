# =====================================================================
# Imagen de la landing de Codegym Net (Slim 4 + PHP + Apache)
# =====================================================================

# --- Etapa 1: instalar las dependencias de Composer (carpeta vendor/) ---
FROM composer:2 AS dependencias
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# --- Etapa 2: la imagen final con PHP y Apache ---
FROM php:8.3-apache

# Extensiones de PHP para conectarse a MySQL con PDO
RUN docker-php-ext-install pdo pdo_mysql

# Activar mod_rewrite (lo usa public/.htaccess) y apuntar Apache a la carpeta public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN a2enmod rewrite \
 && sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Copiar el código y la carpeta vendor/ de la etapa anterior
COPY . .
COPY --from=dependencias /app/vendor ./vendor

# La carpeta logs/ tiene que poder escribirla Apache (www-data)
RUN mkdir -p logs && chown -R www-data:www-data logs

# Script de arranque: espera la base, corre las migraciones y levanta Apache
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
