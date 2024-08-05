# Usando a imagem oficial do PHP com Apache
FROM php:8.1-apache

# Instala as extensões necessárias
RUN docker-php-ext-install pdo pdo_mysql

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia os arquivos do projeto para o container
COPY . /var/www/html

# Define o diretório de trabalho
WORKDIR /var/www/html

# Define permissões
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Habilita o módulo rewrite do Apache
RUN a2enmod rewrite
