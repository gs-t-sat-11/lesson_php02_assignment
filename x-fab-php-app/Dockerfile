# PHP 8.2 with Apache
FROM php:8.2-apache

# Install PHP extensions and tools
RUN apt-get update && apt-get install -y \
    curl \
    net-tools \
    && docker-php-ext-install pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers

# Copy custom Apache configuration
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Set ServerName to avoid warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy application files
COPY . /var/www/html/

# Remove development files
RUN rm -f /var/www/html/test_db.php \
    /var/www/html/check_mysql.php \
    /var/www/html/clear_cache.php \
    /var/www/html/deploy_to_xampp.sh \
    /var/www/html/Dockerfile \
    /var/www/html/apache-config.conf \
    /var/www/html/.dockerignore

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Create a startup script
RUN echo '#!/bin/bash\n\
echo "Starting Apache on port 8080..."\n\
echo "Testing port binding..."\n\
netstat -tuln | grep 8080 || echo "Port 8080 not yet bound"\n\
exec apache2-foreground' > /start.sh \
    && chmod +x /start.sh

# Expose port 8080
EXPOSE 8080

# Start Apache
CMD ["/start.sh"]