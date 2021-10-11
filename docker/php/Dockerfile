FROM php:8.0.1-fpm-alpine

RUN set -xe && apk update && apk upgrade

RUN apk add --no-cache \
    shadow \
    libzip-dev \
    libintl \
    icu \
    icu-dev \
    bash \
    curl \
    libmcrypt-dev \
    libxml2-dev \
    freetype-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    postgresql-dev \
    pcre-dev \
    git \
    g++ \
    make \
    autoconf \
    openssh

RUN  docker-php-ext-configure gd \
    --enable-gd \
    --with-jpeg \
    --with-freetype \
  && NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) \
  && docker-php-ext-install -j${NPROC} gd

RUN docker-php-ext-install intl opcache mysqli pdo_mysql zip soap sockets exif

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install sockets \
    && docker-php-ext-enable sockets \
    && docker-php-ext-install pcntl \
    && docker-php-ext-enable pcntl


# composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# change uid for nginx user to avoid problems with host permissions
# change uid for nginx user to avoid problems with host permissions
ARG HOST_USER_ID
ARG HOST_GROUP_ID
ARG BUILD_TARGET

ENV TARGET_UID ${HOST_USER_ID:-1000}
ENV TARGET_GID ${HOST_GROUP_ID:-1000}

RUN if [ ! -z "$TARGET_UID" ] ; then usermod -u $TARGET_UID www-data ; fi
RUN if [ ! -z "$TARGET_GID" ] ; then groupmod -g $TARGET_GID www-data ; fi
RUN mkdir /var/session && chown www-data:www-data /var/session

WORKDIR /var/www

ENV TARGET ${BUILD_TARGET:-'dev'}

USER ${TARGET_UID}:${TARGET_GID}