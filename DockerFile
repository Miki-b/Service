# Use official PHP with Apache
FROM php:8.1-apache

# Copy your code into Apache's web root
COPY ./ /var/www/html/

# Expose port 80 (the web port)
EXPOSE 80
