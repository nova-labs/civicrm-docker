<?php

/**
 * @file
 * Drupal site-specific configuration file.
 * For documentation of options, see
 * https://git.drupalcode.org/project/drupal/blob/7.x/sites/default/default.settings.php
 */

/**
 * Database settings:
 */
$databases = array (
  'default' => 
  array (
    'default' => 
    array (
      'database' => getenv('DRUPAL_DATABASE'),
      'username' => getenv('DRUPAL_DB_USERNAME'),
      'password' => getenv('DRUPAL_DB_PASSWORD'),
      'host' => getenv('DRUPAL_DB_HOST'),
      'port' => getenv('DRUPAL_DB_PORT'),
      'driver' => 'mysql',
      'prefix' => getenv('DRUPAL_DB_PREFIX'),
    ),
  ),
);

/**
 * Access control for update.php script.
 */
$update_free_access = FALSE;

/**
 * Salt for one-time login links and cancel links, form tokens, etc.
 */
$settings['hash_salt'] = getenv('DRUPAL_HASH_SALT');

/**
 * Base URL (optional).
 */
$base_url = getenv('CIVICRM_UF_BASEURL');

/**
 * PHP settings:
 */

/**
 * Some distributions of Linux (most notably Debian) ship their PHP
 * installations with garbage collection (gc) disabled. Since Drupal depends on
 * PHP's garbage collection for clearing sessions, ensure that garbage
 * collection occurs by using the most common settings.
 */
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);

/**
 * Set session lifetime (in seconds), i.e. the time from the user's last visit
 * to the active session may be deleted by the session garbage collector. When
 * a session is deleted, authenticated users are logged out, and the contents
 * of the user's $_SESSION variable is discarded.
 */
ini_set('session.gc_maxlifetime', getenv('DRUPAL_SESSION_GC_MAX_LIFETIME'));

/**
 * Set session cookie lifetime (in seconds), i.e. the time from the session is
 * created to the cookie expires, i.e. when the browser is expected to discard
 * the cookie. The value 0 means "until the browser is closed".
 */
ini_set('session.cookie_lifetime', getenv('DRUPAL_SESSION_COOKIE_LIFETIME'));

/**
 * Reverse Proxy Configuration:
 */
# $conf['reverse_proxy'] = TRUE;

/**
 * Specify every reverse proxy IP address in your environment.
 * This setting is required if $conf['reverse_proxy'] is TRUE.
 */
# $conf['reverse_proxy_addresses'] = array('a.b.c.d', ...);

/**
 * Set this value if your proxy server sends the client IP in a header
 * other than X-Forwarded-For.
 */
# $conf['reverse_proxy_header'] = 'HTTP_X_CLUSTER_CLIENT_IP';

/**
 * CSS/JS aggregated file gzip compression:
 *
 * By default, when CSS or JS aggregation and clean URLs are enabled Drupal will
 * store a gzip compressed (.gz) copy of the aggregated files. If this file is
 * available then rewrite rules in the default .htaccess file will serve these
 * files to browsers that accept gzip encoded content. This allows pages to load
 * faster for these users and has minimal impact on server load. If you are
 * using a webserver other than Apache httpd, or a caching reverse proxy that is
 * configured to cache and compress these files itself you may want to uncomment
 * one or both of the below lines, which will prevent gzip files being stored.
 */
# $conf['css_gzip_compression'] = FALSE;
# $conf['js_gzip_compression'] = FALSE;

/**
 * Fast 404 pages:
 */
$conf['404_fast_paths_exclude'] = '/\/(?:styles)|(?:system\/files)\//';
$conf['404_fast_paths'] = '/\.(?:txt|png|gif|jpe?g|css|js|ico|swf|flv|cgi|bat|pl|dll|exe|asp)$/i';
$conf['404_fast_html'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL "@path" was not found on this server.</p></body></html>';

/**
 * Authorized file system operations:
 * @see http://drupal.org/node/244924
 */
$conf['allow_authorize_operations'] = FALSE;

/**
 * CSS identifier double underscores allowance:
 */
$conf['allow_css_double_underscores'] = TRUE;

/**
 * The default list of directories that will be ignored by Drupal's file API.
 */
$conf['file_scan_ignore_directories'] = array(
  'node_modules',
  'bower_components',
);

$conf['redis_client_interface'] = 'PhpRedis';
$conf['redis_client_host'] = getenv('DRUPAL_REDIS_HOST');
$conf['redis_client_port'] = getenv('DRUPAL_REDIS_PORT');
$conf['redis_client_password'] = getenv('DRUPAL_REDIS_PASSWORD');
$conf['lock_inc'] = 'sites/all/modules/redis/redis.lock.inc';
$conf['path_inc'] = 'sites/all/modules/redis/redis.path.inc';
$conf['cache_backends'][] = 'sites/all/modules/redis/redis.autoload.inc';
$conf['cache_default_class'] = 'Redis_Cache';
$conf['cache_prefix'] = getenv('DRUPAL_REDIS_PREFIX');