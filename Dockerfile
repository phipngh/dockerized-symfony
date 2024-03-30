FROM php:8.2-fpm

ARG UID=${UID}
ARG GID=${GID}

RUN apt-get update && apt-get upgrade -y && apt-get install -y \
      unzip \
      git \
      libicu-dev \
      libpq-dev \
      libzip-dev \
    && pecl install grpc \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install \
      intl \
      opcache \
      zip \
      pdo_pgsql \
      pgsql \
    && docker-php-ext-enable grpc \
    && rm -rf /tmp/* \
    && rm -rf /var/list/apt/* \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN chmod +x /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

ARG APP_HOME=/srv/app
ARG USERNAME=www-data
ENV USERNAME=$USERNAME

# create document root, fix permissions for www-data user and change owner to www-data
RUN usermod -u $UID $USERNAME -d $APP_HOME && groupmod -g $GID $USERNAME \
    && mkdir -p $APP_HOME && chown -R $USERNAME:$USERNAME $APP_HOME

WORKDIR $APP_HOME

COPY . .

RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	composer symfony:dump-env prod; \
	composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync  

VOLUME /srv/app/var

WORKDIR $APP_HOME
