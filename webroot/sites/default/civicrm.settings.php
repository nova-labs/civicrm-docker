<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 5                                                  |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2018                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 * CiviCRM Configuration File.
 * 
 * For documentation of options, see 
 * https://github.com/civicrm/civicrm-core/blob/master/templates/CRM/common/civicrm.settings.php.template
 * 
 */
global $civicrm_root, $civicrm_setting, $civicrm_paths;

/**
 * Content Management System (CMS) Host:
 */
if (!defined('CIVICRM_UF')) {
  if (getenv('CIVICRM_UF')) {
    define('CIVICRM_UF', getenv('CIVICRM_UF'));
  }
  else {
    define('CIVICRM_UF', 'Drupal');
  }
}

/**
 * Content Management System (CMS) Datasource:
 *
 * Datasource (DSN) format:
 *      define( 'CIVICRM_UF_DSN', 'mysql://cms_db_username:cms_db_password@db_server/cms_database?new_link=true');
 */
if (!defined('CIVICRM_UF_DSN') && CIVICRM_UF !== 'UnitTests') {
  define( 'CIVICRM_UF_DSN', getenv('CIVICRM_UF_DSN'));
}

// 

/**
 * CiviCRM Database Settings
 */
if (!defined('CIVICRM_DSN')) {
  if (CIVICRM_UF === 'UnitTests' && isset($GLOBALS['_CV']['TEST_DB_DSN'])) {
    define('CIVICRM_DSN', $GLOBALS['_CV']['TEST_DB_DSN']);
  }
  else {
    define('CIVICRM_DSN', getenv('CIVICRM_DSN'));
  }
}

/**
 * CiviCRM Logging Database
 */
if (!defined('CIVICRM_LOGGING_DSN')) {
  define('CIVICRM_LOGGING_DSN', CIVICRM_DSN);
}

/**
 * File System Paths:
 */

$civicrm_root = '/var/www/html/sites/all/modules/civicrm';
if (!defined('CIVICRM_TEMPLATE_COMPILEDIR')) {
  define( 'CIVICRM_TEMPLATE_COMPILEDIR', '/tmp/');
}

$civicrm_paths['civicrm.files'] = array(
  'url' => getenv('CIVICRM_UF_BASEURL') . 'sites/default/files/civicrm/',
  'path' => '/var/www/html/sites/default/files/civicrm/',
);

/**
 * Site URLs:
 */
if (!defined('CIVICRM_UF_BASEURL')) {
  define( 'CIVICRM_UF_BASEURL'      , getenv('CIVICRM_UF_BASEURL'));
}

/**
 * Define any CiviCRM Settings Overrides per http://wiki.civicrm.org/confluence/display/CRMDOC/Override+CiviCRM+Settings
 *
 * Uncomment and edit the below as appropriate.
 */
$civicrm_setting['Directory Preferences']['uploadDir'] = '[civicrm.files]/upload' ;
$civicrm_setting['Directory Preferences']['customFileUploadDir'] = '[civicrm.files]/custom';
$civicrm_setting['Directory Preferences']['imageUploadDir'] = '[civicrm.files]/persist' ;
$civicrm_setting['Directory Preferences']['extensionsDir'] = '[cms.root]/sites/default/civicrm-ext';
$civicrm_setting['URL Preferences']['userFrameworkResourceURL'] = '[civicrm.root]';
$civicrm_setting['URL Preferences']['imageUploadURL'] = '[civicrm.files]/persist/contribute';
$civicrm_setting['URL Preferences']['extensionsURL'] = '[cms.root]/sites/default/civicrm-ext';

$civicrm_paths['civicrm.private']['path']     = '/var/www/html/sites/default/files/civicrm';
$civicrm_paths['civicrm.log']['path'] = '/var/www/html/sites/default/files/civicrm/ConfigAndLog';
$civicrm_paths['civicrm.compile']['path'] = '/tmp';
$civicrm_paths['civicrm.phpCache']['path']    = '/tmp';
$civicrm_paths['civicrm.imageUpload']['*'] = '[civicrm.files]/persist/contribute';
$civicrm_paths['civicrm.assetCache']['*']  = '[civicrm.files]/dyn';

$civicrm_paths['cms.root']['path']         = '/var/www/html';
$civicrm_paths['cms.root']['url']          = getenv('CIVICRM_UF_BASEURL');
$civicrm_paths['civicrm.root']['*']        = '[cms.root]/sites/all/modules/civicrm';
$civicrm_paths['civicrm.files']['*']       = '[cms.root]/sites/default/files/civicrm';
$civicrm_paths['civicrm.extension']['*']   = '[civicrm.root]/ext';
$civicrm_paths['civicrm.packages']['*']    = '[civicrm.root]/packages';

 // Override the custom templates directory.
 // $civicrm_setting['Directory Preferences']['customTemplateDir'] = '/path/to/template-dir';

 // Override the Custom php path directory.
 // $civicrm_setting['Directory Preferences']['customPHPPathDir'] = '/path/to/custom-php-dir';
 
 // Override the Custom CiviCRM CSS URL
 // $civicrm_setting['URL Preferences']['customCSSURL'] = 'http://example.com/example-css-url' ;

 // Disable automatic download / installation of extensions
 $civicrm_setting['Extension Preferences']['ext_repo_url'] = false;

/**
 * If you are using any CiviCRM script in the bin directory that
 * requires authentication, then you also need to set this key.
 * We recommend using a 16-32 bit alphanumeric/punctuation key.
 * More info at http://wiki.civicrm.org/confluence/display/CRMDOC/Command-line+Script+Configuration
 */
if (!defined('CIVICRM_SITE_KEY')) {
  define( 'CIVICRM_SITE_KEY', getenv('CIVICRM_SITE_KEY'));
}

if (!defined('CIVICRM_DOMAIN_ID')) {
  define( 'CIVICRM_DOMAIN_ID', 1);
}

/**
 * Setting to define the environment in which this CiviCRM instance is running.
 * Note the setting here must be value from the option group 'Environment',
 * (see Administration > System Settings > Option Groups, Options beside Environment)
 * which by default has three option values: 'Production', 'Staging', 'Development'.
 * NB: defining a value for environment here prevents it from being set
 * via the browser.
 */
$civicrm_setting['domain']['environment'] = getenv('CIVICRM_ENVIRONMENT');

/**
 * Settings to enable external caching using a cache server.  This is an
 * advanced feature, and you should read and understand the documentation
 * before you turn it on. We cannot store these settings in the DB since the
 * config could potentially also be cached and we need to avoid an infinite
 * recursion scenario.
 *
 * @see http://civicrm.org/node/126
 */

/**
 * Settings to enable external caching using a cache server.  This is an
 * advanced feature, and you should read and understand the documentation
 * before you turn it on. We cannot store these settings in the DB since the
 * config could potentially also be cached and we need to avoid an infinite
 * recursion scenario.
 *
 * @see http://civicrm.org/node/126
 */

if (!defined('CIVICRM_DB_CACHE_CLASS')) {
  define('CIVICRM_DB_CACHE_CLASS', getenv('CIVICRM_DB_CACHE_CLASS'));
}

if (!defined('CIVICRM_DB_CACHE_HOST')) {
  define('CIVICRM_DB_CACHE_HOST', getenv('CIVICRM_DB_CACHE_HOST'));
}

if (!defined('CIVICRM_DB_CACHE_PORT')) {
    define('CIVICRM_DB_CACHE_PORT', getenv('CIVICRM_DB_CACHE_PORT'));
  }

if (!defined('CIVICRM_DB_CACHE_PASSWORD')) {
  define('CIVICRM_DB_CACHE_PASSWORD', getenv('CIVICRM_DB_CACHE_PASSWORD'));
}

if (!defined('CIVICRM_DB_CACHE_TIMEOUT')) {
  define('CIVICRM_DB_CACHE_TIMEOUT', getenv('CIVICRM_DB_CACHE_TIMEOUT'));
}

if (!defined('CIVICRM_DB_CACHE_PREFIX')) {
  define('CIVICRM_DB_CACHE_PREFIX', getenv('CIVICRM_DB_CACHE_PREFIX'));
}

/**
 * The cache system traditionally allowed a wide range of cache-keys, but some
 * cache-keys are prohibited by PSR-16.
 */
if (!defined('CIVICRM_PSR16_STRICT')) {
  define('CIVICRM_PSR16_STRICT', FALSE);
}

/**
 * Define how many times to retry a transaction when the DB hits a deadlock
 * (ie. the database is locked by another transaction). This is an
 * advanced setting intended for high-traffic databases & experienced developers/ admins.
 */
define('CIVICRM_DEADLOCK_RETRIES', 3);

/**
 * Configure MySQL to throw more errors when encountering unusual SQL expressions.
 */
if (!defined('CIVICRM_MYSQL_STRICT')) {
  define('CIVICRM_MYSQL_STRICT', FALSE );
}

/**
 * Specify whether the CRM_Core_BAO_Cache should use the legacy
 * direct-to-SQL-mode or the interim PSR-16 adapter.
 */
define('CIVICRM_BAO_CACHE_ADAPTER', 'CRM_Core_BAO_Cache_Psr16');

if (CIVICRM_UF === 'UnitTests') {
  if (!defined('CIVICRM_CONTAINER_CACHE')) define('CIVICRM_CONTAINER_CACHE', 'auto');
  if (!defined('CIVICRM_MYSQL_STRICT')) define('CIVICRM_MYSQL_STRICT', true);
}

/**
 *
 * Do not change anything below this line. Keep as is
 *
 */

$include_path = '.'           . PATH_SEPARATOR .
                $civicrm_root . PATH_SEPARATOR .
                $civicrm_root . DIRECTORY_SEPARATOR . 'packages' . PATH_SEPARATOR .
                get_include_path( );
if ( set_include_path( $include_path ) === false ) {
   echo "Could not set the include path<p>";
   exit( );
}

if (!defined('CIVICRM_CLEANURL')) {
  if ( function_exists('variable_get') && variable_get('clean_url', '0') != '0') {
    define('CIVICRM_CLEANURL', 1 );
  }
  elseif ( function_exists('config_get') && config_get('system.core', 'clean_url') != 0) {
    define('CIVICRM_CLEANURL', 1 );
  }
  else {
    define('CIVICRM_CLEANURL', 0);
  }
}

// force PHP to auto-detect Mac line endings
ini_set('auto_detect_line_endings', '1');

// make sure the memory_limit is at least 64 MB
$memLimitString = trim(ini_get('memory_limit'));
$memLimitUnit   = strtolower(substr($memLimitString, -1));
$memLimit       = (int) $memLimitString;
switch ($memLimitUnit) {
    case 'g': $memLimit *= 1024;
    case 'm': $memLimit *= 1024;
    case 'k': $memLimit *= 1024;
}
if ($memLimit >= 0 and $memLimit < 134217728) {
    ini_set('memory_limit', '128M');
}

require_once 'CRM/Core/ClassLoader.php';
CRM_Core_ClassLoader::singleton()->register();