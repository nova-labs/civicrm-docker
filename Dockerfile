# from https://www.drupal.org/docs/8/system-requirements/drupal-8-php-requirements
FROM php:7.3-apache-stretch AS builder
# TODO switch to buster once https://github.com/docker-library/php/issues/865 is resolved in a clean way (either in the PHP image or in PHP itself)

# install the PHP extensions we need
RUN set -eux; \
	\
	if command -v a2enmod; then \
		a2enmod rewrite; \
	fi; \
	\
	savedAptMark="$(apt-mark showmanual)"; \
	\
	apt-get update; \
	apt-get install -y --no-install-recommends \
		libfreetype6-dev \
		libjpeg-dev \
		libpng-dev \
		libpq-dev \
		libzip-dev \
    zlib1g-dev \
    libxml2-dev \
    libcurl4-gnutls-dev \
	; \
	\
	docker-php-ext-configure gd \
		--with-freetype-dir=/usr \
		--with-jpeg-dir=/usr \
		--with-png-dir=/usr \
	; \
	\
	docker-php-ext-install -j "$(nproc)" \
		gd \
		opcache \
		pdo_mysql \
		pdo_pgsql \
		zip \
    mysqli \
    bcmath \
    curl \
    dom \
    mbstring \
    soap \
	; \
	\
# Download, build, and install the PhpRedis extension.
# We are specifying version 4.3.0 instead of version 5 - see issue at
# https://www.drupal.org/project/redis/issues/3074189
  pear config-set temp_dir /root/tmp; \
  echo '' | pecl install -o -f redis-4.3.0; \
  rm -rf /root/tmp  \
  docker-php-ext-enable redis; \
# reset apt-mark's "manual" list so that "purge --auto-remove" will remove all build dependencies
	apt-mark auto '.*' > /dev/null; \
	apt-mark manual $savedAptMark; \
	ldd "$(php -r 'echo ini_get("extension_dir");')"/*.so \
		| awk '/=>/ { print $3 }' \
		| sort -u \
		| xargs -r dpkg-query -S \
		| cut -d: -f1 \
		| sort -u \
		| xargs -rt apt-mark manual; \
	\
	apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
	rm -rf /var/lib/apt/lists/*

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
		echo 'opcache.memory_consumption=128'; \
		echo 'opcache.interned_strings_buffer=8'; \
		echo 'opcache.max_accelerated_files=4000'; \
		echo 'opcache.revalidate_freq=60'; \
		echo 'opcache.fast_shutdown=1'; \
	} > /usr/local/etc/php/conf.d/opcache-recommended.ini

WORKDIR /var/www/html

FROM builder AS cli
# Add various helpful tools for building/maintaining site
COPY drush8.phar /usr/local/bin/drush
COPY cv.phar /usr/local/bin/cv
COPY civix.phar /usr/local/bin/civix
RUN chmod +x /usr/local/bin/drush && \
	chmod +x /usr/local/bin/cv && \
  chmod +x /usr/local/bin/civix
ARG COMPOSER_AUTH
ENV COMPOSER_AUTH $COMPOSER_AUTH
ENV COMPOSER_CACHE_DIR=/tmp
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY --from=redis /usr/local/bin/redis-cli /usr/local/bin/redis-cli
COPY --from=mariadb /usr/bin/mysql /usr/local/bin/mysql