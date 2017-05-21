<?php

/**
 * Code related to the interface.lib.php interface.
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

if (!defined('GDDYSEC_INIT') || GDDYSEC_INIT !== true) {
    if (!headers_sent()) {
        /* Report invalid access if possible. */
        header('HTTP/1.1 403 Forbidden');
    }
    exit(1);
}

/**
 * Plugin initializer.
 *
 * Define all the required variables, script, styles, and basic functions needed
 * when the site is loaded, not even the administrator panel but also the front
 * page, some bug-fixes will/are applied here for sites behind a proxy, and
 * sites with old versions of the premium plugin (deprecated on July, 2014).
 *
 * @category   Library
 * @package    GoDaddy
 * @subpackage GoDaddySecurity
 * @author     Daniel Cid <dcid@sucuri.net>
 * @copyright  2017 Sucuri Inc. - GoDaddy LLC.
 * @license    https://www.godaddy.com/ - Proprietary
 * @link       https://wordpress.org/plugins/godaddy-security
 */
class GddysecInterface
{
    /**
     * Initialization code for the plugin.
     *
     * @return void
     */
    public static function initialize()
    {
        GddysecEvent::installScheduledTask();
    }

    /**
     * Define which javascript and css files will be loaded in the header of the
     * plugin pages, only when the administrator panel is accessed.
     *
     * @return void
     */
    public static function enqueueScripts()
    {
        wp_register_style(
            'gddysec',
            GDDYSEC_URL . '/inc/css/styles.css',
            array(/* empty */),
            '3eeb7af'
        );
        wp_enqueue_style('gddysec');

        wp_register_script(
            'gddysec',
            GDDYSEC_URL . '/inc/js/scripts.js',
            array(/* empty */),
            '81f6bb4'
        );
        wp_enqueue_script('gddysec');
    }

    /**
     * Create a folder in the WordPress upload directory where the plugin will
     * store all the temporal or dynamic information.
     *
     * @return void
     */
    public static function createStorageFolder()
    {
        $directory = Gddysec::dataStorePath();

        if (!file_exists($directory)) {
            @mkdir($directory, 0755, true);
        }

        if (file_exists($directory)) {
            // Create last-logins datastore file.
            gddysec_lastlogins_datastore_exists();

            // Create a htaccess file to deny access from all.
            if (!GddysecHardening::isHardened($directory)) {
                GddysecHardening::hardenDirectory($directory);
            }

            // Create an index.html to avoid directory listing.
            if (!file_exists($directory . '/index.html')) {
                @file_put_contents(
                    $directory . '/index.html',
                    '<!-- Prevent the directory listing. -->'
                );
            }
        }
    }

    /**
     * Display alerts and execute pre-checks before every page.
     *
     * This method verifies if the visibility of the requested page is allowed
     * for the current user in session which usually needs to be granted admin
     * privileges to access the plugin's tools. It also checks if the required
     * SPL library is available and if the settings file is writable.
     *
     * @return void
     */
    public static function startupChecks()
    {
        self::checkPageVisibility();

        self::noticeAfterUpdate();

        if (!GddysecFileInfo::isSplAvailable()) {
            /* display a warning when system dependencies are not met */
            self::error('The plugin requires PHP 5 >= 5.3.0 - OR - PHP 7');
        }

        $filename = GddysecOption::optionsFilePath();

        if (!is_writable($filename)) {
            self::error(
                sprintf(
                    'Storage is not writable: <code>%s</code>',
                    $filename /* absolute path of the settings file */
                )
            );
        }
    }

    /**
     * Do something if the plugin was updated.
     *
     * Check if an option exists with the version number of the plugin, if the
     * number is different than the number defined in the constant that comes
     * with this code then we can consider this as an update, in which case we
     * will execute certain actions and/or display some messages.
     *
     * @codeCoverageIgnore
     *
     * @return void
     */
    public static function noticeAfterUpdate()
    {
        /* get version of the plugin that was previously installed */
        $version = GddysecOption::getOption(':plugin_version');

        /* use simple comparison to force type cast. */
        if ($version == GDDYSEC_VERSION) {
            return;
        }

        /* update the version number in the plugin settings. */
        GddysecOption::updateOption(':plugin_version', GDDYSEC_VERSION);

        /**
         * Suggest re-activation of the API communication.
         *
         * Check if the API communication has been disabled due to issues with
         * the previous version of the code, in this case we will display a
         * message at the top of the admin dashboard suggesting the user to
         * enable it once again expecting to see have a better performance with
         * the new code.
         */
        if (GddysecOption::isDisabled(':api_service')) {
            self::info('API service communication is disabled, if you just updated the plugin this might be a good opportunity to test this feature once again with the new code. Enable it again from the "API Service" panel located in the settings page.');
        }
    }

    /**
     * Check whether a user has the permissions to see a page from the plugin.
     *
     * @codeCoverageIgnore
     *
     * @return void
     */
    public static function checkPageVisibility()
    {
        if (!function_exists('current_user_can') || !current_user_can('manage_options')) {
            Gddysec::throwException('Access denied; cannot manage options');
            wp_die('Access denied by GoDaddy Security WordPress plugin');
        }
    }

    /**
     * Verify the nonce of the previous page after a form submission. If the
     * validation fails the execution of the script will be stopped and a dead page
     * will be printed to the client using the official WordPress method.
     *
     * @codeCoverageIgnore
     *
     * @return bool True if the nonce is valid, false otherwise.
     */
    public static function checkNonce()
    {
        if (!empty($_POST)) {
            $nonce_name = 'gddysec_page_nonce';
            $nonce_value = GddysecRequest::post($nonce_name, '_nonce');

            if (!$nonce_value || !wp_verify_nonce($nonce_value, $nonce_name)) {
                Gddysec::throwException('Nonce is invalid');
                wp_die('WordPress Nonce verification failed, try again going back and checking the form.');
                return false;
            }
        }

        return true;
    }

    /**
     * Prints a HTML alert in the WordPress admin interface.
     *
     * @codeCoverageIgnore
     *
     * @param  string $type    The type of alert, it can be either Updated or Error.
     * @param  string $message The message that will be printed in the alert.
     * @return void
     */
    private static function adminNotice($type = 'updated', $message = '')
    {
        $display_notice = true;

        /**
         * Do not render notice during user authentication.
         *
         * There are some special cases when the error or warning messages
         * should not be rendered to the end user because it may break the
         * default functionality of the request handler. For instance, rendering
         * an HTML alert like this when the user authentication process is
         * executed may cause a "headers already sent" error.
         */
        if (!empty($_POST)
            && GddysecRequest::post('log')
            && GddysecRequest::post('pwd')
            && GddysecRequest::post('wp-submit')
        ) {
            $display_notice = false;
        }

        /* display the HTML notice to the current user */
        if ($display_notice === true && !empty($message)) {
            $message = GDDYSEC_ADMIN_NOTICE_PREFIX . "\x20" . $message;

            Gddysec::throwException($message, $type);

            echo GddysecTemplate::getSection(
                'notification-admin',
                array(
                    'AlertType' => $type,
                    'AlertUnique' => rand(100, 999),
                    'AlertMessage' => $message,
                )
            );
        }
    }

    /**
     * Prints a HTML alert of type ERROR in the WordPress admin interface.
     *
     * @param  string $msg The message that will be printed in the alert.
     * @return void
     */
    public static function error($msg = '')
    {
        self::adminNotice('error', $msg);
        return false; /* assume failure */
    }

    /**
     * Prints a HTML alert of type INFO in the WordPress admin interface.
     *
     * @param  string $msg The message that will be printed in the alert.
     * @return void
     */
    public static function info($msg = '')
    {
        self::adminNotice('updated', $msg);
        return true; /* assume success */
    }
}
