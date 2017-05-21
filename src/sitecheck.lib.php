<?php

if (!defined('GDDYSEC_INIT') || GDDYSEC_INIT !== true) {
    if (!headers_sent()) {
        /* Report invalid access if possible. */
        header('HTTP/1.1 403 Forbidden');
    }
    exit(1);
}

class GddysecSiteCheck
{
    public static function scanAndCollectData()
    {
        $tld = Gddysec::get_domain();
        $cache = new GddysecCache('sitecheck');
        $results = $cache->get('scan_results', GDDYSEC_SITECHECK_LIFETIME, 'array');

        /**
         * Allow the user to scan foreign domains.
         *
         * This condition allows for the execution of the malware scanner on a
         * website different than the one where the plugin is installed. This is
         * basically the same as scanning any domain on the SiteCheck website.
         * In this case, this is mostly used to allow the development execute
         * tests and to troubleshoot issues reported by other users.
         *
         * @var boolean
         */
        if ($custom = GddysecRequest::get('s')) {
            $tld = Gddysec::escape($custom);
            $results = false /* invalid cache */;
        }

        // Return cached malware scan results.
        if ($results && !empty($results)) {
            return $results;
        }

        // Send HTTP request to SiteCheck's API service.
        $results = GddysecAPI::getSitecheckResults($tld);

        // Check for error in the request's response.
        if (is_string($results)) {
            if (@preg_match('/^ERROR:(.+)/', $results, $error_m)) {
                GddysecInterface::error(
                    'The site <code>' . Gddysec::escape($tld) . '</code>'
                    . ' was not scanned: ' . Gddysec::escape($error_m[1])
                );
            } else {
                GddysecInterface::error('SiteCheck error: ' . $results);
            }

            return false;
        }

        // Increase the malware scan counter.
        $counter = (int) GddysecOption::get_option(':sitecheck_counter');
        GddysecOption::update_option(':sitecheck_counter', $counter+1);

        // Cache the results for some time.
        $cache = new GddysecCache('sitecheck');
        $results_were_cached = $cache->add('scan_results', $results);

        if (!$results_were_cached) {
            GddysecInterface::error('Could not cache the malware scan results.');
        }

        return $results;
    }

    public static function details()
    {
        $params = array();
        $data = self::scanAndCollectData();

        $params['SiteCheck.Website'] = '(unknown)';
        $params['SiteCheck.Domain'] = '(unknown)';
        $params['SiteCheck.ServerAddress'] = '(unknown)';
        $params['SiteCheck.WPVersion'] = Gddysec::site_version();
        $params['SiteCheck.PHPVersion'] = phpversion();
        $params['SiteCheck.Additional'] = '';

        if (isset($data['SCAN']['SITE'])) {
            $params['SiteCheck.Website'] = $data['SCAN']['SITE'][0];
        }

        if (isset($data['SCAN']['DOMAIN'])) {
            $params['SiteCheck.Domain'] = $data['SCAN']['DOMAIN'][0];
        }

        if (isset($data['SCAN']['IP'])) {
            $params['SiteCheck.ServerAddress'] = $data['SCAN']['IP'][0];
        }

        $data['SCAN_ADDITIONAL'] = array();

        if (isset($data['SCAN']['HOSTING'])) {
            $data['SCAN_ADDITIONAL'][] = 'Hosting: ' . $data['SCAN']['HOSTING'][0];
        }

        if (isset($data['SCAN']['CMS'])) {
            $data['SCAN_ADDITIONAL'][] = 'CMS: ' . $data['SCAN']['CMS'][0];
        }

        if (isset($data['SYSTEM']['NOTICE'])) {
            $data['SCAN_ADDITIONAL'] = array_merge(
                $data['SCAN_ADDITIONAL'],
                $data['SYSTEM']['NOTICE']
            );
        }

        if (isset($data['SYSTEM']['INFO'])) {
            $data['SCAN_ADDITIONAL'] = array_merge(
                $data['SCAN_ADDITIONAL'],
                $data['SYSTEM']['INFO']
            );
        }

        if (isset($data['WEBAPP']['VERSION'])) {
            $data['SCAN_ADDITIONAL'] = array_merge(
                $data['SCAN_ADDITIONAL'],
                $data['WEBAPP']['VERSION']
            );
        }

        if (isset($data['WEBAPP']['WARN'])) {
            $data['SCAN_ADDITIONAL'] = array_merge(
                $data['SCAN_ADDITIONAL'],
                $data['WEBAPP']['WARN']
            );
        }

        if (isset($data['OUTDATEDSCAN'])) {
            foreach ($data['OUTDATEDSCAN'] as $outdated) {
                $data['SCAN_ADDITIONAL'][] = $outdated[0] . ':' . $outdated[2];
            }
        }

        foreach ($data['SCAN_ADDITIONAL'] as $text) {
            $parts = explode(':', $text, 2);

            if (count($parts) === 2) {
                $params['SiteCheck.Additional'] .= GddysecTemplate::getSnippet(
                    'sitecheck-details',
                    array(
                        'SiteCheck.Title' => $parts[0],
                        'SiteCheck.Value' => $parts[1],
                    )
                );
            }
        }

        return GddysecTemplate::getSection('sitecheck-details', $params);
    }

    public static function malware()
    {
        $params = array();
        $data = self::scanAndCollectData();

        $params['Malware.Content'] = '';
        $params['Malware.Color'] = 'green';
        $params['Malware.Title'] = 'Site is Clean';
        $params['Malware.CleanVisibility'] = 'visible';
        $params['Malware.InfectedVisibility'] = 'hidden';

        if (isset($data['MALWARE']['WARN']) && !empty($data['MALWARE']['WARN'])) {
            $params['Malware.Color'] = 'red';
            $params['Malware.Title'] = 'Site is not Clean';
            $params['Malware.CleanVisibility'] = 'hidden';
            $params['Malware.InfectedVisibility'] = 'visible';

            foreach ($data['MALWARE']['WARN'] as $mal) {
                $info = self::malwareDetails($mal);

                if (!$info) {
                    continue;
                }

                $params['Malware.Content'] .= GddysecTemplate::getSnippet(
                    'sitecheck-malware',
                    array(
                        'Malware.InfectedURL' => $info['infected_url'],
                        'Malware.MalwareType' => $info['malware_type'],
                        'Malware.MalwareDocs' => $info['malware_docs'],
                        'Malware.AlertMessage' => $info['alert_message'],
                        'Malware.MalwarePayload' => $info['malware_payload'],
                    )
                );
            }
        }

        return GddysecTemplate::getSection('sitecheck-malware', $params);
    }

    public static function blacklist()
    {
        $params = array();
        $data = self::scanAndCollectData();

        $params['Blacklist.Title'] = 'Not Blacklisted';
        $params['Blacklist.Color'] = 'green';
        $params['Blacklist.Content'] = '';

        foreach ($data['BLACKLIST'] as $type => $proof) {
            foreach ($proof as $info) {
                $url = $info[1];
                $title = @preg_replace(
                    '/Domain (clean|blacklisted) (on|by) (the )?/',
                    '' /* remove unnecessary text from the output */,
                    substr($info[0], 0, strrpos($info[0], ':'))
                );

                $params['Blacklist.Content'] .= GddysecTemplate::getSnippet(
                    'sitecheck-blacklist',
                    array(
                        'Blacklist.URL' => $url,
                        'Blacklist.Status' => $type,
                        'Blacklist.Service' => $title,
                    )
                );
            }
        }

        if (isset($data['BLACKLIST']['WARN'])) {
            $params['Blacklist.Title'] = 'Blacklisted';
            $params['Blacklist.Color'] = 'red';
        }

        return GddysecTemplate::getSection('sitecheck-blacklist', $params);
    }

    public static function recommendations()
    {
        $params = array();
        $data = self::scanAndCollectData();

        $params['Recommendations.Content'] = '';
        $params['Recommendations.Visibility'] = 'hidden';

        if (isset($data['RECOMMENDATIONS'])) {
            foreach ($data['RECOMMENDATIONS'] as $recommendation) {
                if (count($recommendation) < 3) {
                    continue;
                }

                $params['Recommendations.Visibility'] = 'visible';
                $params['Recommendations.Content'] .= GddysecTemplate::getSnippet(
                    'sitecheck-recommendations',
                    array(
                        'Recommendations.Title' => $recommendation[0],
                        'Recommendations.Value' => $recommendation[1],
                        'Recommendations.URL' => $recommendation[2],
                    )
                );
            }
        }

        return GddysecTemplate::getSection('sitecheck-recommendations', $params);
    }

    public static function iFramesTitle()
    {
        $data = self::scanAndCollectData();

        if (!isset($data['LINKS']['IFRAME'])) {
            return 'No iFrames Found';
        }

        return sprintf('%d iFrames Found', count($data['LINKS']['IFRAME']));
    }

    public static function linksTitle()
    {
        $data = self::scanAndCollectData();

        if (!isset($data['LINKS']['URL'])) {
            return 'No Links Found';
        }

        return sprintf('%d Links Found', count($data['LINKS']['URL']));
    }

    public static function scriptsTitle()
    {
        $data = self::scanAndCollectData();
        $total = 0; /* all type of scripts */

        if (isset($data['LINKS']['JSLOCAL'])) {
            $total += count($data['LINKS']['JSLOCAL']);
        }

        if (isset($data['LINKS']['JSEXTERNAL'])) {
            $total += count($data['LINKS']['JSEXTERNAL']);
        }

        if ($total === 0) {
            return 'No Scripts Found';
        }

        return sprintf('%d Scripts Found', $total);
    }

    public static function iFramesContent()
    {
        $params = array();
        $data = self::scanAndCollectData();

        if (!isset($data['LINKS']['IFRAME'])) {
            return ''; /* empty content */
        }

        $params['SiteCheck.Resources'] = '';

        foreach ($data['LINKS']['IFRAME'] as $url) {
            $params['SiteCheck.Resources'] .= GddysecTemplate::getSnippet(
                'sitecheck-links',
                array(
                    'SiteCheck.URL' => $url,
                )
            );
        }

        return GddysecTemplate::getSection('sitecheck-links', $params);
    }

    public static function linksContent()
    {
        $params = array();
        $data = self::scanAndCollectData();

        if (!isset($data['LINKS']['URL'])) {
            return ''; /* empty content */
        }

        $params['SiteCheck.Resources'] = '';

        foreach ($data['LINKS']['URL'] as $url) {
            $params['SiteCheck.Resources'] .= GddysecTemplate::getSnippet(
                'sitecheck-links',
                array(
                    'SiteCheck.URL' => $url,
                )
            );
        }

        return GddysecTemplate::getSection('sitecheck-links', $params);
    }

    public static function scriptsContent()
    {
        $total = 0;
        $params = array();
        $data = self::scanAndCollectData();

        $params['SiteCheck.Resources'] = '';

        if (isset($data['LINKS']['JSLOCAL'])) {
            foreach ($data['LINKS']['JSLOCAL'] as $url) {
                $total++;

                $params['SiteCheck.Resources'] .= GddysecTemplate::getSnippet(
                    'sitecheck-links',
                    array(
                        'SiteCheck.URL' => $url,
                    )
                );
            }
        }

        if (isset($data['LINKS']['JSEXTERNAL'])) {
            foreach ($data['LINKS']['JSEXTERNAL'] as $url) {
                $total++;
                $params['SiteCheck.Resources'] .= GddysecTemplate::getSnippet(
                    'sitecheck-links',
                    array(
                        'SiteCheck.URL' => $url,
                    )
                );
            }
        }

        if ($total === 0) {
            return ''; /* empty content */
        }

        return GddysecTemplate::getSection('sitecheck-links', $params);
    }

    /**
     * Extract detailed information from a SiteCheck malware payload.
     *
     * @param  array $malware Array with two entries with basic malware information.
     * @return array          Detailed information of the malware found by SiteCheck.
     */
    private static function malwareDetails($malware = array())
    {
        if (count($malware) < 2) {
            return false;
        }

        $data = array(
            'alert_message' => '',
            'infected_url' => '',
            'malware_type' => '',
            'malware_docs' => '',
            'malware_payload' => '',
        );

        // Extract the information from the alert message.
        $alert_parts = explode(':', $malware[0], 2);

        if (isset($alert_parts[1])) {
            $data['alert_message'] = $alert_parts[0];
            $data['infected_url'] = $alert_parts[1];
        }

        // Extract the information from the malware message.
        $malware_parts = explode("\n", $malware[1]);

        if (isset($malware_parts[1])) {
            if (@preg_match('/(.+)\. Details: (.+)/', $malware_parts[0], $match)) {
                $data['malware_type'] = $match[1];
                $data['malware_docs'] = $match[2];
            }

            $data['malware_payload'] = trim($malware_parts[1]);
        }

        return $data;
    }
}
