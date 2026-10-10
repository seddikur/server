FROM php:8.2-fpm

# Все build + runtime зависимости в одном слое, ничего не purge'им,
# чтобы gd.so и imagick.so не потеряли libpng16 / libMagickWand в рантайме
RUN apt-get update && apt-get install -y --no-install-recommends \
        zlib1g-dev \
        libzip-dev \
        unzip \
        git \
        curl \
        libfreetype6-dev \
        libjpeg-dev \
        libpng-dev \
        libmagickwand-dev \
        libmagickcore-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP расширения
RUN docker-php-ext-install zip \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-install opcache \
    && docker-php-ext-install bcmath exif

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j "$(nproc)" gd

# Imagick
RUN mkdir -p /usr/src/php/ext/imagick \
    && curl -fsSL https://github.com/Imagick/imagick/archive/refs/tags/3.7.0.tar.gz \
       | tar xvz -C /usr/src/php/ext/imagick --strip 1 \
    && docker-php-ext-install imagick

# Composer — копируем из официального образа.
# Никаких curl getcomposer.org/installer (у тебя он падал по TLS)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# php.ini
ADD .docker/php/php.ini /usr/local/etc/php/conf.d/40-custom.ini

WORKDIR /app

CMD ["php-fpm"]