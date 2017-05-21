<?php

/**
 * Plugin Name: GoDaddy Security
 * Description: The GoDaddy Security plugin provides the website owner a good Activity Auditing, SiteCheck Remote Malware Scanning, and WordPress Integrity Check features. SiteCheck will check for malware, spam, blacklisting and other security issues like .htaccess redirects, hidden eval code, among other threats.
 * Plugin URI: https://www.godaddy.com/
 * Author URI: https://sucuri.net/
 * Author: Sucuri Inc.
 * Version: 0.1.0
 *
 * PHP version 5
 *
 * @category   Library
 * @package    GoDaddy
 * @subpackage GoDaddySecurity
 * @author     Daniel Cid <dcid@sucuri.net>
 * @copyright  2017 Sucuri Inc. - GoDaddy LLC.
 * @license    https://www.godaddy.com/ - Proprietary
 * @link       https://wordpress.org/plugins/godaddy-security
 */

/**
 * Main file to control the plugin.
 *
 * The constant will be used in the additional PHP files to determine if the
 * code is being called from a legitimate interface or not. It is expected that
 * during the direct access of any of the extra PHP files the interpreter will
 * return a 403/Forbidden response and immediately exit the execution, this will
 * prevent unwanted access to code with unmet dependencies.
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

/* terminate execution if dependencies are not met */
foreach ($gddysec_dependencies as $dependency) {
    if (!function_exists($dependency)) {
        /* Report invalid access if possible. */
        header('HTTP/1.1 403 Forbidden');
        exit(0);
    }
}

/* check if installation path is available */
if (!defined('ABSPATH')) {
    /* Report invalid access if possible. */
    header('HTTP/1.1 403 Forbidden');
    exit(0);
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
 * The name of the folder where the plugin's files will be located.
 *
 * Note that we are using the constant FILE instead of DIR because some
 * installations of PHP are either outdated or are not supporting the access to
 * that definition, to keep things simple we will select the name of the
 * directory name of the current file, then select the base name of that
 * directory.
 */
define('GDDYSEC_PLUGIN_FOLDER', basename(dirname(__FILE__)));

/**
 * The fullpath where the plugin's files will be located.
 */
define('GDDYSEC_PLUGIN_PATH', WP_PLUGIN_DIR . '/' . GDDYSEC_PLUGIN_FOLDER);

/**
 * The local URL where the plugin's files and assets are served.
 */
define('GDDYSEC_URL', rtrim(plugin_dir_url(__FILE__), '/'));

/**
 * Remote URL where the public Sucuri API service is running.
 *
 * We will check if the constant was already set to allow developers to use
 * their own API service. This is useful both for the execution of the tests
 * as well as for website owners who do not want to send data to the Sucuri
 * servers.
 */
if (!defined('GDDYSEC_API_URL')) {
    define('GDDYSEC_API_URL', 'https://wordpress.sucuri.net/api/');
}

/**
 * Latest version of the public Sucuri API.
 */
define('GDDYSEC_API_VERSION', 'v1');

/**
 * The maximum quantity of entries that will be displayed in the last login page.
 */
define('GDDYSEC_LASTLOGINS_USERSLIMIT', 25);

/**
 * The life time of the cache for the audit logs to help API perforamnce.
 */
define('GDDYSEC_AUDITLOGS_LIFETIME', 600);

/**
 * The maximum quantity of entries that will be displayed in the audit logs page.
 */
define('GDDYSEC_AUDITLOGS_PER_PAGE', 50);

/**
 * The maximum quantity of buttons in the paginations.
 */
define('GDDYSEC_MAX_PAGINATION_BUTTONS', 16);

/**
 * Frequency of the file system scans in seconds.
 */
define('GDDYSEC_SCANNER_FREQUENCY', 10800);

/**
 * The life time of the cache for the results of the SiteCheck scans.
 */
define('GDDYSEC_SITECHECK_LIFETIME', 21600);

/**
 * The maximum execution time of a HTTP request before timeout.
 */
define('GDDYSEC_MAX_REQUEST_TIMEOUT', 5);

/**
 * Sets the text that will preceed the admin notices.
 *
 * If you have defined GDDYSEC_THROW_EXCEPTIONS to throw a generic exception
 * when an info or error alert is triggered, this text will be replaced by the
 * type of alert that was fired (either Info or Error respectively) which is
 * useful when you are executing code in a testing environment.
 */
define('GDDYSEC_ADMIN_NOTICE_PREFIX', '<b>Godaddy Security:</b>');

/* Fix missing server name in non-webview context */
if (!array_key_exists('SERVER_NAME', $_SERVER)) {
    $_SERVER['SERVER_NAME'] = 'localhost';
}

/* Load all classes before anything else. */
require_once 'src/gddysec.lib.php';
require_once 'src/request.lib.php';
require_once 'src/fileinfo.lib.php';
require_once 'src/cache.lib.php';
require_once 'src/option.lib.php';
require_once 'src/event.lib.php';
require_once 'src/hook.lib.php';
require_once 'src/api.lib.php';
require_once 'src/mail.lib.php';
require_once 'src/template.lib.php';
require_once 'src/hardening.lib.php';
require_once 'src/fsscanner.lib.php';
require_once 'src/interface.lib.php';
require_once 'src/auditlogs.lib.php';
require_once 'src/sitecheck.lib.php';
require_once 'src/integrity.lib.php';

/* Load page and ajax handlers */
require_once 'src/pagehandler.php';

/* Load handlers for main pages (lastlogins). */
require_once 'src/lastlogins.php';
require_once 'src/lastlogins-failed.php';
require_once 'src/lastlogins-blocked.php';

/* Load handlers for main pages (settings). */
require_once 'src/settings.php';
require_once 'src/settings-general.php';
require_once 'src/settings-scanner.php';
require_once 'src/settings-integrity.php';
require_once 'src/settings-alerts.php';
require_once 'src/settings-apiservice.php';

/* Load global variables and triggers */
require_once 'src/globals.php';

/**
 * Uninstalls the plugin, its settings and reverts the hardening.
 *
 * When the user decides to deactivate and/or uninstall the plugin it will call
 * this method to delete all traces of data inserted into the database by older
 * versions of the code, will remove the scheduled task, will delte the options
 * inserted into the sub-database associated to a multi-site installation, will
 * revert the hardening applied to the core directories, and will delete all the
 * security logs, cache and additional data stored in the storage directory.
 *
 * @return void
 */
function gddysecResetAndDeactivate()
{
    if (array_key_exists('wpdb', $GLOBALS)) {
        /* Delete all plugin related options from the database */
        $options = $GLOBALS['wpdb']->get_results(
            'SELECT option_id, option_name FROM ' . $GLOBALS['wpdb']->options
            . ' WHERE option_name LIKE "' . GDDYSEC . '%"'
        );

        foreach ($options as $option) {
            delete_site_option($option->option_name);
            delete_option($option->option_name);
        }
    }

    /* Delete scheduled task from the system */
    wp_clear_scheduled_hook('gddysec_scheduled_scan');

    /* Delete settings from the database if they exist */
    $options = GddysecOption::getDefaultOptionNames();
    foreach ($options as $option_name) {
        delete_site_option($option_name);
        delete_option($option_name);
    }

    /* Delete hardening in standard directories */
    GddysecHardening::dewhitelist('ms-files.php', 'wp-includes');
    GddysecHardening::dewhitelist('wp-tinymce.php', 'wp-includes');
    GddysecHardening::unhardenDirectory(WP_CONTENT_DIR);
    GddysecHardening::unhardenDirectory(WP_CONTENT_DIR . '/uploads');
    GddysecHardening::unhardenDirectory(ABSPATH . '/wp-includes');
    GddysecHardening::unhardenDirectory(ABSPATH . '/wp-admin');

    /* Delete cache files from disk */
    $fifo = new GddysecFileInfo();
    $fifo->ignore_files = false;
    $fifo->ignore_directories = false;
    $fifo->run_recursively = false;
    $directory = Gddysec::dataStorePath();
    $fifo->removeDirectoryTree($directory);
}

register_deactivation_hook(__FILE__, 'gddysecResetAndDeactivate');
