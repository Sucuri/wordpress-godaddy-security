<?php

/**
 * Code related to the pagehandler.php interface.
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
 * Renders the content of the plugin's dashboard page.
 *
 * @return void
 */
function gddysec_page()
{
    $params = array();

    GddysecInterface::startupChecks();

    /* load data for the Integrity section */
    $params['Integrity'] = GddysecIntegrity::pageIntegrity();

    /* load data for the AuditLogs section */
    $params['AuditLogs'] = GddysecAuditLogs::pageAuditLogs();

    /* load data for the SiteCheck section */
    $params['SiteCheck.iFramesTitle'] = 'iFrames';
    $params['SiteCheck.LinksTitle'] = 'Links';
    $params['SiteCheck.ScriptsTitle'] = 'Scripts';
    $params['SiteCheck.iFramesContent'] = 'Loading...';
    $params['SiteCheck.LinksContent'] = 'Loading...';
    $params['SiteCheck.ScriptsContent'] = 'Loading...';
    $params['SiteCheck.Malware'] = '<div id="gddysec-malware"></div>';
    $params['SiteCheck.Blacklist'] = '<div id="gddysec-blacklist"></div>';
    $params['SiteCheck.Recommendations'] = '<div id="gddysec-recommendations"></div>';

    echo GddysecTemplate::getTemplate('dashboard', $params);
}

/**
 * Renders the content of the plugin's last logins page.
 *
 * @return void
 */
function gddysec_lastlogins_page()
{
    GddysecInterface::startupChecks();

    // Reset the file with the last-logins logs.
    if (GddysecInterface::checkNonce()
        && GddysecRequest::post(':reset_lastlogins') !== false
    ) {
        $file_path = gddysec_lastlogins_datastore_filepath();

        if (@unlink($file_path)) {
            gddysec_lastlogins_datastore_exists();
            GddysecInterface::info('Last-Logins logs were successfully reset.');
        } else {
            GddysecInterface::error('Could not reset the last-logins data file.');
        }
    }

    // Page pseudo-variables initialization.
    $params = array(
        'LastLogins.AllUsers' => gddysec_lastlogins_all(),
        'LastLogins.Admins' => gddysec_lastlogins_admins(),
        'LoggedInUsers' => gddysec_loggedin_users_panel(),
        'FailedLogins' => gddysec_failed_logins_panel(),
        'BlockedUsers' => GddysecBlockedUsers::page(),
    );

    echo GddysecTemplate::getTemplate('lastlogins', $params);
}

/**
 * Renders the content of the plugin's settings page.
 *
 * @return void
 */
function gddysec_settings_page()
{
    GddysecInterface::startupChecks();

    $params = array();
    $nonce = GddysecInterface::checkNonce();

    // Keep the reset options panel and form submission processor before anything else.
    $params['Settings.General.ResetOptions'] = gddysec_settings_general_resetoptions($nonce);

    /* settings - general */
    $params['Settings.General.ApiKey'] = gddysec_settings_general_apikey($nonce);
    $params['Settings.General.DataStorage'] = gddysec_settings_general_datastorage($nonce);
    $params['Settings.General.SelfHosting'] = gddysec_settings_general_selfhosting($nonce);
    $params['Settings.General.ReverseProxy'] = gddysec_settings_general_reverseproxy($nonce);
    $params['Settings.General.ImportExport'] = gddysec_settings_general_importexport($nonce);
    $params['Settings.General.Timezone'] = gddysec_settings_general_timezone($nonce);

    /* settings - scanner */
    $params['Settings.Scanner.Cronjobs'] = GddysecSettingsScanner::cronjobs($nonce);
    $params['Settings.Scanner.IntegrityDiffUtility'] = GddysecSettingsIntegrity::diffUtility($nonce);
    $params['Settings.Scanner.IntegrityCache'] = GddysecSettingsIntegrity::cache($nonce);
    $params['Settings.Scanner.IgnoreFolders'] = GddysecSettingsScanner::ignoreFolders($nonce);

    /* settings - alerts */
    $params['Settings.Alerts.Recipients'] = gddysec_settings_alerts_recipients($nonce);
    $params['Settings.Alerts.Subject'] = gddysec_settings_alerts_subject($nonce);
    $params['Settings.Alerts.PerHour'] = gddysec_settings_alerts_perhour($nonce);
    $params['Settings.Alerts.BruteForce'] = gddysec_settings_alerts_bruteforce($nonce);
    $params['Settings.Alerts.Events'] = gddysec_settings_alerts_events($nonce);
    $params['Settings.Alerts.IgnorePosts'] = gddysec_settings_alerts_ignore_posts();

    /* settings - api service */
    $params['Settings.APIService.Status'] = gddysec_settings_apiservice_status($nonce);
    $params['Settings.APIService.Proxy'] = gddysec_settings_apiservice_proxy();
    $params['Settings.SiteCheck.Target'] = GddysecSiteCheck::targetURLOption();
    $params['Settings.APIService.Checksums'] = gddysec_settings_apiservice_checksums($nonce);

    echo GddysecTemplate::getTemplate('settings', $params);
}

/**
 * Handles all the AJAX plugin's requests.
 *
 * @return void
 */
function gddysec_ajax()
{
    GddysecInterface::checkPageVisibility();

    if (GddysecInterface::checkNonce()) {
        GddysecAuditLogs::ajaxAuditLogs();
        GddysecAuditLogs::ajaxAuditLogsSendLogs();
        GddysecSiteCheck::ajaxMalwareScan();
        GddysecIntegrity::ajaxIntegrity();
        GddysecIntegrity::ajaxIntegrityDiffUtility();
        GddysecSettingsScanner::ignoreFoldersAjax();
    }

    wp_send_json(array('ok' => false, 'error' => 'invalid ajax action'), 200);
}
