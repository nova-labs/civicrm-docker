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