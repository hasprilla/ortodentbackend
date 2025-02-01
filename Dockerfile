# Usa una imagen oficial de PHP con Nginx para Laravel
FROM php:8.2-fpm

# Instala dependencias de sistema
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev git unzip

# Instala extensiones de PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd pdo pdo_mysql

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establece el directorio de trabajo
WORKDIR /var/www

# Copia el código fuente
COPY . .

# Instala dependencias de Laravel
RUN composer install

# Establece permisos correctos
RUN chown -R www-data:www-data /var/www

EXPOSE 8000

CMD ["php-fpm"]
