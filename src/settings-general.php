<?php

if (!defined('GDDYSEC_INIT') || GDDYSEC_INIT !== true) {
    if (!headers_sent()) {
        /* Report invalid access if possible. */
        header('HTTP/1.1 403 Forbidden');
    }
    exit(1);
}

/**
 * Read and parse the content of the general settings template.
 *
 * @return string Parsed HTML code for the general settings panel.
 */
function gddysec_settings_general($nonce)
{
    // Process all form submissions.
    gddysec_settings_form_submissions($nonce);

    $params = array();

    // Keep the reset options panel and form submission processor before anything else.
    $params['Settings.ResetOptions'] = gddysec_settings_general_resetoptions($nonce);

    // Build HTML code for the additional general settings panels.
    $params['Settings.ApiKey'] = gddysec_settings_general_apikey($nonce);
    $params['Settings.DataStorage'] = gddysec_settings_general_datastorage($nonce);
    $params['Settings.ReverseProxy'] = gddysec_settings_general_reverseproxy($nonce);
    $params['Settings.IPDiscoverer'] = gddysec_settings_general_ipdiscoverer($nonce);
    $params['Settings.CommentMonitor'] = gddysec_settings_general_commentmonitor($nonce);
    $params['Settings.CoreFilesLanguage'] = gddysec_settings_general_corefiles_language($nonce);
    $params['Settings.CoreFilesCache'] = gddysec_settings_general_corefiles_cache($nonce);

    return GddysecTemplate::getSection('settings-general', $params);
}

function gddysec_settings_general_resetoptions($nonce)
{
    // Reset all the plugin's options.
    if ($nonce && GddysecRequest::post(':reset_options') !== false) {
        $process = GddysecRequest::post(':process_form');

        if (intval($process) === 1) {
            // Notify the event before the API key is removed.
            $message = 'GoDaddy Security plugin options were reset';
            GddysecEvent::report_critical_event($message);
            GddysecEvent::notify_event('plugin_change', $message);

            // Remove all plugin options from the database.
            GddysecOption::deletePluginOptions();

            // Remove the scheduled tasks.
            wp_clear_scheduled_hook('gddysec_scheduled_scan');

            // Remove all the local security logs.
            @unlink(Gddysec::datastore_folder_path('.htaccess'));
            @unlink(Gddysec::datastore_folder_path('index.html'));
            @unlink(Gddysec::datastore_folder_path(GDDYSEC . '-failedlogins.php'));
            @unlink(Gddysec::datastore_folder_path(GDDYSEC . '-integrity.php'));
            @unlink(Gddysec::datastore_folder_path(GDDYSEC . '-lastlogins.php'));
            @unlink(Gddysec::datastore_folder_path(GDDYSEC . '-oldfailedlogins.php'));
            @unlink(Gddysec::datastore_folder_path(GDDYSEC . '-plugindata.php'));
            @unlink(Gddysec::datastore_folder_path(GDDYSEC . '-sitecheck.php'));
            @unlink(Gddysec::datastore_folder_path(GDDYSEC . '-settings.php'));
            @unlink(Gddysec::datastore_folder_path(GDDYSEC . '-trustip.php'));
            @rmdir(Gddysec::datastore_folder_path());

            // Revert hardening of core directories (includes, content, uploads).
            GddysecHardening::dewhitelist('ms-files.php', 'wp-includes');
            GddysecHardening::dewhitelist('wp-tinymce.php', 'wp-includes');
            GddysecHardening::unharden_directory(ABSPATH . '/wp-includes');
            GddysecHardening::unharden_directory(WP_CONTENT_DIR . '/uploads');
            GddysecHardening::unharden_directory(WP_CONTENT_DIR);

            GddysecInterface::info('Plugin options, core directory hardening, and security logs were reset');
        } else {
            GddysecInterface::error('You need to confirm that you understand the risk of this operation.');
        }
    }

    return GddysecTemplate::getSection('settings-general-resetoptions');
}

function gddysec_settings_general_apikey($nonce)
{
    $params = array();
    $invalid_domain = false;
    $api_recovery_modal = '';
    $api_registered_modal = '';

    // Whether the form to manually add the API key should be shown or not.
    $display_manual_key_form = (bool) (GddysecRequest::post(':recover_key') !== false);

    if ($nonce) {
        if (!empty($_POST) && GddysecOption::settingsInTextFile()) {
            $fpath = GddysecOption::optionsFilePath();

            if (!is_writable($fpath)) {
                GddysecInterface::error(
                    'Storage is not writable: <code>'
                    . $fpath . '</code>'
                );
            }
        }

        // Remove API key from the local storage.
        if (GddysecRequest::post(':remove_api_key') !== false) {
            GddysecAPI::setPluginKey('');
            wp_clear_scheduled_hook('gddysec_scheduled_scan');
            GddysecEvent::report_critical_event('GoDaddy Security API key was deleted.');
            GddysecEvent::notify_event('plugin_change', 'GoDaddy Security API key removed');
        }

        // Save API key after it was recovered by the administrator.
        if ($api_key = GddysecRequest::post(':manual_api_key')) {
            GddysecAPI::setPluginKey($api_key, true);
            GddysecEvent::schedule_task();
            GddysecEvent::report_info_event('GoDaddy Security API key was added manually.');
        }

        // Generate new API key from the API service.
        if (GddysecRequest::post(':plugin_api_key') !== false) {
            $user_id = GddysecRequest::post(':setup_user');
            $user_obj = Gddysec::get_user_by_id($user_id);

            if ($user_obj !== false && user_can($user_obj, 'administrator')) {
                // Send request to generate new API key or display form to set manually.
                if (GddysecAPI::registerSite($user_obj->user_email)) {
                    $api_registered_modal = GddysecTemplate::getModal('settings-apiregistered', array(
                        'Title' => 'Site registered successfully',
                        'CssClass' => 'gddysec-apikey-registered',
                    ));
                } else {
                    $display_manual_key_form = true;
                }
            }
        }

        // Recover API key through the email registered previously.
        if (GddysecRequest::post(':recover_key') !== false) {
            $_GET['recover'] = 'true';
            GddysecAPI::recoverKey();
            GddysecEvent::report_info_event('Recovery of the GoDaddy Security API key was requested.');
        }
    }

    $api_key = GddysecAPI::getPluginKey();

    if (GddysecRequest::get('recover') !== false) {
        $api_recovery_modal = GddysecTemplate::getModal('settings-apirecovery', array(
            'Title' => 'Plugin API Key Recovery',
            'CssClass' => 'gddysec-apirecovery',
        ));
    }

    // Check whether the domain name is valid or not.
    if (!$api_key) {
        $clean_domain = Gddysec::get_top_level_domain();
        $domain_address = @gethostbyname($clean_domain);
        $invalid_domain = (bool) ($domain_address === $clean_domain);
    }

    $params['APIKey'] = (!$api_key ? '(not set)' : $api_key);
    $params['APIKey.RecoverVisibility'] = GddysecTemplate::visibility(!$api_key && !$display_manual_key_form);
    $params['APIKey.ManualKeyFormVisibility'] = GddysecTemplate::visibility($display_manual_key_form);
    $params['APIKey.RemoveVisibility'] = GddysecTemplate::visibility((bool) $api_key);
    $params['InvalidDomainVisibility'] = GddysecTemplate::visibility($invalid_domain);
    $params['ModalWhenAPIRegistered'] = $api_registered_modal;
    $params['ModalForApiKeyRecovery'] = $api_recovery_modal;

    return GddysecTemplate::getSection('settings-general-apikey', $params);
}

function gddysec_settings_general_datastorage($nonce)
{
    $params = array();
    $files = array(
        '', /* <root> */
        'auditqueue',
        'failedlogins',
        'integrity',
        'settings',
        'sitecheck',
    );

    $counter = 0;
    $params['DataStorage.Files'] = '';
    $params['DatastorePath'] = GddysecOption::get_option(':datastore_path');

    foreach ($files as $name) {
        $counter++;
        $fname = ($name ? sprintf('%s-%s.php', GDDYSEC, $name) : '');
        $fpath = Gddysec::datastore_folder_path($fname);
        $exists = (file_exists($fpath) ? 'Yes' : 'No');
        $iswritable = (is_writable($fpath) ? 'Yes' : 'No');
        $css_class = ($counter % 2 === 0) ? 'alternate' : '';
        $disabled = 'disabled="disabled"';

        if ($exists === 'Yes' && $iswritable === 'Yes') {
            $disabled = ''; /* Allow file deletion */
        }

        // Remove unnecessary parts from the file path.
        $fpath = str_replace(ABSPATH, '/', $fpath);

        $params['DataStorage.Files'] .= GddysecTemplate::getSnippet('settings-datastorage-files', array(
            'DataStorage.CssClass' => $css_class,
            'DataStorage.Fname' => $fname,
            'DataStorage.Fpath' => $fpath,
            'DataStorage.Exists' => $exists,
            'DataStorage.IsWritable' => $iswritable,
            'DataStorage.DisabledInput' => $disabled,
        ));
    }

    return GddysecTemplate::getSection('settings-general-datastorage', $params);
}

function gddysec_settings_general_reverseproxy($nonce)
{
    $params = array(
        'ReverseProxyStatus' => 'Enabled',
        'ReverseProxySwitchText' => 'Disable',
        'ReverseProxySwitchValue' => 'disable',
        'ReverseProxySwitchCssClass' => 'button-danger',
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

    if (GddysecOption::is_disabled(':revproxy')) {
        $params['ReverseProxyStatus'] = 'Disabled';
        $params['ReverseProxySwitchText'] = 'Enable';
        $params['ReverseProxySwitchValue'] = 'enable';
        $params['ReverseProxySwitchCssClass'] = 'button-success';
    }

    return GddysecTemplate::getSection('settings-general-reverseproxy', $params);
}

function gddysec_settings_general_ipdiscoverer($nonce)
{
    $params = array(
        'TopLevelDomain' => 'Unknown',
        'WebsiteHostName' => 'Unknown',
        'WebsiteHostAddress' => 'Unknown',
        'IsUsingCloudProxy' => 'Unknown',
        'WebsiteURL' => 'Unknown',
        'RemoteAddress' => '127.0.0.1',
        'RemoteAddressHeader' => 'INVALID',
        'AddrHeaderOptions' => '',
        /* Switch form information. */
        'DnsLookupsStatus' => 'Enabled',
        'DnsLookupsSwitchText' => 'Disable',
        'DnsLookupsSwitchValue' => 'disable',
        'DnsLookupsSwitchCssClass' => 'button-danger',
    );

    // Get main HTTP header for IP retrieval.
    $allowed_headers = Gddysec::allowedHttpHeaders(true);

    // Configure the DNS lookups option for reverse proxy detection.
    if ($nonce) {
        $dns_lookups = GddysecRequest::post(':dns_lookups', '(en|dis)able');
        $addr_header = GddysecRequest::post(':addr_header');

        if ($dns_lookups) {
            $action_d = $dns_lookups . 'd';
            $message = 'DNS lookups for reverse proxy detection <code>' . $action_d . '</code>';

            GddysecOption::update_option(':dns_lookups', $action_d);
            GddysecEvent::report_info_event($message);
            GddysecEvent::notify_event('plugin_change', $message);
            GddysecInterface::info($message);
        }

        if ($addr_header) {
            if ($addr_header === 'REMOTE_ADDR') {
                GddysecOption::setAddrHeader('REMOTE_ADDR');
                GddysecOption::setRevProxy('disable');
            } else {
                GddysecOption::setAddrHeader($addr_header);
                GddysecOption::setRevProxy('enable');
            }
        }
    }

    if (GddysecOption::is_disabled(':dns_lookups')) {
        $params['DnsLookupsStatus'] = 'Disabled';
        $params['DnsLookupsSwitchText'] = 'Enable';
        $params['DnsLookupsSwitchValue'] = 'enable';
        $params['DnsLookupsSwitchCssClass'] = 'button-success';
    }

    $params['RemoteAddressHeader'] = Gddysec::remoteAddrHeader();
    $params['RemoteAddress'] = Gddysec::remoteAddr();
    $params['WebsiteURL'] = Gddysec::get_domain();
    $params['AddrHeaderOptions'] = GddysecTemplate::selectOptions(
        $allowed_headers,
        GddysecOption::get_option(':addr_header')
    );

    return GddysecTemplate::getSection('settings-general-ipdiscoverer', $params);
}

function gddysec_settings_general_commentmonitor($nonce)
{
    $params = array(
        'CommentMonitorStatus' => 'Enabled',
        'CommentMonitorSwitchText' => 'Disable',
        'CommentMonitorSwitchValue' => 'disable',
        'CommentMonitorSwitchCssClass' => 'button-danger',
    );

    // Configure the comment monitor option.
    if ($nonce) {
        $monitor = GddysecRequest::post(':comment_monitor', '(en|dis)able');

        if ($monitor) {
            $action_d = $monitor . 'd';
            $message = 'Comment monitor was <code>' . $action_d . '</code>';

            GddysecOption::update_option(':comment_monitor', $action_d);
            GddysecEvent::report_info_event($message);
            GddysecEvent::notify_event('plugin_change', $message);
            GddysecInterface::info($message);
        }
    }

    if (GddysecOption::is_disabled(':comment_monitor')) {
        $params['CommentMonitorStatus'] = 'Disabled';
        $params['CommentMonitorSwitchText'] = 'Enable';
        $params['CommentMonitorSwitchValue'] = 'enable';
        $params['CommentMonitorSwitchCssClass'] = 'button-success';
    }

    return GddysecTemplate::getSection('settings-general-commentmonitor', $params);
}

function gddysec_settings_general_corefiles_language($nonce)
{
    $params = array();
    $languages = Gddysec::languages();

    if ($nonce) {
        // Configure the language for the core integrity checks.
        if ($language = GddysecRequest::post(':set_language')) {
            if (array_key_exists($language, $languages)) {
                $message = 'Language for the core integrity checks set to <code>' . $language . '</code>';

                GddysecOption::update_option(':language', $language);
                GddysecEvent::report_auto_event($message);
                GddysecEvent::notify_event('plugin_change', $message);
                GddysecInterface::info($message);
            } else {
                GddysecInterface::error('Selected language is not supported.');
            }
        }
    }

    $language = GddysecOption::get_option(':language');
    $params['Integrity.LanguageDropdown'] = GddysecTemplate::selectOptions($languages, $language);

    return GddysecTemplate::getSection('settings-general-corefiles-language', $params);
}

function gddysec_settings_general_corefiles_cache($nonce)
{
    $params = array();
    $fpath = Gddysec::datastore_folder_path(GDDYSEC . '-integrity.php');

    if ($nonce) {
        // Reset core integrity files marked as fixed
        if (GddysecRequest::post(':corefiles_cache')) {
            if (file_exists($fpath)) {
                if (@unlink($fpath)) {
                    $message = 'Core integrity files marked as fixed were successfully reset.';

                    GddysecEvent::report_debug_event($message);
                    GddysecInterface::info($message);
                } else {
                    GddysecInterface::error('Count not reset the cache, delete manually.');
                }
            } else {
                GddysecInterface::error('The cache file does not exists.');
            }
        }
    }

    $params['CoreFiles.CacheSize'] = Gddysec::human_filesize(@filesize($fpath));
    $params['CoreFiles.CacheLifeTime'] = SUCURISCAN_SITECHECK_LIFETIME;
    $params['CoreFiles.TableVisibility'] = 'hidden';
    $params['CoreFiles.IgnoredFiles'] = '';
    $cache = new GddysecCache('integrity');
    $ignored_files = $cache->getAll();
    $counter = 0;

    if ($ignored_files) {
        $tpl = 'settings-general-corefiles-cache';
        $params['CoreFiles.TableVisibility'] = 'visible';

        foreach ($ignored_files as $hash => $data) {
            $params['CoreFiles.IgnoredFiles'] .= GddysecTemplate::getSnippet($tpl, array(
                'IgnoredFile.CssClass' => ($counter % 2 === 0) ? '' : 'alternate',
                'IgnoredFile.UniqueId' => substr($hash, 0, 8),
                'IgnoredFile.FilePath' => $data->file_path,
                'IgnoredFile.StatusType' => $data->file_status,
                'IgnoredFile.IgnoredAt' => Gddysec::datetime($data->ignored_at),
            ));
            $counter++;
        }
    }

    return GddysecTemplate::getSection('settings-general-corefiles-cache', $params);
}
