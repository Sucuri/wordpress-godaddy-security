<?php

/**
 * Code related to the globals.php interface.
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
 * Plugin's global variables.
 *
 * These variables will be defined globally to allow the inclusion in multiple
 * methods and classes defined in the libraries loaded by this plugin. The
 * conditional will act as a container helping in the readability of the code
 * considering the total number of lines that this file will have.
 */
if (defined('GDDYSEC')) {
    /**
     * Define the prefix for some actions and filters that rely in the differen-
     * tiation of the type of site where the extension is being used. There are
     * a few differences between a single site installation that must be
     * correctly defined when the extension is in a different environment, for
     * example, in a multisite installation.
     *
     * @var string
     */
    $gddysec_action_prefix = Gddysec::isMultiSite() ? 'network_' : '';

    /**
     * Remove the WordPress generator meta-tag from the source code.
     */
    remove_action('wp_head', 'wp_generator');

    /**
     * Run a specific method defined in the plugin's code to locate every
     * directory and file, collect their checksum and file size, and send this
     * information to the Sucuri API service where a security and integrity scan
     * will be performed against the hashes provided and the official versions.
     */
    add_action('gddysec_scheduled_scan', 'Gddysec::runScheduledTask');

    /**
     * Initialize the execute of the main plugin's functions.
     *
     * This will load the menu options in the WordPress administrator panel, and
     * execute the bootstrap method of the plugin.
     */
    add_action('init', 'GddysecInterface::initialize', 1);
    add_action('admin_enqueue_scripts', 'GddysecInterface::enqueueScripts', 1);

    if (Gddysec::runAdminInit()) {
        add_action('admin_init', 'GddysecInterface::createStorageFolder');
    }

    /**
     * List an associative array with the sub-pages of this plugin.
     *
     * @return array List of sub-pages of this plugin.
     */
    function gddysecMainPages()
    {
        return array(
            'gddysec' => 'Dashboard',
            'gddysec_settings' => 'Settings',
        );
    }

    if (function_exists('add_action')) {
        /**
         * Display extension menu and submenu items in the correct interface.
         * For single site installations the menu items can be displayed
         * normally as always but for multisite installations the menu items
         * must be available only in the network panel and hidden in the
         * administration panel of the subsites.
         *
         * @codeCoverageIgnore
         *
         * @return void
         */
        function gddysecAddMenuPage()
        {
            $pages = gddysecMainPages();

            add_menu_page(
                'GoDaddy Security',
                'GoDaddy Security',
                'manage_options',
                'gddysec',
                'gddysec_page',
                GDDYSEC_URL . '/inc/images/menuicon.png'
            );

            foreach ($pages as $sub_page_func => $sub_page_title) {
                add_submenu_page(
                    'gddysec',
                    $sub_page_title,
                    $sub_page_title,
                    'manage_options',
                    $sub_page_func,
                    $sub_page_func . '_page'
                );
            }
        }

        /* Attach HTTP request handlers for the internal plugin pages */
        add_action($gddysec_action_prefix . 'admin_menu', 'gddysecAddMenuPage');

        /* Attach HTTP request handlers for the AJAX requests */
        add_action('wp_ajax_gddysec_ajax', 'gddysec_ajax');
    }

    /**
     * Function call interceptors.
     *
     * Define the names for the hooks that will intercept specific method calls in
     * the admin interface and parts of the external site, an event report will be
     * sent to the API service and an email notification to the administrator of the
     * site.
     *
     * @see Class GddysecHook
     */
    if (class_exists('GddysecHook')) {
        add_action('activated_plugin', 'GddysecHook::hookPluginActivate', 50, 2);
        add_action('add_attachment', 'GddysecHook::hookAttachmentAdd', 50, 5);
        add_action('add_link', 'GddysecHook::hookLinkAdd', 50, 5);
        add_action('before_delete_post', 'GddysecHook::hookPostBeforeDelete', 50, 5);
        add_action('create_category', 'GddysecHook::hookCategoryCreate', 50, 5);
        add_action('deactivated_plugin', 'GddysecHook::hookPluginDeactivate', 50, 2);
        add_action('delete_post', 'GddysecHook::hookPostDelete', 50, 5);
        add_action('delete_user', 'GddysecHook::hookUserDelete', 50, 5);
        add_action('edit_link', 'GddysecHook::hookLinkEdit', 50, 5);
        add_action('login_form_resetpass', 'GddysecHook::hookLoginFormResetpass', 50, 5);
        add_action('publish_page', 'GddysecHook::hookPublishPage', 50, 5);
        add_action('publish_phone', 'GddysecHook::hookPublishPhone', 50, 5);
        add_action('publish_post', 'GddysecHook::hookPublishPost', 50, 5);
        add_action('retrieve_password', 'GddysecHook::hookRetrievePassword', 50, 5);
        add_action('switch_theme', 'GddysecHook::hookThemeSwitch', 50, 5);
        add_action('transition_post_status', 'GddysecHook::hookPostStatus', 50, 3);
        add_action('user_register', 'GddysecHook::hookUserRegister', 50, 5);
        add_action('wp_login', 'GddysecHook::hookLoginSuccess', 50, 5);
        add_action('wp_login_failed', 'GddysecHook::hookLoginFailure', 50, 5);
        add_action('wp_trash_post', 'GddysecHook::hookPostTrash', 50, 5);
        add_action('xmlrpc_publish_post', 'GddysecHook::hookPublishPostXMLRPC', 50, 5);

        if (Gddysec::runAdminInit()) {
            add_action('admin_init', 'GddysecHook::hookCoreUpdate');
            add_action('admin_init', 'GddysecHook::hookOptionsManagement');
            add_action('admin_init', 'GddysecHook::hookPluginDelete');
            add_action('admin_init', 'GddysecHook::hookPluginEditor');
            add_action('admin_init', 'GddysecHook::hookPluginInstall');
            add_action('admin_init', 'GddysecHook::hookPluginUpdate');
            add_action('admin_init', 'GddysecHook::hookThemeDelete');
            add_action('admin_init', 'GddysecHook::hookThemeEditor');
            add_action('admin_init', 'GddysecHook::hookThemeInstall');
            add_action('admin_init', 'GddysecHook::hookThemeUpdate');
            add_action('admin_init', 'GddysecHook::hookWidgetAdd');
            add_action('admin_init', 'GddysecHook::hookWidgetDelete');
        }
    }
}
