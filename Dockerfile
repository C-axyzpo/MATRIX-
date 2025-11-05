# Use an official PHP image that includes Apache
FROM php:8.2-apache

# Enable common PHP extensions (if needed)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy all files from your local project to the web directory
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Expose port 80 (standard HTTP)
EXPOSE 80

# Start Apache automatically when the container runs
CMD ["apache2-foreground"]
