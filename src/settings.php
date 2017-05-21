<?php

if (!defined('GDDYSEC_INIT') || GDDYSEC_INIT !== true) {
    if (!headers_sent()) {
        /* Report invalid access if possible. */
        header('HTTP/1.1 403 Forbidden');
    }
    exit(1);
}

class GddysecSettings extends GddysecOption
{
    public static function initialPage()
    {
        GddysecInterface::check_permissions();

        $params = array();
        $nonce = GddysecInterface::check_nonce();

        $params['PageTitle'] = 'Settings';
        $params['Settings.General'] = gddysec_settings_general($nonce);
        $params['Settings.Alerts'] = gddysec_settings_alert($nonce);

        echo GddysecTemplate::getTemplate('settings', $params);
    }
}

/**
 * Handle an Ajax request for this specific page.
 *
 * @return mixed.
 */
function gddysec_settings_ajax()
{
    GddysecInterface::check_permissions();

    if (GddysecInterface::check_nonce()) {
        gddysec_settings_ignorescan_ajax();
        gddysec_settings_apiservice_https_ajax();
    }

    wp_die();
}
