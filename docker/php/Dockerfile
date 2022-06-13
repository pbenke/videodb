FROM php:7.4.30-fpm-alpine3.16

# Install GD dependencies.
RUN apk --no-cache update \
    && apk --no-cache upgrade \
    && apk add --no-cache $PHPIZE_DEPS \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --enable-gd \
    && docker-php-ext-install -j$(nproc) gd

# intall mysql extention
RUN docker-php-ext-install mysqli
