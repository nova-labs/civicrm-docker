FROM drupal:7 as builder

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

RUN apt-get update && apt-get install -y wget git unzip zlib1g-dev libxml2-dev libcurl4-gnutls-dev libzip-dev mariadb-client

RUN docker-php-ext-install mysqli bcmath curl dom mbstring zip soap && \
	docker-php-ext-enable mysqli bcmath curl dom mbstring zip soap

# Download, build, and install the PhpRedis extension.
# We are specifying version 4.3.0 instead of version 5 - see issue at
# https://www.drupal.org/project/redis/issues/3074189
RUN pear config-set temp_dir /root/tmp && \
  echo '' | pecl install -o -f redis-4.3.0 && \
  rm -rf /root/tmp && \
  docker-php-ext-enable redis

RUN wget -qO- https://download.civicrm.org/civicrm-5.22.0-drupal.tar.gz | tar xz -C /var/www/html/sites/all/modules
RUN drush dl ctools views webform rules webform_civicrm civicrm_entity entity options_element redis advanced_help

RUN  wget -q -O /tmp/ext.zip https://lab.civicrm.org/extensions/mjwshared/-/archive/0.6/mjwshared-0.6.zip \
  && unzip /tmp/ext.zip -d /var/www/html/sites/all/modules/civicrm/ext/mjwshared \
  && rm /tmp/ext.zip \
  && wget -q -O /tmp/ext.zip https://lab.civicrm.org/extensions/stripe/-/archive/6.3.1/stripe-6.3.1.zip \
  && unzip /tmp/ext.zip -d /var/www/html/sites/all/modules/civicrm/ext/com.drastikbydesign.stripe \
  && rm /tmp/ext.zip \
  && wget -q -O /tmp/ext.zip https://github.com/adixon/ca.civicrm.logviewer/archive/1.2.zip \
  && unzip /tmp/ext.zip -d /var/www/html/sites/all/modules/civicrm/ext/ca.civicrm.logviewer \
  && rm /tmp/ext.zip \
  && wget -q -O /tmp/ext.zip https://github.com/civicrm/org.civicrm.volunteer/archive/22eb7ca6dbf99cddb6c43405a88f2cdf83ae7609.zip \
  && unzip /tmp/ext.zip -d /var/www/html/sites/all/modules/civicrm/ext/org.civicrm.volunteer \
  && rm /tmp/ext.zip \
  && wget -q -O /tmp/ext.zip https://github.com/civicrm/org.civicrm.contactlayout/archive/1.4.3.zip \
  && unzip /tmp/ext.zip -d /var/www/html/sites/all/modules/civicrm/ext/org.civicrm.contactlayout \
  && rm /tmp/ext.zip \
  && wget -q -O /tmp/ext.zip https://github.com/veda-consulting/uk.co.vedaconsulting.gdpr/archive/v2.8.zip \
  && unzip /tmp/ext.zip -d /var/www/html/sites/all/modules/civicrm/ext/uk.co.vedaconsulting.gdpr \
  && rm /tmp/ext.zip \
  && wget -q -O /tmp/ext.zip https://github.com/fuzionnz/nz.co.fuzion.transactional/archive/v1.0.3.zip \
  && unzip /tmp/ext.zip -d /var/www/html/sites/all/modules/civicrm/ext/nz.co.fuzion.transactional \
  && rm /tmp/ext.zip \
  && wget -q -O /tmp/ext.zip https://github.com/civicrm/org.civicrm.module.cividiscount/archive/3.8.1.zip \
  && unzip /tmp/ext.zip -d /var/www/html/sites/all/modules/civicrm/ext/org.civicrm.module.cividiscount \
  && rm /tmp/ext.zip \
  && wget -q -O /tmp/ext.zip https://lab.civicrm.org/extensions/civirules/-/archive/2.11/civirules-2.11.zip \
  && unzip /tmp/ext.zip -d /var/www/html/sites/all/modules/civicrm/ext/org.civicoop.civirules \
  && rm /tmp/ext.zip \
  && wget -q -O /tmp/ext.zip https://github.com/ginkgostreet/org.civicrm.angularprofiles/archive/v4.7.31-1.1.2.zip \
  && unzip /tmp/ext.zip -d /var/www/html/sites/all/modules/civicrm/ext/org.civicrm.angularprofiles \
  && rm /tmp/ext.zip

# copy in our config files
COPY settings.php /var/www/html/sites/default
COPY civicrm.settings.php /var/www/html/sites/default