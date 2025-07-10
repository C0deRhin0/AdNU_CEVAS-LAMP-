FROM php:8.1-apache

MAINTAINER Intern <intern@adnu-cevas.local>

WORKDIR /var/www/html

RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy app files
COPY . /var/www/html

# Copy custom apache2.conf
COPY apache2.conf /etc/apache2/apache2.conf

# Set permissions (handled at build time)
RUN chown -R www-data:www-data /var/www/html && \
    find /var/www/html -type d -exec chmod 755 {} \; && \
    find /var/www/html -type f -exec chmod 644 {} \;

# Enable .htaccess support and mod_rewrite
RUN a2enmod rewrite

EXPOSE 80