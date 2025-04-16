FROM php:8.1-apache

# Copy files into the default Apache directory
COPY . /var/www/html/

# Give permission to Apache
RUN chown -R www-data:www-data /var/www/html

# Enable Apache mod_rewrite if needed (optional for route handling)
RUN a2enmod rewrite
