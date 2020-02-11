FROM drupal:7 as builder

COPY drush8.phar /usr/local/bin/drush
COPY cv.phar /usr/local/bin/cv
RUN chmod +x /usr/local/bin/drush && \
	chmod +x /usr/local/bin/cv

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
RUN drush dl ctools views webform rules webform_civicrm civicrm_entity entity options_element redis

#RUN cat composer.json \
#  | jq '.extra.civicrm.extensions."mjwshared" = "https://lab.civicrm.org/extensions/mjwshared/-/archive/master/mjwshared-master.zip"' \
#  | jq '.extra.civicrm.extensions."com.drastikbydesign.stripe" = "https://lab.civicrm.org/extensions/stripe/-/archive/master/stripe-master.zip"' \
#  | jq '.extra.civicrm.extensions."ca.civicrm.logviewer" = "https://github.com/adixon/ca.civicrm.logviewer/archive/master.zip"' \
#  | jq '.extra.civicrm.extensions."org.civicrm.volunteer" = "https://github.com/civicrm/org.civicrm.volunteer/archive/master.zip"' \
#  | jq '.extra.civicrm.extensions."org.civicrm.contactlayout" = "https://github.com/civicrm/org.civicrm.contactlayout/archive/master.zip"' \
#  | jq '.extra.civicrm.extensions."uk.co.vedaconsulting.gdpr" = "https://github.com/veda-consulting/uk.co.vedaconsulting.gdpr/archive/master.zip"' \
#  | jq '.extra.civicrm.extensions."nz.co.fuzion.transactional" = "https://github.com/fuzionnz/nz.co.fuzion.transactional/archive/master.zip"' \
#  | jq '.extra.civicrm.extensions."org.civicrm.module.cividiscount" = "https://github.com/civicrm/org.civicrm.module.cividiscount/archive/master.zip"' \
#  | jq '.extra.civicrm.extensions."org.civicoop.civirules" = "https://lab.civicrm.org/extensions/civirules/-/archive/master/civirules-master.zip"' \
#  | jq '.extra.civicrm.extensions."org.civicrm.angularprofiles" = "https://github.com/ginkgostreet/org.civicrm.angularprofiles/archive/master.zip"' \
#  | jq '.extra.civicrm.extensions."" = ""' \
#  > /tmp/composer.json && mv /tmp/composer.json composer.json
#RUN composer civicrm

# copy in our config files
COPY settings.php /var/www/html/sites/default
COPY civicrm.settings.php /var/www/html/sites/default