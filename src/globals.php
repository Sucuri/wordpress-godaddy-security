<?php

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
 * functions and classes defined in the libraries loaded by this plugin. The
 * conditional will act as a container helping in the readability of the code
 * considering the total number of lines that this file will have.
 */
if (defined('GDDYSEC')) {
    /**
     * Define the prefix for some actions and filters that rely in the
     * differentiation of the type of site where the extension is being used. There
     * are a few differences between a single site installation that must be
     * correctly defined when the extension is in a different environment, for
     * example, in a multisite installation.
     *
     * @var string
     */
    $gddysec_action_prefix = Gddysec::is_multisite() ? 'network_' : '';

    /**
     * List an associative array with the sub-pages of this plugin.
     *
     * @return array
     */
    $gddysec_pages = array(
        'gddysec' => 'Dashboard',
        'gddysec_scanner' => 'Malware Scan',
        'gddysec_settings' => 'Settings',
    );

    /**
     * Settings options.
     *
     * The following global variables are mostly associative arrays where the key is
     * linked to an option that will be stored in the database, and their
     * correspondent values are the description of the option. These variables will
     * be used in the settings page to offer the user a way to configure the
     * behaviour of the plugin.
     *
     * @var array
     */

    $gddysec_notify_options = array(
        'gddysec_notify_plugin_change' => 'Receive email alerts for <b>GoDaddy Security</b> plugin changes',
        'gddysec_prettify_mails' => 'Receive email alerts in HTML <em>(there may be issues with some mail services)</em>',
        'gddysec_use_wpmail' => 'Use WordPress functions to send mails <em>(uncheck to use native PHP functions)</em>',
        'gddysec_lastlogin_redirection' => 'Allow redirection after login to report the last-login information',
        'gddysec_notify_scan_checksums' => 'Receive email alerts for core integrity checks',
        'gddysec_notify_available_updates' => 'Receive email alerts for available updates',
        'gddysec_notify_user_registration' => 'user:Receive email alerts for new user registration',
        'gddysec_notify_success_login' => 'user:Receive email alerts for successful login attempts',
        'gddysec_notify_failed_login' => 'user:Receive email alerts for failed login attempts <em>(you may receive tons of emails)</em>',
        'gddysec_notify_bruteforce_attack' => 'user:Receive email alerts for password guessing attacks <em>(summary of failed logins per hour)</em>',
        'gddysec_notify_post_publication' => 'Receive email alerts for Post-Type changes <em>(configure from Ignore Alerts)</em>',
        'gddysec_notify_website_updated' => 'Receive email alerts when the WordPress version is updated',
        'gddysec_notify_settings_updated' => 'Receive email alerts when your website settings are updated',
        'gddysec_notify_theme_editor' => 'Receive email alerts when a file is modified with theme/plugin editor',
        'gddysec_notify_plugin_installed' => 'plugin:Receive email alerts when a <b>plugin is installed</b>',
        'gddysec_notify_plugin_activated' => 'plugin:Receive email alerts when a <b>plugin is activated</b>',
        'gddysec_notify_plugin_deactivated' => 'plugin:Receive email alerts when a <b>plugin is deactivated</b>',
        'gddysec_notify_plugin_updated' => 'plugin:Receive email alerts when a <b>plugin is updated</b>',
        'gddysec_notify_plugin_deleted' => 'plugin:Receive email alerts when a <b>plugin is deleted</b>',
        'gddysec_notify_widget_added' => 'widget:Receive email alerts when a <b>widget is added</b> to a sidebar',
        'gddysec_notify_widget_deleted' => 'widget:Receive email alerts when a <b>widget is deleted</b> from a sidebar',
        'gddysec_notify_theme_installed' => 'theme:Receive email alerts when a <b>theme is installed</b>',
        'gddysec_notify_theme_activated' => 'theme:Receive email alerts when a <b>theme is activated</b>',
        'gddysec_notify_theme_updated' => 'theme:Receive email alerts when a <b>theme is updated</b>',
        'gddysec_notify_theme_deleted' => 'theme:Receive email alerts when a <b>theme is deleted</b>',
    );

    $gddysec_emails_per_hour = array(
        '5' => 'Maximum 5 per hour',
        '10' => 'Maximum 10 per hour',
        '20' => 'Maximum 20 per hour',
        '40' => 'Maximum 40 per hour',
        '80' => 'Maximum 80 per hour',
        '160' => 'Maximum 160 per hour',
        'unlimited' => 'Unlimited',
    );

    $gddysec_maximum_failed_logins = array(
        '30' => '30 failed logins per hour',
        '60' => '60 failed logins per hour',
        '120' => '120 failed logins per hour',
        '240' => '240 failed logins per hour',
        '480' => '480 failed logins per hour',
    );

    $gddysec_no_notices_in = array(
        /* Value of the page parameter to ignore. */
    );

    $gddysec_email_subjects = array(
        'GoDaddy Security Alert, :domain, :event',
        'GoDaddy Security Alert, :domain, :event, :remoteaddr',
        'GoDaddy Security Alert, :domain, :event, :username',
        'GoDaddy Security Alert, :domain, :event, :email',
        'GoDaddy Security Alert, :event, :remoteaddr',
        'GoDaddy Security Alert, :event',
    );

    $gddysec_date_format = get_option('date_format');
    $gddysec_time_format = get_option('time_format');

    /**
     * Remove the WordPress generator meta-tag from the source code.
     */
    remove_action('wp_head', 'wp_generator');

    /**
     * Run a specific function defined in the plugin's code to locate every
     * directory and file, collect their checksum and file size, and send this
     * information to the Sucuri API service where a security and integrity scan
     * will be performed against the hashes provided and the official versions.
     */
    add_action('gddysec_scheduled_scan', 'Gddysec::runScheduledTask');

    /**
     * Initialize the execute of the main plugin's functions.
     *
     * This will load the menu options in the WordPress administrator panel, and
     * execute the bootstrap function of the plugin.
     */
    add_action('init', 'GddysecInterface::initialize', 1);
    add_action('admin_enqueue_scripts', 'GddysecInterface::enqueueScripts', 1);

    if (Gddysec::runAdminInit()) {
        add_action('admin_init', 'GddysecInterface::createStorageFolder');
        add_action('admin_init', 'GddysecInterface::noticeAfterUpdate');
    }

    /**
     * Display extension menu and submenu items in the correct interface. For single
     * site installations the menu items can be displayed normally as always but for
     * multisite installations the menu items must be available only in the network
     * panel and hidden in the administration panel of the subsites.
     */
    function gddysecAddMenu()
    {
        add_menu_page(
            'GoDaddy Security',
            'GoDaddy Security',
            'manage_options',
            'gddysec',
            'GddysecDashboard::initialPage',
            GDDYSEC_URL . '/inc/images/menuicon.png'
        );

        add_submenu_page(
            'gddysec',
            'gddysec_settings' /* empty */,
            'GoDaddy Settings' /* empty */,
            'manage_options',
            'gddysec_settings',
            'GddysecSettings::initialPage'
        );
    }

    add_action($gddysec_action_prefix . 'admin_menu', 'gddysecAddMenu');
    add_action('wp_ajax_gddysec_ajax', 'GddysecDashboard::ajaxRequests');

    /**
     * Function call interceptors.
     *
     * Define the names for the hooks that will intercept specific function calls in
     * the admin interface and parts of the external site, an event report will be
     * sent to the API service and an email notification to the administrator of the
     * site.
     *
     * @see Class GddysecHook
     */
    if (class_exists('GddysecHook')) {
        $gddysec_hooks = array(
            'add_attachment',
            'add_link',
            'create_category',
            'delete_post',
            'delete_user',
            'login_form_resetpass',
            'private_to_published',
            'publish_page',
            'publish_phone',
            'publish_post',
            'retrieve_password',
            'switch_theme',
            'user_register',
            'wp_insert_comment',
            'wp_login',
            'wp_login_failed',
            'wp_trash_post',
            'xmlrpc_publish_post',
        );

        if (GddysecOption::is_enabled(':xhr_monitor')) {
            $gddysec_hooks[] = 'all';
        }

        foreach ($gddysec_hooks as $hook_name) {
            $hook_func = 'GddysecHook::hook_' . $hook_name;
            add_action($hook_name, $hook_func, 50, 5);
        }

        if (Gddysec::runAdminInit()) {
            add_action('admin_init', 'GddysecHook::hook_undefined_actions');
        }

        add_action('login_form', 'GddysecHook::hook_undefined_actions');
    } else {
        GddysecInterface::error('Function call interceptors are not working properly.');
    }
}
