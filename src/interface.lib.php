<?php

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
 * sites with old versions of the premium plugin (that was deprecated at
 * July/2014).
 */
class GddysecInterface
{
    /**
     * Initialization code for the plugin.
     *
     * The initial variables and information needed by the plugin during the
     * execution of other functions will be generated. Things like the real IP
     * address of the client when it has been forwarded or it's behind an external
     * service like a Proxy.
     *
     * @return void
     */
    public static function initialize()
    {
        GddysecEvent::schedule_task(false);
    }

    /**
     * Define which javascript and css files will be loaded in the header of the
     * plugin pages, only when the administrator panel is accessed.
     *
     * @return void
     */
    public static function enqueueScripts()
    {
        $asset = substr(microtime(true), 5);

        wp_register_style('gddysec', GDDYSEC_URL . '/inc/css/styles.css', array(), $asset);
        wp_register_script('gddysec', GDDYSEC_URL . '/inc/js/scripts.js', array(), $asset);
        wp_enqueue_style('gddysec');
        wp_enqueue_script('gddysec');

        if (GddysecRequest::get('page', 'gddysec') !== false) {
            wp_register_script('gddysec2', GDDYSEC_URL . '/inc/js/d3.min.js', array(), $asset);
            wp_register_script('gddysec3', GDDYSEC_URL . '/inc/js/c3.min.js', array(), $asset);
            wp_enqueue_script('gddysec2');
            wp_enqueue_script('gddysec3');
        }
    }

    /**
     * Create a folder in the WordPress upload directory where the plugin will
     * store all the temporal or dynamic information.
     *
     * @return void
     */
    public static function createStorageFolder()
    {
        $directory = Gddysec::datastore_folder_path();

        if (!file_exists($directory)) {
            @mkdir($directory, 0755, true);
        }

        if (file_exists($directory)) {
            // Create a htaccess file to deny access from all.
            if (!GddysecHardening::is_hardened($directory)) {
                GddysecHardening::harden_directory($directory);
            }

            // Create an index.html to avoid directory listing.
            @file_put_contents(
                $directory . '/index.html',
                '<!-- Prevent the directory listing. -->',
                LOCK_EX
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
     * @return void
     */
    public static function noticeAfterUpdate()
    {
        $version = GddysecOption::get_option(':plugin_version');

        // Use simple comparison to force type cast.
        if ($version != GDDYSEC_VERSION) {
            /**
             * Check if the API communication has been disabled due to issues
             * with the previous version of the code, in this case we will
             * display a message at the top of the admin dashboard suggesting
             * the user to enable it once again expecting to see have a better
             * performance with the new code.
             */
            if (GddysecOption::is_disabled(':api_service')) {
                self::info(
                    'API service communication is disabled, if you just updated '
                    . 'the plugin this might be a good opportunity to test this '
                    . 'feature once again with the new code. Enable it again from '
                    . 'the "API Service" panel located in the settings page.'
                );
            }

            // Update the version number in the plugin settings.
            GddysecOption::update_option(':plugin_version', GDDYSEC_VERSION);
        }
    }

    /**
     * Check whether a user has the permissions to see a page from the plugin.
     *
     * @return void
     */
    public static function check_permissions()
    {
        if (!function_exists('current_user_can')
            || !current_user_can('manage_options')
        ) {
            $page = GddysecRequest::get('page', '_page');
            $page = Gddysec::escape($page);
            wp_die(__('Access denied by <b>GoDaddy Security</b> to see <code>' . $page . '</code>'));
        }
    }

    /**
     * Verify the nonce of the previous page after a form submission. If the
     * validation fails the execution of the script will be stopped and a dead page
     * will be printed to the client using the official WordPress method.
     *
     * @return boolean Either TRUE or FALSE if the nonce is valid or not respectively.
     */
    public static function check_nonce()
    {
        if (!empty($_POST)) {
            $nonce_name = 'gddysec_page_nonce';
            $nonce_value = GddysecRequest::post($nonce_name, '_nonce');

            if (!$nonce_value || !wp_verify_nonce($nonce_value, $nonce_name)) {
                wp_die(__('WordPress Nonce verification failed, try again going back and checking the form.'));

                return false;
            }
        }

        return true;
    }

    /**
     * Prints a HTML alert in the WordPress admin interface.
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
         * There are some special cases when the error or warning messages should not be
         * rendered to the end user because it may break the default functionality of
         * the request handler. For instance, rendering an HTML alert like this when the
         * user authentication process is executed may cause a "headers already sent"
         * error.
         */
        if (!empty($_POST)
            && GddysecRequest::post('log')
            && GddysecRequest::post('pwd')
            && GddysecRequest::post('wp-submit')
        ) {
            $display_notice = false;
        }

        // Display the HTML notice to the current user.
        if ($display_notice === true && !empty($message)) {
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
     * @param  string $error_msg The message that will be printed in the alert.
     * @return void
     */
    public static function error($error_msg = '')
    {
        self::adminNotice('error', '<b>GoDaddy Security:</b> ' . $error_msg);
    }

    /**
     * Prints a HTML alert of type INFO in the WordPress admin interface.
     *
     * @param  string $info_msg The message that will be printed in the alert.
     * @return void
     */
    public static function info($info_msg = '')
    {
        self::adminNotice('updated', '<b>GoDaddy Security:</b> ' . $info_msg);
    }
}
