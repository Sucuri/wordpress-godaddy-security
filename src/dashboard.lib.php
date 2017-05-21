<?php

if (!defined('GDDYSEC_INIT') || GDDYSEC_INIT !== true) {
    if (!headers_sent()) {
        /* Report invalid access if possible. */
        header('HTTP/1.1 403 Forbidden');
    }
    exit(1);
}

/**
 * Dashboard Page
 *
 * Implements all the functions that will render the HTML content of the plugin
 * as well as process the HTTP requests from forms and JavaScript code. Ajax
 * requests are handled by individual functions passed by the main hook attached
 * to the WordPress actions mechanism.
 */
class GddysecDashboard
{
    /**
     * WordPress core integrity page.
     *
     * It checks whether the WordPress core files are the original ones, and the state
     * of the themes and plugins reporting the availability of updates. It also checks
     * the user accounts under the administrator group.
     *
     * @return void
     */
    public static function initialPage()
    {
        GddysecInterface::check_permissions();

        $params = array(
            'CoreFiles' => GddysecCoreFiles::pageCoreFiles(),
            'SiteCheck.Details' => GddysecSiteCheck::details(),
            'SiteCheck.Malware' => GddysecSiteCheck::malware(),
            'SiteCheck.Blacklist' => GddysecSiteCheck::blacklist(),
            'SiteCheck.Recommendations' => GddysecSiteCheck::recommendations(),
            'SiteCheck.iFramesTitle' => GddysecSiteCheck::iFramesTitle(),
            'SiteCheck.LinksTitle' => GddysecSiteCheck::linksTitle(),
            'SiteCheck.ScriptsTitle' => GddysecSiteCheck::scriptsTitle(),
            'SiteCheck.iFramesContent' => GddysecSiteCheck::iFramesContent(),
            'SiteCheck.LinksContent' => GddysecSiteCheck::linksContent(),
            'SiteCheck.ScriptsContent' => GddysecSiteCheck::scriptsContent(),
            'AuditLogs' => GddysecAuditLogs::pageAuditLogs(),
        );

        echo GddysecTemplate::getTemplate('integrity', $params);
    }

    /**
     * Handle an Ajax request for this specific page.
     *
     * @return mixed.
     */
    public static function ajaxRequests()
    {
        GddysecInterface::check_permissions();

        if (GddysecInterface::check_nonce()) {
            GddysecCoreFiles::ajaxCoreFiles();
            GddysecAuditLogs::ajaxAuditLogs();
        }

        wp_die();
    }
}
