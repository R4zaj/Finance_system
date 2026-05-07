# 1. Use the official PHP image with Apache built-in
FROM php:8.2-apache

# 2. Enable Apache mod_rewrite AND mod_headers (CRITICAL FOR YOUR API CORS)
RUN a2enmod rewrite headers

# 3. Install necessary PHP extensions (PDO MySQL is required for your finance_database)
RUN docker-php-ext-install pdo pdo_mysql

# 4. Set the working directory inside the container
WORKDIR /var/www/html

# 5. Copy all your local project files into the container
COPY . /var/www/html/

# 6. Set the correct permissions so Apache can read/write files
RUN chown -R www-data:www-data /var/www/html

# 7. CRITICAL FOR RENDER: Configure Apache to listen on Render's dynamic PORT
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 8. Start the Apache web server
CMD ["apache2-foreground"]
