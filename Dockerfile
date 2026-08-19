FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev zip unzip git curl

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Default post_max_size (8M) is smaller than the face-recognition image_base64
# payload the AI service is built to accept (up to 15MB decoded, ~20MB as
# base64 + JSON). Without this, a larger phone photo gets silently dropped by
# PHP before Laravel ever sees it - image_base64 validation then fails with
# Laravel's generic {"message": ...} shape instead of a useful error.
RUN { \
        echo 'post_max_size=20M'; \
        echo 'upload_max_filesize=20M'; \
    } > /usr/local/etc/php/conf.d/uploads.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN cp .env.example .env && php artisan key:generate

# public/storage is a symlink to storage/app/public (where uploaded photos,
# worksite logos, etc. actually live). It's gitignored, so a fresh image
# build never has it, and every rebuild of this container was silently
# losing it - any previously-working image URL then 404s until someone
# manually re-runs `storage:link` inside the container. Baking it into the
# image here means it survives every rebuild automatically.
RUN php artisan storage:link

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
