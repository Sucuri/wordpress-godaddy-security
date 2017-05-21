<?php
/**
 * Plugin Name:     GoDaddy Security
 * Plugin URI:      https://www.godaddy.com/
 * Description:     The GoDaddy Security plugin provides the website owner a good Activity Auditing, SiteCheck Remote Malware Scanning, and WordPress Integrity Check features. SiteCheck will check for malware, spam, blacklisting and other security issues like .htaccess redirects, hidden eval code, among other threats.
 * Author:          Sucuri Inc. <dcid@sucuri.net>
 * Author URI:      https://sucuri.net/
 * Text Domain:     godaddy-security
 * Domain Path:     /languages
 * Version:         0.1.0
 */

/**
 * Main file to control the plugin.
 *
 * The constant will be used in the additional PHP files to determine if the
 * code is being called from a legitimate interface or not. It is expected that
 * during the direct access of any of the extra PHP files the interpreter will
 * return a 403/Forbidden response and immediately exit the execution, this will
 * prevent unwanted access to code with unmet dependencies.
 *
 * @package   GoDaddy Security
 * @author    Daniel Cid   <dcid@sucuri.net>
 * @copyright Since 2010-2015 Sucuri Inc.
 * @license   Released under the GPL - see LICENSE file for details.
 * @link      https://wordpress.sucuri.net/
 * @since     File available since Release 0.1
 */
define('GDDYSEC_INIT', true);

/**
 * Plugin dependencies.
 *
 * List of required functions for the execution of this plugin, we are assuming
 * that this site was built on top of the WordPress project, and that it is
 * being loaded through a pluggable system, these functions most be defined
 * before to continue.
 *
 * @var array
 */
$gddysec_dependencies = array(
    'wp',
    'wp_die',
    'add_action',
    'remove_action',
    'wp_remote_get',
    'wp_remote_post',
);

// Terminate execution if any of the functions mentioned above is not defined.
foreach ($gddysec_dependencies as $dependency) {
    if (!function_exists($dependency)) {
        exit(0);
    }
}

/**
 * Plugin's constants.
 *
 * These constants will hold the basic information of the plugin, file/folder
 * paths, version numbers, read-only variables that will affect the functioning
 * of the rest of the code. The conditional will act as a container helping in
 * the readability of the code considering the total number of lines that this
 * file will have.
 */

/**
 * Unique name of the plugin through out all the code.
 */
define('GDDYSEC', 'gddysec');

/**
 * Current version of the plugin's code.
 */
define('GDDYSEC_VERSION', '0.1.0');

/**
 * The name of the Sucuri plugin main file.
 */
define('GDDYSEC_PLUGIN_FILE', 'gddysec.php');

/**
 * The name of the folder where the plugin's files will be located.
 *
 * Note that we are using the constant FILE instead of DIR because some
 * installations of PHP are either outdated or are not supporting the access to
 * that definition, to keep things simple we will select the name of the
 * directory name of the current file, then select the base name of that
 * directory.
 */
define('GDDYSEC_PLUGIN_FOLDER', basename(dirname(__FILE__)));
;
/**
 * The fullpath where the plugin's files will be located.
 */
define('GDDYSEC_PLUGIN_PATH', WP_PLUGIN_DIR . '/' . GDDYSEC_PLUGIN_FOLDER);

/**
 * The fullpath of the main plugin file.
 */
define('GDDYSEC_PLUGIN_FILEPATH', GDDYSEC_PLUGIN_PATH . '/' . GDDYSEC_PLUGIN_FILE);

/**
 * The local URL where the plugin's files and assets are served.
 */
define('GDDYSEC_URL', rtrim(plugin_dir_url(GDDYSEC_PLUGIN_FILEPATH), '/'));

/**
 * Remote URL where the public Sucuri API service is running.
 */
define('GDDYSEC_API', 'https://wordpress.sucuri.net/api/');

/**
 * Latest version of the public Sucuri API.
 */
define('GDDYSEC_API_VERSION', 'v1');

/**
 * The maximum quantity of entries that will be displayed in the audit logs page.
 */
define('GDDYSEC_AUDITLOGS_PER_PAGE', 50);

/**
 * The maximum quantity of buttons in the paginations.
 */
define('GDDYSEC_MAX_PAGINATION_BUTTONS', 20);

/**
 * The minimum quantity of seconds to wait before each filesystem scan.
 */
define('GDDYSEC_MINIMUM_RUNTIME', 10800);

/**
 * The life time of the cache for the results of the SiteCheck scans.
 */
define('GDDYSEC_SITECHECK_LIFETIME', 21600);

/**
 * The life time of the cache for the audit logs to help API perforamnce.
 */
define('GDDYSEC_AUDITLOGS_LIFETIME', 600);

/**
 * The maximum execution time of a HTTP request before timeout.
 */
define('GDDYSEC_MAX_REQUEST_TIMEOUT', 15);

/**
 * The maximum execution time for SiteCheck requests before timeout.
 */
define('GDDYSEC_MAX_SITECHECK_TIMEOUT', 60);

/* Load all classes before anything else. */
require_once('src/gddysec.lib.php');
require_once('src/request.lib.php');
require_once('src/fileinfo.lib.php');
require_once('src/cache.lib.php');
require_once('src/option.lib.php');
require_once('src/event.lib.php');
require_once('src/hook.lib.php');
require_once('src/api.lib.php');
require_once('src/mail.lib.php');
require_once('src/template.lib.php');
require_once('src/hardening.lib.php');
require_once('src/interface.lib.php');
require_once('src/dashboard.lib.php');
require_once('src/sitecheck.lib.php');
require_once('src/auditlogs.lib.php');
require_once('src/corefiles.lib.php');
require_once('src/failedlogins.lib.php');

/* Load handlers for main pages (settings). */
require_once('src/settings.php');
require_once('src/settings-handler.php');
require_once('src/settings-general.php');
require_once('src/settings-alerts.php');

/* Load global variables and triggers */
require_once('src/globals.php');
