<?php

/**
 * Code related to the settings-general.php interface.
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
 * Renders a page with information about the reset options feature.
 *
 * @param bool $nonce True if the CSRF protection worked.
 * @return string Page with information about the reset options.
 */
function gddysec_settings_general_resetoptions($nonce)
{
    // Reset all the plugin's options.
    if ($nonce && GddysecRequest::post(':reset_options') !== false) {
        $process = GddysecRequest::post(':process_form');

        if (intval($process) === 1) {
            $message = 'Local security logs, hardening and settings were deleted';

            gddysecResetAndDeactivate(); /* simulate plugin deactivation */

            GddysecEvent::reportCriticalEvent($message);
            GddysecEvent::notifyEvent('plugin_change', $message);
            GddysecInterface::info('Local security logs, hardening and settings were deleted');
        } else {
            GddysecInterface::error('You need to confirm that you understand the risk of this operation.');
        }
    }

    return GddysecTemplate::getSection('settings-general-resetoptions');
}

/**
 * Renders a page with information about the API key feature.
 *
 * @param bool $nonce True if the CSRF protection worked.
 * @return string Page with information about the API key.
 */
function gddysec_settings_general_apikey($nonce)
{
    $params = array();
    $invalid_domain = false;
    $api_recovery_modal = '';
    $api_registered_modal = '';

    // Whether the form to manually add the API key should be shown or not.
    $display_manual_key_form = (bool) (GddysecRequest::post(':recover_key') !== false);

    if ($nonce) {
        // Remove API key from the local storage.
        $api_key = GddysecAPI::getPluginKey();
        if (GddysecRequest::post(':remove_api_key') !== false
            && GddysecAPI::setPluginKey('') !== false
        ) {
            wp_clear_scheduled_hook('gddysec_scheduled_scan');

            $api_key = Gddysec::escape($api_key);
            GddysecEvent::reportCriticalEvent('GoDaddy Security API key has been deleted.');
            GddysecEvent::notifyEvent('plugin_change', 'GoDaddy Security API key removed');
            GddysecInterface::info('GoDaddy Security API key has been deleted <code>' . $api_key . '</code>');
        }

        // Save API key after it was recovered by the administrator.
        $api_key = GddysecRequest::post(':manual_api_key');

        if ($api_key) {
            GddysecAPI::setPluginKey($api_key, true);
            GddysecEvent::installScheduledTask();
            GddysecEvent::reportInfoEvent('GoDaddy Security API key was added manually.');
        }

        // Generate new API key from the API service.
        if (GddysecRequest::post(':plugin_api_key') !== false) {
            $user_id = (int) GddysecRequest::post(':setup_user');
            $user_obj = Gddysec::getUserByID($user_id);

            if ($user_obj && user_can($user_obj, 'administrator')) {
                // Send request to generate new API key or display form to set manually.
                if (GddysecAPI::registerSite($user_obj->user_email)) {
                    $api_registered_modal = GddysecTemplate::getModal(
                        'settings-apiregistered',
                        array('Title' => 'Site registered successfully')
                    );
                } else {
                    $display_manual_key_form = true;
                }
            }
        }

        // Recover API key through the email registered previously.
        if (GddysecRequest::post(':recover_key') !== false) {
            if (GddysecAPI::recoverKey()) {
                $_GET['recover'] = 'true'; /* display modal window */
                GddysecEvent::reportInfoEvent('API key recovery (email sent)');
            } else {
                GddysecEvent::reportInfoEvent('API key recovery (failure)');
            }
        }
    }

    $api_key = GddysecAPI::getPluginKey();

    if (GddysecRequest::get('recover') !== false) {
        $api_recovery_modal = GddysecTemplate::getModal(
            'settings-apirecovery',
            array('Title' => 'Plugin API Key Recovery')
        );
    }

    // Check whether the domain name is valid or not.
    if (!$api_key) {
        $clean_domain = Gddysec::getTopLevelDomain();
        $domain_address = @gethostbyname($clean_domain);
        $invalid_domain = (bool) ($domain_address === $clean_domain);
    }

    $params['APIKey'] = (!$api_key ? '(not set)' : $api_key);
    $params['APIKey.RecoverVisibility'] = GddysecTemplate::visibility(!$api_key);
    $params['APIKey.ManualKeyFormVisibility'] = GddysecTemplate::visibility($display_manual_key_form);
    $params['APIKey.RemoveVisibility'] = GddysecTemplate::visibility((bool) $api_key);
    $params['InvalidDomainVisibility'] = GddysecTemplate::visibility($invalid_domain);
    $params['ModalWhenAPIRegistered'] = $api_registered_modal;
    $params['ModalForApiKeyRecovery'] = $api_recovery_modal;

    return GddysecTemplate::getSection('settings-general-apikey', $params);
}

/**
 * Renders a page with information about the data storage feature.
 *
 * @param bool $nonce True if the CSRF protection worked.
 * @return string Page with information about the data storage.
 */
function gddysec_settings_general_datastorage($nonce)
{
    $params = array();
    $files = array(
        '', /* <root> */
        'auditlogs',
        'auditqueue',
        'blockedusers',
        'failedlogins',
        'hookdata',
        'ignorescanning',
        'integrity',
        'lastlogins',
        'oldfailedlogins',
        'plugindata',
        'settings',
        'sitecheck',
        'trustip',
    );

    $params['Storage.Files'] = '';
    $params['Storage.Path'] = Gddysec::dataStorePath();

    if ($nonce) {
        $filenames = GddysecRequest::post(':filename', '_array');

        if ($filenames) {
            $deleted = 0;

            foreach ($filenames as $filename) {
                $short = substr($filename, 7); /* drop directroy path */
                $short = substr($short, 0, -4); /* drop file extension */

                if (!$short || empty($short) || !in_array($short, $files)) {
                    continue; /* prevent path traversal */
                }

                $filepath = Gddysec::dataStorePath($filename);

                if (!file_exists($filepath) || is_dir($filepath)) {
                    continue; /* there is nothing to reset */
                }

                /* ignore write permissions */
                if (@unlink($filepath)) {
                    $deleted++;
                }
            }

            GddysecInterface::info(
                sprintf(
                    '%d out of %d files has been deleted',
                    $deleted,
                    count($filenames)
                )
            );
        }
    }

    foreach ($files as $name) {
        $fsize = 0;
        $fname = ($name ? sprintf('gddysec-%s.php', $name) : '');
        $fpath = Gddysec::dataStorePath($fname);
        $disabled = 'disabled="disabled"';
        $iswritable = 'Not Writable';
        $exists = 'Does Not Exist';
        $labelExistence = 'danger';
        $labelWritability = 'default';

        if (file_exists($fpath)) {
            $fsize = @filesize($fpath);
            $exists = 'Exists';
            $labelExistence = 'success';
            $labelWritability = 'danger';

            if (is_writable($fpath)) {
                $disabled = ''; /* Allow file deletion */
                $iswritable = 'Writable';
                $labelWritability = 'success';
            }
        }

        $params['Storage.Filename'] = $fname;
        $params['Storage.Filepath'] = str_replace(ABSPATH, '', $fpath);
        $params['Storage.Filesize'] = Gddysec::humanFileSize($fsize);
        $params['Storage.Exists'] = $exists;
        $params['Storage.IsWritable'] = $iswritable;
        $params['Storage.DisabledInput'] = $disabled;
        $params['Storage.Existence'] = $labelExistence;
        $params['Storage.Writability'] = $labelWritability;

        if (is_dir($fpath)) {
            $params['Storage.Filesize'] = '';
            $params['Storage.DisabledInput'] = 'disabled="disabled"';
        }

        $params['Storage.Files'] .= GddysecTemplate::getSnippet('settings-general-datastorage', $params);
    }

    return GddysecTemplate::getSection('settings-general-datastorage', $params);
}

/**
 * Returns the path to the local event monitoring file.
 *
 * The website owner can configure the plugin to send a copy of the security
 * events to a local file that can be integrated with other monitoring systems
 * like OSSEC, OpenVAS, NewRelic and similar.
 *
 * @return string|bool Path to the log file, false if disabled.
 */
function gddysec_selfhosting_fpath()
{
    $monitor = GddysecOption::getOption(':selfhosting_monitor');
    $monitor_fpath = GddysecOption::getOption(':selfhosting_fpath');
    $folder = dirname($monitor_fpath);

    if ($monitor === 'enabled'
        && !empty($monitor_fpath)
        && is_writable($folder)
    ) {
        return $monitor_fpath;
    }

    return false;
}

/**
 * Renders a page with information about the self-hosting feature.
 *
 * @param bool $nonce True if the CSRF protection worked.
 * @return string Page with information about the self-hosting.
 */
function gddysec_settings_general_selfhosting($nonce)
{
    $params = array();

    $params['SelfHosting.DisabledVisibility'] = 'visible';
    $params['SelfHosting.Status'] = 'Enabled';
    $params['SelfHosting.SwitchText'] = 'Disable';
    $params['SelfHosting.SwitchValue'] = 'disable';
    $params['SelfHosting.FpathVisibility'] = 'hidden';
    $params['SelfHosting.Fpath'] = '';

    if ($nonce) {
        // Set a file path for the self-hosted event monitor.
        $monitor_fpath = GddysecRequest::post(':selfhosting_fpath');

        if ($monitor_fpath !== false) {
            if (empty($monitor_fpath)) {
                $message = 'Log exporter was disabled';

                GddysecEvent::reportInfoEvent($message);
                GddysecOption::deleteOption(':selfhosting_fpath');
                GddysecOption::updateOption(':selfhosting_monitor', 'disabled');
                GddysecEvent::notifyEvent('plugin_change', $message);
                GddysecInterface::info('The log exporter feature has been disabled');
            } elseif (strpos($monitor_fpath, $_SERVER['DOCUMENT_ROOT']) !== false) {
                GddysecInterface::error('File should not be publicly accessible.');
            } elseif (file_exists($monitor_fpath)) {
                GddysecInterface::error('File already exists and will not be overwritten.');
            } elseif (!is_writable(dirname($monitor_fpath))) {
                GddysecInterface::error('File parent directory is not writable.');
            } else {
                @file_put_contents($monitor_fpath, '', LOCK_EX);

                $message = 'Log exporter file path was correctly set';

                GddysecEvent::reportInfoEvent($message);
                GddysecOption::updateOption(':selfhosting_monitor', 'enabled');
                GddysecOption::updateOption(':selfhosting_fpath', $monitor_fpath);
                GddysecEvent::notifyEvent('plugin_change', $message);
                GddysecInterface::info('The log exporter feature has been enabled and the data file was successfully set.');
            }
        }
    }

    $monitor = GddysecOption::getOption(':selfhosting_monitor');
    $monitor_fpath = GddysecOption::getOption(':selfhosting_fpath');

    if ($monitor === 'disabled') {
        $params['SelfHosting.Status'] = 'Disabled';
        $params['SelfHosting.SwitchText'] = 'Enable';
        $params['SelfHosting.SwitchValue'] = 'enable';
    }

    if ($monitor === 'enabled' && $monitor_fpath) {
        $params['SelfHosting.DisabledVisibility'] = 'hidden';
        $params['SelfHosting.FpathVisibility'] = 'visible';
        $params['SelfHosting.Fpath'] = Gddysec::escape($monitor_fpath);
    }

    return GddysecTemplate::getSection('settings-general-selfhosting', $params);
}

/**
 * Renders a page with information about the reverse proxy feature.
 *
 * @param bool $nonce True if the CSRF protection worked.
 * @return string Page with information about the reverse proxy.
 */
function gddysec_settings_general_reverseproxy($nonce)
{
    $params = array(
        'ReverseProxyStatus' => 'Enabled',
        'ReverseProxySwitchText' => 'Disable',
        'ReverseProxySwitchValue' => 'disable',
    );

    // Enable or disable the reverse proxy support.
    if ($nonce) {
        $revproxy = GddysecRequest::post(':revproxy', '(en|dis)able');

        if ($revproxy) {
            if ($revproxy === 'enable') {
                GddysecOption::setRevProxy('enable');
                GddysecOption::setAddrHeader('HTTP_X_SUCURI_CLIENTIP');
            } else {
                GddysecOption::setRevProxy('disable');
                GddysecOption::setAddrHeader('REMOTE_ADDR');
            }
        }
    }

    if (GddysecOption::isDisabled(':revproxy')) {
        $params['ReverseProxyStatus'] = 'Disabled';
        $params['ReverseProxySwitchText'] = 'Enable';
        $params['ReverseProxySwitchValue'] = 'enable';
    }

    return GddysecTemplate::getSection('settings-general-reverseproxy', $params);
}

/**
 * Renders a page with information about the import export feature.
 *
 * @param bool $nonce True if the CSRF protection worked.
 * @return string Page with information about the import export.
 */
function gddysec_settings_general_importexport($nonce)
{
    $settings = array();
    $params = array();
    $allowed = array(
        ':addr_header',
        ':api_key',
        ':api_protocol',
        ':api_service',
        ':cloudproxy_apikey',
        ':diff_utility',
        ':dns_lookups',
        ':email_subject',
        ':emails_per_hour',
        ':ignored_events',
        ':lastlogin_redirection',
        ':maximum_failed_logins',
        ':notify_available_updates',
        ':notify_bruteforce_attack',
        ':notify_failed_login',
        ':notify_plugin_activated',
        ':notify_plugin_change',
        ':notify_plugin_deactivated',
        ':notify_plugin_deleted',
        ':notify_plugin_installed',
        ':notify_plugin_updated',
        ':notify_post_publication',
        ':notify_scan_checksums',
        ':notify_settings_updated',
        ':notify_success_login',
        ':notify_theme_activated',
        ':notify_theme_deleted',
        ':notify_theme_editor',
        ':notify_theme_installed',
        ':notify_theme_updated',
        ':notify_to',
        ':notify_user_registration',
        ':notify_website_updated',
        ':notify_widget_added',
        ':notify_widget_deleted',
        ':prettify_mails',
        ':revproxy',
        ':selfhosting_fpath',
        ':selfhosting_monitor',
        ':use_wpmail',
    );

    if ($nonce && GddysecRequest::post(':import') !== false) {
        $process = GddysecRequest::post(':process_form');

        if (intval($process) === 1) {
            $json = GddysecRequest::post(':settings');
            $json = str_replace('\&quot;', '"', $json);
            $data = @json_decode($json, true);

            if ($data) {
                $count = 0;
                $total = count($data);

                /* minimum length for option name */
                $minLength = strlen(GDDYSEC . '_');

                foreach ($data as $option => $value) {
                    if (strlen($option) <= $minLength) {
                        continue;
                    }

                    $option_name = ':' . substr($option, $minLength);

                    /* check if the option can be imported */
                    if (!in_array($option_name, $allowed)) {
                        continue;
                    }

                    GddysecOption::updateOption($option_name, $value);

                    $count++;
                }

                GddysecInterface::info(
                    sprintf(
                        '%d out of %d option have been successfully imported',
                        $count,
                        $total
                    )
                );
            } else {
                GddysecInterface::error('Data is incorrectly encoded');
            }
        } else {
            GddysecInterface::error('You need to confirm that you understand the risk of this operation.');
        }
    }

    foreach ($allowed as $option) {
        $option_name = Gddysec::varPrefix($option);
        $settings[$option_name] = GddysecOption::getOption($option);
    }

    $params['Export'] = @json_encode($settings);

    return GddysecTemplate::getSection('settings-general-importexport', $params);
}

/**
 * Renders a page with the option to configure the timezone.
 *
 * @param bool $nonce True if the CSRF protection worked.
 * @return string Page to configure the timezone.
 */
function gddysec_settings_general_timezone($nonce)
{
    $params = array();
    $current = time();
    $options = array();
    $offsets = array(
        -12.0, -11.5, -11.0, -10.5, -10.0, -9.50, -9.00, -8.50, -8.00, -7.50,
        -7.00, -6.50, -6.00, -5.50, -5.00, -4.50, -4.00, -3.50, -3.00, -2.50,
        -2.00, -1.50, -1.00, -0.50, +0.00, +0.50, +1.00, +1.50, +2.00, +2.50,
        +3.00, +3.50, +4.00, +4.50, +5.00, +5.50, +5.75, +6.00, +6.50, +7.00,
        +7.50, +8.00, +8.50, +8.75, +9.00, +9.50, 10.00, 10.50, 11.00, 11.50,
        12.00, 12.75, 13.00, 13.75, 14.00
    );

    foreach ($offsets as $hour) {
        $sign = ($hour < 0) ? '-' : '+';
        $fill = (abs($hour) < 10) ? '0' : '';
        $keyname = sprintf('UTC%s%s%.2f', $sign, $fill, abs($hour));
        $label = date('d M, Y H:i:s', $current + ($hour * 3600));
        $options[$keyname] = $label;
    }

    if ($nonce) {
        $pattern = 'UTC[\-\+][0-9]{2}\.[0-9]{2}';
        $timezone = GddysecRequest::post(':timezone', $pattern);

        if ($timezone) {
            $message = 'Timezone override will use ' . $timezone;

            GddysecOption::updateOption(':timezone', $timezone);
            GddysecEvent::reportInfoEvent($message);
            GddysecEvent::notifyEvent('plugin_change', $message);
            GddysecInterface::info('The timezone for the date and time in the audit logs has been changed');
        }
    }

    $val = GddysecOption::getOption(':timezone');
    $params['Timezone.Dropdown'] = GddysecTemplate::selectOptions($options, $val);
    $params['Timezone.Example'] = Gddysec::datetime();

    return GddysecTemplate::getSection('settings-general-timezone', $params);
}
