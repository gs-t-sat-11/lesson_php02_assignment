FROM php:8.2-apache

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy the application
COPY . /var/www/html/

# Update Apache configuration to use port 8080
RUN sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/g' /etc/apache2/sites-available/000-default.conf

# Give ownership to www-data
RUN chown -R www-data:www-data /var/www/html

# Start Apache in foreground
CMD ["apache2-foreground"]