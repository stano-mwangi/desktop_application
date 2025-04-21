# Use PHP 8.1 with FPM for production
FROM php:8.1-fpm

# Install system dependencies and Node.js for Livewire assets
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    zip unzip git curl \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    nginx \
    sqlite3 libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_sqlite pdo_mysql mbstring exif pcntl bcmath gd zip \
    && curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application code
COPY . /var/www

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Install frontend dependencies and build assets (for Livewire)
RUN npm install && npm run build && rm -rf node_modules

# Create SQLite database file
RUN touch /var/www/database/database.sqlite \
    && chown www-data:www-data /var/www/database/database.sqlite \
    && chmod 664 /var/www/database/database.sqlite

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Copy Nginx configuration
COPY ./nginx.conf /etc/nginx/sites-available/default

# Configure environment
RUN if [ ! -f .env ]; then cp .env.example .env; fi \
    && sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env \
    && sed -i 's/DB_DATABASE=.*/DB_DATABASE=\/var\/www\/database\/database.sqlite/' .env \
    && php artisan key:generate --ansi \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan migrate --force --no-interaction

# Expose port (Render uses PORT env variable, default 10000)
EXPOSE $PORT

# Start PHP-FPM and Nginx
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]