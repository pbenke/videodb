FROM php:7.4-fpm

# Set working directory
WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    mariadb-client \
    libpng-dev \
    libonig-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    ffmpeg
RUN pecl -q install xdebug  && \
    docker-php-ext-enable xdebug

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-configure gd --enable-gd --with-jpeg && \
docker-php-ext-install pdo_mysql mysqli exif pcntl gd

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ARG USER_NAME="root"
ARG UID=0
RUN if [ ${UID} > 0 ] ; then \
     apt-get update; \
     apt-get -y install sudo && \
     groupadd -g ${UID} ${USER_NAME} && \
     useradd -r -m -u ${UID} -g ${USER_NAME} ${USER_NAME} && echo "${USER_NAME}:${USER_NAME}" | chpasswd \
;fi

RUN sudo adduser ${USER_NAME} www-data

USER ${USER_NAME}

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
